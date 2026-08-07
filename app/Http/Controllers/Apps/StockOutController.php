<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Item;
use App\Models\Pic;
use App\Models\StockMovement;
use App\Models\StockOut;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockOutsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOutController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

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

        return inertia('Apps/StockOuts/Index', [
            'stockOuts' => $query->paginate(15)->withQueryString(),
            'items'     => Item::orderBy('name')->get(['id', 'name', 'code']),
            'filters'   => $request->only(['item_id', 'date_from', 'date_to']),
        ]);
    }
public function create()
{
    $user = auth()->user();

    $itemsQuery = Item::with('category.departments:id');

    // department_user hanya boleh input Stock Out untuk material dari
    // kategori yang di-assign ke departemennya sendiri.
    if ($user->hasRole('department_user')) {
        $itemsQuery->whereHas('category.departments', function ($q) use ($user) {
            $q->where('departments.id', $user->department_id);
        });
    }

    $items = $itemsQuery->orderBy('name')->get(['id', 'name', 'code', 'unit', 'current_stock', 'category_id'])
        ->map(fn ($item) => [
            'id'             => $item->id,
            'name'           => $item->name,
            'code'           => $item->code,
            'unit'           => $item->unit,
            'current_stock'  => $item->current_stock,
            'category_id'    => $item->category_id,
            'category_name'  => $item->category?->name,
            'department_ids' => $item->category?->departments->pluck('id') ?? [],
        ]);

    if ($user->hasRole('department_user')) {
        $departments = Department::where('id', $user->department_id)->get(['id', 'name']);
        $pics        = Pic::where('department_id', $user->department_id)->get(['id', 'name', 'department_id']);
    } else {
        $departments = Department::orderBy('name')->get(['id', 'name']);
        $pics        = Pic::orderBy('name')->get(['id', 'name', 'department_id']);
    }

    return inertia('Apps/StockOuts/Create', [
        'items'              => $items,
        'departments'        => $departments,
        'pics'               => $pics,
        'lockedDepartmentId' => $user->hasRole('department_user') ? $user->department_id : null,
    ]);
}

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'item_id'       => 'required|exists:items,id',
            'department_id' => 'required|exists:departments,id',
            'pic_id'        => 'required|exists:pics,id',
            'qty'           => 'required|integer|min:1',
            'purpose'       => 'required|string|max:255',
            'notes'         => 'nullable|string',
            'date'          => 'required|date',
        ]);

        if ($user->hasRole('department_user') && $validated['department_id'] != $user->department_id) {
            abort(403, 'Anda hanya bisa membuat transaksi untuk departemen Anda sendiri.');
        }

        $item = Item::findOrFail($validated['item_id']);

        if ($validated['qty'] > $item->current_stock) {
            return back()->withErrors([
                'qty' => "Stok tidak cukup. Stok tersedia: {$item->current_stock} {$item->unit}.",
            ])->withInput();
        }

        $stockOut = DB::transaction(function () use ($validated, $item, $user) {
            $stockOut = StockOut::create([
                'item_id'       => $validated['item_id'],
                'department_id' => $validated['department_id'],
                'pic_id'        => $validated['pic_id'],
                'user_id'       => $user->id,
                'reference_no'  => 'OUT-' . now()->format('Ymd') . '-' . str_pad(StockOut::whereDate('created_at', now())->count() + 1, 4, '0', STR_PAD_LEFT),
                'qty'           => $validated['qty'],
                'purpose'       => $validated['purpose'],
                'notes'         => $validated['notes'] ?? null,
                'date'          => $validated['date'],
            ]);

            $stockBefore = $item->current_stock;

            $item->decrement('current_stock', $validated['qty']);

            StockMovement::create([
                'item_id'       => $item->id,
                'user_id'       => $user->id,
                'pic_id'        => $validated['pic_id'],
                'type'          => 'out',
                'qty'           => $validated['qty'],
                'stock_before'  => $stockBefore,
                'balance_after' => $item->fresh()->current_stock,
                'ref_type'      => StockOut::class,
                'ref_id'        => $stockOut->id,
                'notes'         => $validated['purpose'],
            ]);

            return $stockOut;
        });

        return to_route('apps.stock-outs.index')->with([
            'success'       => 'Material keluar berhasil dicatat.',
            'newStockOutId' => $stockOut->id,
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
        $this->authorizeStockOutAccess($stockOut);

        return inertia('Apps/StockOuts/Edit', [
            'stockOut'    => $stockOut,
            'items'       => Item::orderBy('name')->get(['id', 'name', 'code', 'unit', 'current_stock']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'pics'        => Pic::orderBy('name')->get(['id', 'name', 'department_id']),
        ]);
    }

    public function update(Request $request, StockOut $stockOut)
    {
        $this->authorizeStockOutAccess($stockOut);

        $validated = $request->validate([
            'purpose' => 'required|string|max:255',
            'notes'   => 'nullable|string',
        ]);

        $stockOut->update($validated);

        return to_route('apps.stock-outs.index')->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(StockOut $stockOut)
    {
        $this->authorizeStockOutAccess($stockOut);

        DB::transaction(function () use ($stockOut) {
            $item = $stockOut->item;
            $stockBefore = $item->current_stock;

            $item->increment('current_stock', $stockOut->qty);

            StockMovement::create([
                'item_id'       => $item->id,
                'user_id'       => auth()->id(),
                'type'          => 'in',
                'qty'           => $stockOut->qty,
                'stock_before'  => $stockBefore,
                'balance_after' => $item->fresh()->current_stock,
                'ref_type'      => StockOut::class,
                'ref_id'        => $stockOut->id,
                'notes'         => 'Pembatalan transaksi ' . $stockOut->reference_no,
            ]);

            $stockOut->delete();
        });

        return to_route('apps.stock-outs.index')->with('success', 'Transaksi berhasil dibatalkan, stok dikembalikan.');
    }

    public function downloadPdf(StockOut $stockOut)
    {
        $this->authorizeStockOutAccess($stockOut);

        $pdf = Pdf::loadView('pdf.stock-out', [
            'stockOut' => $stockOut->load(['item', 'department', 'pic', 'user']),
        ]);

        return $pdf->download('StockOut-' . $stockOut->reference_no . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $user = auth()->user();

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

        return Excel::download(new StockOutsExport($query), 'MaterialTrack-StockOut-' . now()->format('Ymd-His') . '.xlsx');
    }
    public function downloadAllPdf(Request $request)
    {
        $user = auth()->user();

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

        return $pdf->download('MaterialTrack-StockOut-Rekap-' . now()->format('Ymd-His') . '.pdf');
    }

    private function authorizeStockOutAccess(StockOut $stockOut): void
    {
        $user = auth()->user();

        if ($user->hasRole('department_user') && $stockOut->department_id != $user->department_id) {
            abort(403, 'Anda tidak memiliki akses ke transaksi departemen lain.');
        }
    }
}