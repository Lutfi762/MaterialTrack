<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceipt;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\StockIn;
use App\Models\Warehouse;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class GoodsReceiptController extends Controller
{
    public function index(Request $request)
    {
        $query = GoodsReceipt::with(['purchaseOrder.supplier', 'receiver', 'warehouse'])->latest();

        if ($request->filled('search')) {
            $query->where('gr_number', 'like', "%{$request->search}%");
        }

        return Inertia::render('Apps/GoodsReceipts/Index', [
            'goodsReceipts' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/GoodsReceipts/Create', [
            'purchaseOrders' => PurchaseOrder::whereIn('status', ['approved', 'partial'])
                ->with(['supplier', 'items.item'])
                ->orderBy('po_number')
                ->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'receipt_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty_received' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            // Kunci baris PO terlebih dahulu: serialisasi GR untuk PO yang sama (anti race over-receive)
            $po = PurchaseOrder::with('items.item')->lockForUpdate()->findOrFail($validated['purchase_order_id']);

            if (! in_array($po->status, ['approved', 'partial'])) {
                throw ValidationException::withMessages(['purchase_order_id' => 'PO berstatus ' . $po->status . ' — hanya PO approved/partial yang dapat diterima.']);
            }

            // Agregasi qty per baris PO (dedup bila request mengirim baris sama berulang)
            $lines = [];
            $lineItemIds = [];
            foreach ($validated['items'] as $itemData) {
                $poItemId = (int) $itemData['purchase_order_item_id'];
                $lines[$poItemId] = ($lines[$poItemId] ?? 0) + (int) $itemData['qty_received'];
                $lineItemIds[$poItemId] = (int) $itemData['item_id'];
            }

            // Validasi kepemilikan baris, konsistensi item, dan sisa qty PO (anti over-receive)
            foreach ($lines as $poItemId => $qty) {
                if ($qty <= 0) {
                    continue;
                }
                $poItem = $po->items->firstWhere('id', $poItemId);
                if (! $poItem) {
                    throw ValidationException::withMessages(['items' => "Purchase order item #{$poItemId} bukan milik PO {$po->po_number}."]);
                }
                if ($lineItemIds[$poItemId] !== (int) $poItem->item_id) {
                    throw ValidationException::withMessages(['items' => "Item tidak cocok untuk item PO {$poItem->item->name}."]);
                }

                $receivedSoFar = (int) DB::table('goods_receipt_items')
                    ->join('goods_receipts', 'goods_receipts.id', '=', 'goods_receipt_items.goods_receipt_id')
                    ->where('goods_receipts.purchase_order_id', $po->id)
                    ->where('goods_receipt_items.purchase_order_item_id', $poItemId)
                    ->sum('goods_receipt_items.qty_received');

                $remaining = $poItem->qty - $receivedSoFar;
                if ($qty > $remaining) {
                    throw ValidationException::withMessages(['items' => "Qty diterima ({$qty}) melebihi sisa PO ({$remaining}) untuk item {$poItem->item->name}."]);
                }
            }

            $grNumber = DocumentNumber::next('goods_receipts', 'GR', 4);

            $gr = GoodsReceipt::create([
                'gr_number' => $grNumber,
                'purchase_order_id' => $po->id,
                'received_by' => auth()->id(),
                'warehouse_id' => $validated['warehouse_id'] ?? null,
                'receipt_date' => $validated['receipt_date'],
                'status' => 'accepted',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($lines as $poItemId => $qty) {
                if ($qty <= 0) {
                    continue;
                }
                $poItem = $po->items->firstWhere('id', $poItemId);
                $item = $poItem->item;

                $gr->items()->create([
                    'purchase_order_item_id' => $poItemId,
                    'item_id' => $poItem->item_id,
                    'qty_received' => $qty,
                ]);

                $poItem->increment('qty_received', $qty);
                $item->increment('current_stock', $qty);

                StockIn::create([
                    'reference_no' => 'IN-GR-' . $grNumber,
                    'item_id' => $poItem->item_id,
                    'qty' => $qty,
                    'date' => $validated['receipt_date'],
                    'user_id' => auth()->id(),
                    'po_number' => $po->po_number,
                    'supplier_id' => $po->supplier_id,
                    'notes' => 'Penerimaan barang dari ' . $grNumber,
                ]);
            }

            // Update status PO (refetch dari DB — baris PO sudah terkunci sejak awal)
            $po->refresh();
            $allFullyReceived = $po->items->every(fn ($i) => $i->qty_received >= $i->qty);
            $po->update(['status' => $allFullyReceived ? 'received' : 'partial']);
        });

        return to_route('apps.goods-receipts.index')->with('success', 'Penerimaan Barang (GR) berhasil diproses dan stok telah ditambahkan.');
    }

    public function show(GoodsReceipt $goodsReceipt)
    {
        return Inertia::render('Apps/GoodsReceipts/Show', [
            'goodsReceipt' => $goodsReceipt->load(['purchaseOrder.supplier', 'receiver', 'warehouse', 'items.item']),
        ]);
    }

    public function edit(GoodsReceipt $goodsReceipt)
    {
        return Inertia::render('Apps/GoodsReceipts/Edit', [
            'goodsReceipt' => $goodsReceipt->load(['purchaseOrder.supplier', 'purchaseOrder.items.item', 'items']),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, GoodsReceipt $goodsReceipt)
    {
        $validated = $request->validate([
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'receipt_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty_received' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($validated, $goodsReceipt) {
            // Kunci baris PO: serialisasi update GR (anti race over-receive)
            $po = PurchaseOrder::with('items.item')->lockForUpdate()->findOrFail($goodsReceipt->purchase_order_id);

            if (! in_array($po->status, ['approved', 'partial', 'received'])) {
                throw ValidationException::withMessages(['purchase_order_id' => 'PO berstatus ' . $po->status . ' — tidak dapat mengubah penerimaan.']);
            }

            // 1. Balik efek lama (stok, qty_received PO, StockIn terkait)
            $this->reverseGoodsReceipt($goodsReceipt);

            // 2. Hapus line lama agar tidak terhitung pada perhitungan sisa PO
            $goodsReceipt->items()->delete();

            // 3. Agregasi qty per baris PO (dedup bila request mengirim baris sama berulang)
            $lines = [];
            $lineItemIds = [];
            foreach ($validated['items'] as $itemData) {
                $poItemId = (int) $itemData['purchase_order_item_id'];
                $lines[$poItemId] = ($lines[$poItemId] ?? 0) + (int) $itemData['qty_received'];
                $lineItemIds[$poItemId] = (int) $itemData['item_id'];
            }

            // 4. Validasi kepemilikan baris, konsistensi item, dan sisa qty PO (anti over-receive)
            foreach ($lines as $poItemId => $qty) {
                if ($qty <= 0) {
                    continue;
                }
                $poItem = $po->items->firstWhere('id', $poItemId);
                if (! $poItem) {
                    throw ValidationException::withMessages(['items' => "Purchase order item #{$poItemId} bukan milik PO {$po->po_number}."]);
                }
                if ($lineItemIds[$poItemId] !== (int) $poItem->item_id) {
                    throw ValidationException::withMessages(['items' => "Item tidak cocok untuk item PO {$poItem->item->name}."]);
                }

                $receivedSoFar = (int) DB::table('goods_receipt_items')
                    ->join('goods_receipts', 'goods_receipts.id', '=', 'goods_receipt_items.goods_receipt_id')
                    ->where('goods_receipts.purchase_order_id', $po->id)
                    ->where('goods_receipt_items.purchase_order_item_id', $poItemId)
                    ->sum('goods_receipt_items.qty_received');

                $remaining = $poItem->qty - $receivedSoFar;
                if ($qty > $remaining) {
                    throw ValidationException::withMessages(['items' => "Qty diterima ({$qty}) melebihi sisa PO ({$remaining}) untuk item {$poItem->item->name}."]);
                }
            }

            // 5. Update header & terapkan kembali
            $goodsReceipt->update([
                'warehouse_id' => $validated['warehouse_id'] ?? null,
                'receipt_date' => $validated['receipt_date'],
                'status' => 'accepted',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($lines as $poItemId => $qty) {
                if ($qty <= 0) {
                    continue;
                }
                $poItem = $po->items->firstWhere('id', $poItemId);
                $item = $poItem->item;

                $goodsReceipt->items()->create([
                    'purchase_order_item_id' => $poItemId,
                    'item_id' => $poItem->item_id,
                    'qty_received' => $qty,
                ]);

                $poItem->increment('qty_received', $qty);
                $item->increment('current_stock', $qty);

                StockIn::create([
                    'reference_no' => 'IN-GR-' . $goodsReceipt->gr_number,
                    'item_id' => $poItem->item_id,
                    'qty' => $qty,
                    'date' => $validated['receipt_date'],
                    'user_id' => auth()->id(),
                    'po_number' => $po->po_number,
                    'supplier_id' => $po->supplier_id,
                    'notes' => 'Penerimaan barang dari ' . $goodsReceipt->gr_number,
                ]);
            }

            // 6. Update status PO (baris PO sudah terkunci sejak awal)
            $po->refresh();
            $allFullyReceived = $po->items->every(fn ($i) => $i->qty_received >= $i->qty);
            $po->update(['status' => $allFullyReceived ? 'received' : 'partial']);
        });

        return to_route('apps.goods-receipts.index')->with('success', 'Penerimaan Barang (GR) berhasil diperbarui.');
    }

    public function destroy(GoodsReceipt $goodsReceipt)
    {
        DB::transaction(function () use ($goodsReceipt) {
            $po = PurchaseOrder::with('items')->lockForUpdate()->findOrFail($goodsReceipt->purchase_order_id);

            // Balik efek stok & counter PO, lalu hapus dokumen
            $this->reverseGoodsReceipt($goodsReceipt);
            $goodsReceipt->delete();

            // Status PO dihitung ulang berdasarkan sisa penerimaan
            $po->refresh();
            if ($po->items->sum('qty_received') <= 0) {
                $po->update(['status' => 'approved']);
            } else {
                $allFullyReceived = $po->items->every(fn ($i) => $i->qty_received >= $i->qty);
                $po->update(['status' => $allFullyReceived ? 'received' : 'partial']);
            }
        });

        return to_route('apps.goods-receipts.index')->with('success', 'Penerimaan Barang (GR) dibatalkan dan stok dikembalikan.');
    }

    /**
     * Balik efek stok & counter dari satu GR (sebelum dihapus/diubah ulang).
     */
    private function reverseGoodsReceipt(GoodsReceipt $goodsReceipt): void
    {
        foreach ($goodsReceipt->items as $line) {
            $item = Item::lockForUpdate()->findOrFail($line->item_id);

            if ($item->current_stock < $line->qty_received) {
                throw ValidationException::withMessages(['items' => "Tidak bisa membatalkan: stok {$item->name} sudah terpakai (stok saat ini {$item->current_stock}, perlu mengembalikan {$line->qty_received})."]);
            }

            $item->decrement('current_stock', $line->qty_received);
            $line->purchaseOrderItem->decrement('qty_received', $line->qty_received);
        }

        StockIn::where('reference_no', 'IN-GR-' . $goodsReceipt->gr_number)->delete();
    }
}
