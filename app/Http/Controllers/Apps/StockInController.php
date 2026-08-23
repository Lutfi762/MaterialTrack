<?php

namespace App\Http\Controllers\Apps;

use App\Exports\StockInsExport;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Item;
use App\Models\StockIn;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Services\ActivityLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class StockInController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = StockIn::with(['item', 'department', 'user', 'supplier'])->latest();

        // department_user hanya lihat data dept sendiri
        if ($user->hasRole('department_user')) {
            $query->where('department_id', $user->department_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Departemen untuk dropdown filter
        $departmentsQuery = Department::orderBy('name');
        if ($user->hasRole('department_user')) {
            $departmentsQuery->where('id', $user->department_id);
        }
        $departments = $departmentsQuery->get(['id', 'name']);

        return inertia('Apps/StockIns/Index', [
            'stockIns' => $query->paginate(15)->withQueryString(),
            'items' => Item::orderBy('name')->get(['id', 'name', 'code']),
            'departments' => $departments,
            'filters' => $request->only(['department_id', 'item_id', 'date_from', 'date_to']),
        ]);
    }

    public function create()
    {
        return inertia('Apps/StockIns/Create', [
            'items' => Item::with('category:id,name')->orderBy('name')->get(['id', 'name', 'code', 'unit', 'unit_alt', 'conversion_factor', 'current_stock', 'category_id']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'department_id' => 'nullable|exists:departments,id',
            'qty' => 'required|integer|min:1',
            'po_number' => 'nullable|string|max:50',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'source' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
            'expiry_date' => 'nullable|date|after_or_equal:today',
            'date' => 'required|date',
        ]);

        // Sanitize text fields
        if (isset($validated['source'])) {
            $validated['source'] = strip_tags($validated['source']);
        }
        if (isset($validated['notes'])) {
            $validated['notes'] = strip_tags($validated['notes']);
        }

        $user = auth()->user();

        try {
            $stockIn = DB::transaction(function () use ($validated, $user) {
                // Lock item row untuk prevent race condition
                $item = Item::lockForUpdate()->findOrFail($validated['item_id']);

                $stockBefore = $item->current_stock;
                $stockAfter = $stockBefore + $validated['qty'];

                // Update stock atomically SEBELUM create, agar observer
                // mencatat stock_before/balance_after yang benar
                $item->current_stock = $stockAfter;
                $item->save();

                // lockForUpdate + MAX (bukan count()+1) agar aman dari race
                // condition nomor referensi saat request konkuren.
                $lastSeq = (int) DB::table('stock_ins')
                    ->whereDate('created_at', now())
                    ->lockForUpdate()
                    ->max(DB::raw('CAST(SUBSTRING(reference_no, -4) AS UNSIGNED)'));

                $stockIn = StockIn::create([
                    'item_id' => $validated['item_id'],
                    'department_id' => $validated['department_id'] ?? null,
                    'user_id' => $user->id,
                    'reference_no' => 'IN-'.now()->format('Ymd').'-'.str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT),
                    'po_number' => $validated['po_number'] ?? null,
                    'qty' => $validated['qty'],
                    'source' => $validated['source'] ?? null,
                    'supplier_id' => $validated['supplier_id'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'expiry_date' => $validated['expiry_date'] ?? null,
                    'date' => $validated['date'],
                ]);

                // Observer akan handle StockMovement creation otomatis

                return $stockIn;
            });

            ActivityLogService::log('stock_in.create', $stockIn, ['item_name' => $stockIn->item->name ?? '', 'qty' => $validated['qty']]);

            return to_route('apps.stock-ins.index')->with([
                'success' => 'Material masuk berhasil dicatat.',
                'newStockInId' => $stockIn->id,
            ]);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menyimpan transaksi material masuk: '.$e->getMessage()])->withInput();
        }
    }

    public function edit(StockIn $stockIn)
    {
        $this->authorize('update', $stockIn);

        return inertia('Apps/StockIns/Edit', [
            'stockIn' => $stockIn->load(['item', 'department', 'supplier']),
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
        ]);
    }

    public function update(Request $request, StockIn $stockIn)
    {
        $this->authorize('update', $stockIn);

        $validated = $request->validate([
            'po_number' => 'nullable|string|max:50',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'source' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        // Sanitize text fields
        if (isset($validated['source'])) {
            $validated['source'] = strip_tags($validated['source']);
        }
        if (isset($validated['notes'])) {
            $validated['notes'] = strip_tags($validated['notes']);
        }

        $stockIn->update($validated);

        return to_route('apps.stock-ins.index')->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(StockIn $stockIn)
    {
        $this->authorize('delete', $stockIn);

        try {
            DB::transaction(function () use ($stockIn) {
                $item = $stockIn->item;

                if ($item->current_stock < $stockIn->qty) {
                    throw new \Exception('Tidak bisa membatalkan: stok saat ini sudah lebih kecil dari qty transaksi ini (sudah terpakai).');
                }

                $stockBefore = $item->current_stock;

                $item->decrement('current_stock', $stockIn->qty);

                StockMovement::create([
                    'item_id' => $item->id,
                    'user_id' => auth()->id(),
                    'type' => 'out',
                    'qty' => $stockIn->qty,
                    'stock_before' => $stockBefore,
                    'balance_after' => $item->fresh()->current_stock,
                    'ref_type' => StockIn::class,
                    'ref_id' => $stockIn->id,
                    'notes' => 'Pembatalan transaksi '.$stockIn->reference_no,
                ]);

                $stockIn->delete();
            });

            return to_route('apps.stock-ins.index')->with('success', 'Transaksi berhasil dibatalkan, stok dikembalikan.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal membatalkan transaksi: '.$e->getMessage()]);
        }
    }

    public function downloadPdf(StockIn $stockIn)
    {
        $this->authorize('view', $stockIn);

        $pdf = Pdf::loadView('pdf.stock-in', [
            'stockIn' => $stockIn->load(['item', 'department', 'user']),
        ]);

        return $pdf->download('StockIn-'.$stockIn->reference_no.'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $user = auth()->user();

        if (! $user->hasAnyPermission(['stock-ins.export', 'stock-ins.index'])) {
            abort(403, 'Anda tidak memiliki izin untuk mengekspor data.');
        }

        $query = StockIn::query()->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        return Excel::download(new StockInsExport($query), 'MaterialTrack-StockIn-'.now()->format('Ymd-His').'.xlsx');
    }

    public function downloadAllPdf(Request $request)
    {
        $user = auth()->user();

        if (! $user->hasAnyPermission(['stock-ins.export', 'stock-ins.index'])) {
            abort(403, 'Anda tidak memiliki izin untuk mengekspor data.');
        }

        $query = StockIn::with(['item', 'department', 'user', 'supplier'])->latest();

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $stockIns = $query->get();

        $pdf = Pdf::loadView('pdf.stock-ins-batch', [
            'stockIns' => $stockIns,
        ]);

        return $pdf->download('MaterialTrack-StockIn-Rekap-'.now()->format('Ymd-His').'.pdf');
    }
}
