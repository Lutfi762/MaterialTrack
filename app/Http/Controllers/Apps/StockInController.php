<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Item;
use App\Models\StockIn;
use App\Models\StockMovement;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockInsExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockInController extends Controller
{
    public function index(Request $request)
    {
        $query = StockIn::with(['item', 'department', 'user'])->latest();

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        return inertia('Apps/StockIns/Index', [
            'stockIns' => $query->paginate(15)->withQueryString(),
            'items'    => Item::orderBy('name')->get(['id', 'name', 'code']),
            'filters'  => $request->only(['item_id', 'date_from', 'date_to']),
        ]);
    }

    public function create()
    {
        return inertia('Apps/StockIns/Create', [
            'items'       => Item::orderBy('name')->get(['id', 'name', 'code', 'unit', 'current_stock']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id'       => 'required|exists:items,id',
            'department_id' => 'nullable|exists:departments,id',
            'qty'           => 'required|integer|min:1',
            'source'        => 'nullable|string|max:255',
            'notes'         => 'nullable|string',
            'date'          => 'required|date',
        ]);

        $item = Item::findOrFail($validated['item_id']);
        $user = auth()->user();

        $stockIn = DB::transaction(function () use ($validated, $item, $user) {
            $stockIn = StockIn::create([
                'item_id'       => $validated['item_id'],
                'department_id' => $validated['department_id'] ?? null,
                'user_id'       => $user->id,
                'reference_no'  => 'IN-' . now()->format('Ymd') . '-' . str_pad(StockIn::whereDate('created_at', now())->count() + 1, 4, '0', STR_PAD_LEFT),
                'qty'           => $validated['qty'],
                'source'        => $validated['source'] ?? null,
                'notes'         => $validated['notes'] ?? null,
                'date'          => $validated['date'],
            ]);

            $stockBefore = $item->current_stock;

            $item->increment('current_stock', $validated['qty']);

            StockMovement::create([
                'item_id'       => $item->id,
                'user_id'       => $user->id,
                'type'          => 'in',
                'qty'           => $validated['qty'],
                'stock_before'  => $stockBefore,
                'balance_after' => $item->fresh()->current_stock,
                'ref_type'      => StockIn::class,
                'ref_id'        => $stockIn->id,
                'notes'         => $validated['source'] ?? 'Restock',
            ]);

            return $stockIn;
        });

        return to_route('apps.stock-ins.index')->with([
            'success'      => 'Material masuk berhasil dicatat.',
            'newStockInId' => $stockIn->id,
        ]);
    }

    public function edit(StockIn $stockIn)
    {
        return inertia('Apps/StockIns/Edit', [
            'stockIn' => $stockIn->load(['item', 'department']),
        ]);
    }

    public function update(Request $request, StockIn $stockIn)
    {
        $validated = $request->validate([
            'source' => 'nullable|string|max:255',
            'notes'  => 'nullable|string',
        ]);

        $stockIn->update($validated);

        return to_route('apps.stock-ins.index')->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(StockIn $stockIn)
    {
        DB::transaction(function () use ($stockIn) {
            $item = $stockIn->item;

            if ($item->current_stock < $stockIn->qty) {
                abort(422, 'Tidak bisa membatalkan: stok saat ini sudah lebih kecil dari qty transaksi ini (sudah terpakai).');
            }

            $stockBefore = $item->current_stock;

            $item->decrement('current_stock', $stockIn->qty);

            StockMovement::create([
                'item_id'       => $item->id,
                'user_id'       => auth()->id(),
                'type'          => 'out',
                'qty'           => $stockIn->qty,
                'stock_before'  => $stockBefore,
                'balance_after' => $item->fresh()->current_stock,
                'ref_type'      => StockIn::class,
                'ref_id'        => $stockIn->id,
                'notes'         => 'Pembatalan transaksi ' . $stockIn->reference_no,
            ]);

            $stockIn->delete();
        });

        return to_route('apps.stock-ins.index')->with('success', 'Transaksi berhasil dibatalkan, stok dikembalikan.');
    }

    public function downloadPdf(StockIn $stockIn)
    {
        $pdf = Pdf::loadView('pdf.stock-in', [
            'stockIn' => $stockIn->load(['item', 'department', 'user']),
        ]);

        return $pdf->download('StockIn-' . $stockIn->reference_no . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $query = StockIn::query()->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        return Excel::download(new StockInsExport($query), 'MaterialTrack-StockIn-' . now()->format('Ymd-His') . '.xlsx');
    }
    public function downloadAllPdf(Request $request)
    {
        $query = StockIn::with(['item', 'department', 'user'])->latest();

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

        return $pdf->download('MaterialTrack-StockIn-Rekap-' . now()->format('Ymd-His') . '.pdf');
    }
}