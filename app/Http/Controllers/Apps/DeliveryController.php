<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Item;
use App\Models\SalesOrder;
use App\Models\StockOut;
use App\Models\Warehouse;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        $query = Delivery::with(['salesOrder.customer', 'dispatcher', 'warehouse'])->latest();

        if ($request->filled('search')) {
            $query->where('delivery_number', 'like', "%{$request->search}%");
        }

        return Inertia::render('Apps/Deliveries/Index', [
            'deliveries' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/Deliveries/Create', [
            'salesOrders' => SalesOrder::whereIn('status', ['approved', 'processing'])
                ->with(['customer', 'items.item'])
                ->orderBy('so_number')
                ->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'delivery_date' => 'required|date',
            'driver_name' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.sales_order_item_id' => 'required|exists:sales_order_items,id',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty_shipped' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated) {
            // Kunci baris SO: serialisasi delivery untuk SO yang sama (anti race over-ship)
            $so = SalesOrder::with('items.item')->lockForUpdate()->findOrFail($validated['sales_order_id']);

            if (! in_array($so->status, ['approved', 'processing'])) {
                throw ValidationException::withMessages(['sales_order_id' => 'SO berstatus ' . $so->status . ' — hanya SO approved/processing yang dapat dikirim.']);
            }

            // Agregasi qty per baris SO (dedup baris berulang)
            $lines = [];
            $lineItemIds = [];
            foreach ($validated['items'] as $itemData) {
                $soItemId = (int) $itemData['sales_order_item_id'];
                $lines[$soItemId] = ($lines[$soItemId] ?? 0) + (int) $itemData['qty_shipped'];
                $lineItemIds[$soItemId] = (int) $itemData['item_id'];
            }

            // Validasi kepemilikan baris, konsistensi item, dan sisa qty SO (anti over-ship)
            foreach ($lines as $soItemId => $qty) {
                if ($qty <= 0) {
                    continue;
                }
                $soItem = $so->items->firstWhere('id', $soItemId);
                if (! $soItem) {
                    throw ValidationException::withMessages(['items' => "Sales order item #{$soItemId} bukan milik SO {$so->so_number}."]);
                }
                if ($lineItemIds[$soItemId] !== (int) $soItem->item_id) {
                    throw ValidationException::withMessages(['items' => "Item tidak cocok untuk item SO {$soItem->item->name}."]);
                }

                $shippedSoFar = (int) DB::table('delivery_items')
                    ->join('deliveries', 'deliveries.id', '=', 'delivery_items.delivery_id')
                    ->where('deliveries.sales_order_id', $so->id)
                    ->where('delivery_items.sales_order_item_id', $soItemId)
                    ->sum('delivery_items.qty_shipped');

                $remaining = $soItem->qty - $shippedSoFar;
                if ($qty > $remaining) {
                    throw ValidationException::withMessages(['items' => "Qty kirim ({$qty}) melebihi sisa SO ({$remaining}) untuk item {$soItem->item->name}."]);
                }
            }

            // Kunci baris item yang stoknya akan dikurangi (anti race antar SO berbeda)
            $itemIds = $so->items->whereIn('id', array_keys($lines))->pluck('item_id')->unique()->values();
            $lockedItems = Item::whereIn('id', $itemIds)->lockForUpdate()->get()->keyBy('id');

            $delNumber = DocumentNumber::next('deliveries', 'SJ', 4);

            $delivery = Delivery::create([
                'delivery_number' => $delNumber,
                'sales_order_id' => $so->id,
                'customer_id' => $so->customer_id,
                'dispatched_by' => auth()->id(),
                'warehouse_id' => $validated['warehouse_id'] ?? null,
                'delivery_date' => $validated['delivery_date'],
                'driver_name' => $validated['driver_name'] ?? null,
                'vehicle_number' => $validated['vehicle_number'] ?? null,
                'status' => 'delivered',
                'delivered_at' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($lines as $soItemId => $qty) {
                if ($qty <= 0) {
                    continue;
                }
                $soItem = $so->items->firstWhere('id', $soItemId);
                $item = $lockedItems->get($soItem->item_id);

                if (! $item) {
                    throw ValidationException::withMessages(['items' => "Item {$soItem->item->name} tidak ditemukan."]);
                }
                if ($item->current_stock < $qty) {
                    throw ValidationException::withMessages(['items' => "Stok tidak mencukupi untuk barang {$item->name}. Stok saat ini: {$item->current_stock}, dibutuhkan: {$qty}"]);
                }

                $delivery->items()->create([
                    'sales_order_item_id' => $soItemId,
                    'item_id' => $soItem->item_id,
                    'qty_shipped' => $qty,
                ]);

                $soItem->increment('qty_delivered', $qty);
                $item->decrement('current_stock', $qty);

                StockOut::create([
                    'reference_no' => 'OUT-SJ-' . $delNumber,
                    'item_id' => $soItem->item_id,
                    'qty' => $qty,
                    'date' => $validated['delivery_date'],
                    'user_id' => auth()->id(),
                    'purpose' => 'Pengiriman Surat Jalan ' . $delNumber,
                    'status' => 'approved',
                ]);
            }

            // Update status SO (refetch dari DB — baris SO sudah terkunci sejak awal)
            $so->refresh();
            $allDelivered = $so->items->every(fn ($i) => $i->qty_delivered >= $i->qty);
            $so->update(['status' => $allDelivered ? 'delivered' : 'partial_delivered']);
        });

        return to_route('apps.deliveries.index')->with('success', 'Surat Jalan (Delivery) berhasil diproses dan stok telah berkurang.');
    }

    public function show(Delivery $delivery)
    {
        return Inertia::render('Apps/Deliveries/Show', [
            'delivery' => $delivery->load(['salesOrder.customer', 'dispatcher', 'warehouse', 'items.item']),
        ]);
    }
}
