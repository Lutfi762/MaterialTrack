<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'creator', 'approver'])->latest();

        if ($request->filled('search')) {
            $query->where('po_number', 'like', "%{$request->search}%");
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return Inertia::render('Apps/PurchaseOrders/Index', [
            'purchaseOrders' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/PurchaseOrders/Create', [
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
            'items' => Item::orderBy('name')->get(['id', 'name', 'code', 'unit', 'price']),
            'purchaseRequests' => PurchaseRequest::where('status', 'approved')->orderBy('pr_number')->get(['id', 'pr_number']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_request_id' => 'nullable|exists:purchase_requests,id',
            'expected_delivery_date' => 'nullable|date',
            'payment_term' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $poNumber = DocumentNumber::next('purchase_orders', 'PO');

            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['qty'] * $item['unit_price'];
            }
            $taxAmount = $subtotal * 0.11; // PPN 11%
            $total = $subtotal + $taxAmount;

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $validated['supplier_id'],
                'purchase_request_id' => $validated['purchase_request_id'] ?? null,
                'created_by' => auth()->id(),
                'order_date' => now(),
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'payment_term' => $validated['payment_term'] ?? 'Net 30',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $itemSubtotal = $item['qty'] * $item['unit_price'];
                $po->items()->create([
                    'item_id' => $item['item_id'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $itemSubtotal,
                ]);
            }
        });

        return to_route('apps.purchase-orders.index')->with('success', 'Pesanan Pembelian (PO) berhasil dibuat.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        return Inertia::render('Apps/PurchaseOrders/Show', [
            'purchaseOrder' => $purchaseOrder->load(['supplier', 'creator', 'approver', 'items.item']),
        ]);
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        return Inertia::render('Apps/PurchaseOrders/Edit', [
            'purchaseOrder' => $purchaseOrder->load(['items']),
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
            'items' => Item::orderBy('name')->get(['id', 'name', 'code', 'unit', 'price']),
            'purchaseRequests' => PurchaseRequest::where('status', 'approved')->orderBy('pr_number')->get(['id', 'pr_number']),
        ]);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (! in_array($purchaseOrder->status, ['draft', 'pending'])) {
            return back()->withErrors(['status' => 'Hanya PO berstatus draft/pending yang dapat diedit.']);
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_request_id' => 'nullable|exists:purchase_requests,id',
            'expected_delivery_date' => 'nullable|date',
            'payment_term' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $purchaseOrder) {
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['qty'] * $item['unit_price'];
            }
            $taxAmount = $subtotal * 0.11; // PPN 11%
            $total = $subtotal + $taxAmount;

            $purchaseOrder->update([
                'supplier_id' => $validated['supplier_id'],
                'purchase_request_id' => $validated['purchase_request_id'] ?? null,
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'payment_term' => $validated['payment_term'] ?? 'Net 30',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'notes' => $validated['notes'] ?? null,
                'approved_by' => null,
                'approved_at' => null,
            ]);

            $purchaseOrder->items()->delete();
            foreach ($validated['items'] as $item) {
                $itemSubtotal = $item['qty'] * $item['unit_price'];
                $purchaseOrder->items()->create([
                    'item_id' => $item['item_id'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $itemSubtotal,
                ]);
            }
        });

        return to_route('apps.purchase-orders.index')->with('success', 'Pesanan Pembelian (PO) berhasil diperbarui.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if (! in_array($purchaseOrder->status, ['draft', 'pending'])) {
            return back()->withErrors(['status' => 'Hanya PO berstatus draft/pending yang dapat dihapus.']);
        }
        if ($purchaseOrder->goodsReceipts()->exists() || $purchaseOrder->invoices()->exists()) {
            return back()->withErrors(['status' => 'PO sudah memiliki penerimaan barang / faktur, tidak dapat dihapus.']);
        }

        $purchaseOrder->delete();

        return to_route('apps.purchase-orders.index')->with('success', 'Pesanan Pembelian (PO) berhasil dihapus.');
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        if (! in_array($purchaseOrder->status, ['pending', 'draft'])) {
            return back()->withErrors(['status' => 'Hanya PO berstatus pending yang dapat disetujui.']);
        }

        $purchaseOrder->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Purchase Order berhasil disetujui.');
    }
}
