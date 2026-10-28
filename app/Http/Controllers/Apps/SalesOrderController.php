<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Item;
use App\Models\Quotation;
use App\Support\DocumentNumber;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SalesOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesOrder::with(['customer', 'creator', 'approver'])->latest();

        if ($request->filled('search')) {
            $query->where('so_number', 'like', "%{$request->search}%");
        }

        return Inertia::render('Apps/SalesOrders/Index', [
            'salesOrders' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/SalesOrders/Create', [
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
            'items' => Item::orderBy('name')->get(['id', 'name', 'code', 'unit', 'price', 'current_stock']),
            'quotations' => Quotation::where('status', 'draft')->orderBy('quotation_number')->get(['id', 'quotation_number']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'quotation_id' => 'nullable|exists:quotations,id',
            'delivery_date' => 'nullable|date',
            'payment_term' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        self::validateStockAvailability($validated['items']);

        DB::transaction(function () use ($validated) {
            // Satu quotation maksimal menjadi satu SO
            if (($validated['quotation_id'] ?? null) && SalesOrder::where('quotation_id', $validated['quotation_id'])->exists()) {
                throw ValidationException::withMessages(['quotation_id' => 'Quotation ini sudah dipakai pada Sales Order lain.']);
            }

            $soNumber = DocumentNumber::next('sales_orders', 'SO');

            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['qty'] * $item['unit_price'];
            }
            $taxAmount = $subtotal * 0.11;
            $total = $subtotal + $taxAmount;

            $so = SalesOrder::create([
                'so_number' => $soNumber,
                'customer_id' => $validated['customer_id'],
                'quotation_id' => $validated['quotation_id'] ?? null,
                'created_by' => auth()->id(),
                'order_date' => now(),
                'delivery_date' => $validated['delivery_date'] ?? null,
                'payment_term' => $validated['payment_term'] ?? 'Net 30',
                'shipping_address' => $validated['shipping_address'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $so->items()->create([
                    'item_id' => $item['item_id'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['qty'] * $item['unit_price'],
                ]);
            }
        });

        return to_route('apps.sales-orders.index')->with('success', 'Pesanan Penjualan (SO) berhasil dibuat.');
    }

    /**
     * ponytail: ATP menghitung current_stock dikurangi reservasi aktif;
     * kalau nanti perlu menghitung SO terbuka lain juga, tambahkan di sini.
     */
    public static function validateStockAvailability(array $items): void
    {
        $requested = [];
        foreach ($items as $row) {
            $requested[(int) $row['item_id']] = ($requested[(int) $row['item_id']] ?? 0) + (int) $row['qty'];
        }

        $stocks = Item::whereIn('id', array_keys($requested))->get(['id', 'name', 'current_stock']);
        $reserved = \App\Models\StockReservation::whereIn('item_id', array_keys($requested))
            ->active()
            ->selectRaw('item_id, SUM(qty) as total')
            ->groupBy('item_id')
            ->pluck('total', 'item_id');

        $errors = [];
        foreach ($requested as $itemId => $qty) {
            $stock = $stocks->firstWhere('id', $itemId);
            $available = ($stock->current_stock ?? 0) - (int) ($reserved[$itemId] ?? 0);
            if (! $stock || $qty > $available) {
                $name = $stock->name ?? "ID #{$itemId}";
                $errors[] = "Stok {$name} tidak cukup (tersedia {$available}, diminta {$qty}).";
            }
        }

        if ($errors) {
            throw ValidationException::withMessages(['items' => $errors]);
        }
    }

    public function show(SalesOrder $salesOrder)
    {
        return Inertia::render('Apps/SalesOrders/Show', [
            'salesOrder' => $salesOrder->load(['customer', 'creator', 'approver', 'items.item']),
        ]);
    }

    public function edit(SalesOrder $salesOrder)
    {
        return Inertia::render('Apps/SalesOrders/Edit', [
            'salesOrder' => $salesOrder->load(['items']),
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
            'items' => Item::orderBy('name')->get(['id', 'name', 'code', 'unit', 'price', 'current_stock']),
            'quotations' => Quotation::where('status', 'draft')->orderBy('quotation_number')->get(['id', 'quotation_number']),
        ]);
    }

    public function update(Request $request, SalesOrder $salesOrder)
    {
        if (! in_array($salesOrder->status, ['draft', 'pending'])) {
            return back()->withErrors(['status' => 'Hanya SO berstatus draft/pending yang dapat diedit.']);
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'quotation_id' => 'nullable|exists:quotations,id',
            'delivery_date' => 'nullable|date',
            'payment_term' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        self::validateStockAvailability($validated['items']);

        DB::transaction(function () use ($validated, $salesOrder) {
            if (($validated['quotation_id'] ?? null) && SalesOrder::where('quotation_id', $validated['quotation_id'])->where('id', '!=', $salesOrder->id)->exists()) {
                throw ValidationException::withMessages(['quotation_id' => 'Quotation ini sudah dipakai pada Sales Order lain.']);
            }

            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['qty'] * $item['unit_price'];
            }
            $taxAmount = $subtotal * 0.11;
            $total = $subtotal + $taxAmount;

            $salesOrder->update([
                'customer_id' => $validated['customer_id'],
                'quotation_id' => $validated['quotation_id'] ?? null,
                'delivery_date' => $validated['delivery_date'] ?? null,
                'payment_term' => $validated['payment_term'] ?? 'Net 30',
                'shipping_address' => $validated['shipping_address'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
                'approved_by' => null,
                'approved_at' => null,
            ]);

            $salesOrder->items()->delete();
            foreach ($validated['items'] as $item) {
                $salesOrder->items()->create([
                    'item_id' => $item['item_id'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['qty'] * $item['unit_price'],
                ]);
            }
        });

        return to_route('apps.sales-orders.index')->with('success', 'Pesanan Penjualan (SO) berhasil diperbarui.');
    }

    public function destroy(SalesOrder $salesOrder)
    {
        if (! in_array($salesOrder->status, ['draft', 'pending'])) {
            return back()->withErrors(['status' => 'Hanya SO berstatus draft/pending yang dapat dihapus.']);
        }
        if (Delivery::where('sales_order_id', $salesOrder->id)->exists() || SalesInvoice::where('sales_order_id', $salesOrder->id)->exists()) {
            return back()->withErrors(['status' => 'SO sudah memiliki surat jalan / faktur, tidak dapat dihapus.']);
        }

        $salesOrder->delete();

        return to_route('apps.sales-orders.index')->with('success', 'Pesanan Penjualan (SO) berhasil dihapus.');
    }

    public function approve(SalesOrder $salesOrder)
    {
        if (! in_array($salesOrder->status, ['pending', 'draft'])) {
            return back()->withErrors(['status' => 'Hanya SO berstatus pending yang dapat disetujui.']);
        }

        $salesOrder->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Sales Order berhasil disetujui.');
    }
}
