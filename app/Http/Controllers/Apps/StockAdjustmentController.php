<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\StockAdjustment;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockAdjustmentController extends Controller
{
    public function index(Request $request)
    {
        $query = StockAdjustment::with(['item', 'user', 'approver'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->input('item_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        return Inertia::render('Apps/StockAdjustments/Index', [
            'adjustments' => $query->paginate(15)->withQueryString(),
            'items' => Item::orderBy('name')->get(['id', 'name', 'code', 'unit', 'current_stock']),
            'filters' => $request->only(['status', 'item_id', 'date_from', 'date_to']),
            'pendingCount' => StockAdjustment::pending()->count(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/StockAdjustments/Create', [
            'items' => Item::with('category:id,name')->orderBy('name')->get(['id', 'name', 'code', 'unit', 'current_stock', 'category_id']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'qty' => 'required|integer',
            'reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validated['qty'] === 0) {
            return back()->withErrors(['qty' => 'Jumlah penyesuaian tidak boleh 0.'])
                ->withInput();
        }

        $item = Item::findOrFail($validated['item_id']);

        if ($validated['qty'] < 0 && abs($validated['qty']) > $item->current_stock) {
            return back()->withErrors(['qty' => "Pengurangan melebihi stok tersedia. Stok saat ini: {$item->current_stock} {$item->unit}."])
                ->withInput();
        }

        $adjustment = StockAdjustment::create([
            'reference_no' => StockAdjustment::generateReferenceNo(),
            'item_id' => $validated['item_id'],
            'user_id' => auth()->id(),
            'qty' => $validated['qty'],
            'reason' => strip_tags($validated['reason']),
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        ActivityLogService::log('stock_adjustment.create', $adjustment, [
            'item_name' => $item->name,
            'qty' => $validated['qty'],
            'reference_no' => $adjustment->reference_no,
        ]);

        return to_route('apps.stock-adjustments.index')
            ->with('success', 'Pengajuan penyesuaian stok berhasil dibuat. Menunggu persetujuan.');
    }

    public function approve(StockAdjustment $stockAdjustment)
    {
        if ($stockAdjustment->status !== 'pending') {
            return back()->withErrors(['error' => 'Penyesuaian ini sudah diproses.']);
        }

        $user = auth()->user();

        DB::transaction(function () use ($stockAdjustment, $user) {
            $item = Item::lockForUpdate()->findOrFail($stockAdjustment->item_id);

            if ($stockAdjustment->qty < 0 && abs($stockAdjustment->qty) > $item->current_stock) {
                throw new \Exception("Stok tidak cukup untuk penyesuaian ini. Stok tersedia: {$item->current_stock} {$item->unit}.");
            }

            // Terapkan penyesuaian ke stok sistem; observer mencatat movement
            $item->current_stock = $item->current_stock + $stockAdjustment->qty;
            $item->save();

            $stockAdjustment->update([
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);
        });

        ActivityLogService::log('stock_adjustment.approve', $stockAdjustment, [
            'item_name' => $stockAdjustment->item->name ?? '',
            'qty' => $stockAdjustment->qty,
            'reference_no' => $stockAdjustment->reference_no,
        ]);

        return back()->with('success', 'Penyesuaian stok disetujui dan stok diperbarui.');
    }

    public function reject(StockAdjustment $stockAdjustment)
    {
        if ($stockAdjustment->status !== 'pending') {
            return back()->withErrors(['error' => 'Penyesuaian ini sudah diproses.']);
        }

        $user = auth()->user();

        $stockAdjustment->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        ActivityLogService::log('stock_adjustment.reject', $stockAdjustment, [
            'item_name' => $stockAdjustment->item->name ?? '',
            'reference_no' => $stockAdjustment->reference_no,
        ]);

        return back()->with('success', 'Penyesuaian stok ditolak.');
    }

    public function destroy(StockAdjustment $stockAdjustment)
    {
        if ($stockAdjustment->status !== 'pending') {
            return back()->withErrors(['error' => 'Hanya penyesuaian berstatus pending yang dapat dihapus.']);
        }

        $stockAdjustment->delete();

        return back()->with('success', 'Penyesuaian stok dihapus.');
    }
}
