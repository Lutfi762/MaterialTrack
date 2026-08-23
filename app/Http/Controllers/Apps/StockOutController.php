<?php

namespace App\Http\Controllers\Apps;

use App\Exports\StockOutsExport;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Item;
use App\Models\Pic;
use App\Models\StockMovement;
use App\Models\StockOut;
use App\Services\ActivityLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class StockOutController extends Controller
{
    use AuthorizesRequests;

    const APPROVAL_THRESHOLD = 50;

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = StockOut::with(['item', 'department', 'pic', 'user', 'approver'])->latest();

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
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pendingCount = StockOut::where('status', 'pending')
            ->when($user->hasRole('department_user'), fn ($q) => $q->where('department_id', $user->department_id))
            ->count();

        // Departemen untuk dropdown filter
        $departmentsQuery = Department::orderBy('name');
        if ($user->hasRole('department_user')) {
            $departmentsQuery->where('id', $user->department_id);
        }
        $departments = $departmentsQuery->get(['id', 'name']);

        return inertia('Apps/StockOuts/Index', [
            'stockOuts' => $query->paginate(15)->withQueryString(),
            'items' => Item::orderBy('name')->get(['id', 'name', 'code']),
            'departments' => $departments,
            'filters' => $request->only(['department_id', 'item_id', 'date_from', 'date_to', 'status']),
            'pendingCount' => $pendingCount,
        ]);
    }

    public function create()
    {
        $user = auth()->user();

        $itemsQuery = Item::with('category:id,name', 'category.departments:id');

        // department_user hanya boleh input Stock Out untuk material dari
        // kategori yang di-assign ke departemennya sendiri.
        if ($user->hasRole('department_user')) {
            $itemsQuery->whereHas('category.departments', function ($q) use ($user) {
                $q->where('departments.id', $user->department_id);
            });
        }

        $items = $itemsQuery->orderBy('name')->get(['id', 'name', 'code', 'unit', 'unit_alt', 'conversion_factor', 'current_stock', 'category_id'])
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'unit' => $item->unit,
                'current_stock' => $item->current_stock,
                'category_id' => $item->category_id,
                'category_name' => $item->category?->name,
                'department_ids' => $item->category?->departments->pluck('id') ?? [],
            ]);

        if ($user->hasRole('department_user')) {
            $departments = Department::where('id', $user->department_id)->get(['id', 'name']);
            $pics = Pic::where('department_id', $user->department_id)->get(['id', 'name', 'department_id']);
        } else {
            $departments = Department::orderBy('name')->get(['id', 'name']);
            $pics = Pic::with('department:id,name')->orderBy('name')->get(['id', 'name', 'department_id']);
        }

        return inertia('Apps/StockOuts/Create', [
            'items' => $items,
            'departments' => $departments,
            'pics' => $pics,
            'lockedDepartmentId' => $user->hasRole('department_user') ? $user->department_id : null,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'department_id' => 'required|exists:departments,id',
            'pic_id' => 'required|exists:pics,id',
            'qty' => 'required|integer|min:1',
            'purpose' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
            'date' => 'required|date',
        ]);

        // Sanitize text fields
        $validated['purpose'] = strip_tags($validated['purpose']);
        if (isset($validated['notes'])) {
            $validated['notes'] = strip_tags($validated['notes']);
        }

        if ($user->hasRole('department_user') && $validated['department_id'] != $user->department_id) {
            abort(403, 'Anda hanya bisa membuat transaksi untuk departemen Anda sendiri.');
        }

        $stockOut = DB::transaction(function () use ($validated, $user) {
            // Lock item row untuk prevent race condition
            $item = Item::lockForUpdate()->findOrFail($validated['item_id']);

            // Check stock availability INSIDE transaction with locked row
            if ($validated['qty'] > $item->current_stock) {
                throw new \Exception("Stok tidak cukup. Stok tersedia: {$item->current_stock} {$item->unit}.");
            }

            // Determine if approval is needed
            $needsApproval = $validated['qty'] > self::APPROVAL_THRESHOLD;
            $status = $needsApproval ? 'pending' : 'approved';

            // Update stock atomically SEBELUM create (hanya untuk yang langsung
            // approved), agar observer mencatat stock_before/balance_after yang benar.
            // Untuk pending, stok dikurangi saat approve() dan movement dibuat
            // oleh StockOutObserver::updated().
            if (! $needsApproval) {
                $stockBefore = $item->current_stock;
                $stockAfter = $stockBefore - $validated['qty'];

                $item->current_stock = $stockAfter;
                $item->save();
            }

            // lockForUpdate + MAX (bukan count()+1) agar aman dari race
            // condition nomor referensi saat request konkuren.
            $lastSeq = (int) DB::table('stock_outs')
                ->whereDate('created_at', now())
                ->lockForUpdate()
                ->max(DB::raw('CAST(SUBSTRING(reference_no, -4) AS UNSIGNED)'));

            $stockOut = StockOut::create([
                'item_id' => $validated['item_id'],
                'department_id' => $validated['department_id'],
                'pic_id' => $validated['pic_id'],
                'user_id' => $user->id,
                'reference_no' => 'OUT-'.now()->format('Ymd').'-'.str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT),
                'qty' => $validated['qty'],
                'purpose' => $validated['purpose'],
                'notes' => $validated['notes'] ?? null,
                'date' => $validated['date'],
                'status' => $status,
            ]);

            return $stockOut;
        });

        ActivityLogService::log('stock_out.create', $stockOut, ['item_name' => $stockOut->item->name ?? '', 'qty' => $validated['qty'], 'status' => $stockOut->status]);

        $message = $stockOut->status === 'pending'
            ? 'Material keluar diajukan untuk approval (qty > '.self::APPROVAL_THRESHOLD.'). Menunggu persetujuan admin.'
            : 'Material keluar berhasil dicatat.';

        return to_route('apps.stock-outs.index')->with([
            'success' => $message,
            'newStockOutId' => $stockOut->status === 'approved' ? $stockOut->id : null,
        ]);
    }

    public function show(StockOut $stockOut)
    {
        $this->authorizeStockOutAccess($stockOut);

        return inertia('Apps/StockOuts/Show', [
            'stockOut' => $stockOut->load(['item', 'department', 'pic', 'user']),
        ]);
    }

    public function edit(StockOut $stockOut)
    {
        $this->authorize('update', $stockOut);

        return inertia('Apps/StockOuts/Edit', [
            'stockOut' => $stockOut,
            'items' => Item::orderBy('name')->get(['id', 'name', 'code', 'unit', 'current_stock']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'pics' => Pic::orderBy('name')->get(['id', 'name', 'department_id']),
        ]);
    }

    public function update(Request $request, StockOut $stockOut)
    {
        $this->authorize('update', $stockOut);

        $validated = $request->validate([
            'purpose' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        // Sanitize text fields
        $validated['purpose'] = strip_tags($validated['purpose']);
        if (isset($validated['notes'])) {
            $validated['notes'] = strip_tags($validated['notes']);
        }

        $stockOut->update($validated);

        return to_route('apps.stock-outs.index')->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(StockOut $stockOut)
    {
        $this->authorize('delete', $stockOut);

        try {
            DB::transaction(function () use ($stockOut) {
                $item = $stockOut->item;
                $stockBefore = $item->current_stock;

                $item->increment('current_stock', $stockOut->qty);

                StockMovement::create([
                    'item_id' => $item->id,
                    'user_id' => auth()->id(),
                    'type' => 'in',
                    'qty' => $stockOut->qty,
                    'stock_before' => $stockBefore,
                    'balance_after' => $item->fresh()->current_stock,
                    'ref_type' => StockOut::class,
                    'ref_id' => $stockOut->id,
                    'notes' => 'Pembatalan transaksi '.$stockOut->reference_no,
                ]);

                $stockOut->delete();
            });

            return to_route('apps.stock-outs.index')->with('success', 'Transaksi berhasil dibatalkan, stok dikembalikan.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal membatalkan transaksi: '.$e->getMessage()]);
        }
    }

    public function downloadPdf(StockOut $stockOut)
    {
        $this->authorizeStockOutAccess($stockOut);

        $pdf = Pdf::loadView('pdf.stock-out', [
            'stockOut' => $stockOut->load(['item', 'department', 'pic', 'user']),
        ]);

        return $pdf->download('StockOut-'.$stockOut->reference_no.'.pdf');
    }

    public function exportExcel(Request $request)
    {
        $user = auth()->user();

        if (! $user->hasAnyPermission(['stock-outs.export', 'stock-outs.index'])) {
            abort(403, 'Anda tidak memiliki izin untuk mengekspor data.');
        }

        $query = StockOut::query()->latest();

        if ($user->hasRole('department_user')) {
            $query->where('department_id', $user->department_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        return Excel::download(new StockOutsExport($query), 'MaterialTrack-StockOut-'.now()->format('Ymd-His').'.xlsx');
    }

    public function downloadAllPdf(Request $request)
    {
        $user = auth()->user();

        if (! $user->hasAnyPermission(['stock-outs.export', 'stock-outs.index'])) {
            abort(403, 'Anda tidak memiliki izin untuk mengekspor data.');
        }

        $query = StockOut::with(['item', 'department', 'pic', 'user'])->latest();

        if ($user->hasRole('department_user')) {
            $query->where('department_id', $user->department_id);
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

        $stockOuts = $query->get();

        $pdf = Pdf::loadView('pdf.stock-outs-batch', [
            'stockOuts' => $stockOuts,
        ]);

        return $pdf->download('MaterialTrack-StockOut-Rekap-'.now()->format('Ymd-His').'.pdf');
    }

    private function authorizeStockOutAccess(StockOut $stockOut): void
    {
        $this->authorize('view', $stockOut);
    }

    public function approve(StockOut $stockOut)
    {
        if ($stockOut->status !== 'pending') {
            return back()->withErrors(['error' => 'Transaksi ini tidak dalam status pending.']);
        }

        $user = auth()->user();
        if (! $user->hasAnyPermission(['stock_outs.approve', 'roles.admin'])) {
            abort(403, 'Anda tidak memiliki izin untuk menyetujui transaksi.');
        }

        // Department boundary: department_user hanya bisa approve dept sendiri
        if ($user->hasRole('department_user') && $stockOut->department_id !== $user->department_id) {
            abort(403, 'Anda tidak bisa menyetujui transaksi dari departemen lain.');
        }

        DB::transaction(function () use ($stockOut, $user) {
            $item = Item::lockForUpdate()->findOrFail($stockOut->item_id);

            if ($stockOut->qty > $item->current_stock) {
                throw new \Exception("Stok tidak cukup untuk menyetujui transaksi ini. Stok tersedia: {$item->current_stock} {$item->unit}.");
            }

            $stockBefore = $item->current_stock;
            $stockAfter = $stockBefore - $stockOut->qty;

            $item->current_stock = $stockAfter;
            $item->save();

            $stockOut->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);
        });

        ActivityLogService::log('stock_out.approve', $stockOut, ['item_name' => $stockOut->item->name ?? '', 'qty' => $stockOut->qty]);

        return back()->with('success', 'Transaksi Stock Out berhasil disetujui dan stok dikurangi.');
    }

    public function reject(StockOut $stockOut)
    {
        if ($stockOut->status !== 'pending') {
            return back()->withErrors(['error' => 'Transaksi ini tidak dalam status pending.']);
        }

        $user = auth()->user();
        if (! $user->hasAnyPermission(['stock_outs.approve', 'roles.admin'])) {
            abort(403, 'Anda tidak memiliki izin untuk menolak transaksi.');
        }

        // Department boundary: department_user hanya bisa reject dept sendiri
        if ($user->hasRole('department_user') && $stockOut->department_id !== $user->department_id) {
            abort(403, 'Anda tidak bisa menolak transaksi dari departemen lain.');
        }

        $stockOut->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        ActivityLogService::log('stock_out.reject', $stockOut, ['item_name' => $stockOut->item->name ?? '', 'qty' => $stockOut->qty]);

        return back()->with('success', 'Transaksi Stock Out ditolak.');
    }
}
