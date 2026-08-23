<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PurchaseInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseInvoice::with(['purchaseOrder', 'supplier', 'creator'])->latest();

        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', "%{$request->search}%");
        }

        return Inertia::render('Apps/PurchaseInvoices/Index', [
            'invoices' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/PurchaseInvoices/Create', [
            'purchaseOrders' => PurchaseOrder::whereIn('status', ['received', 'partial'])
                ->with(['supplier', 'items.item'])
                ->orderBy('po_number')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'supplier_invoice_no' => 'nullable|string|max:100',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $po = PurchaseOrder::with(['items', 'supplier'])->lockForUpdate()->findOrFail($validated['purchase_order_id']);

            // Satu PO = satu faktur (cegah faktur ganda)
            if (PurchaseInvoice::where('purchase_order_id', $po->id)->exists()) {
                throw ValidationException::withMessages(['purchase_order_id' => 'PO ini sudah pernah dibuatkan faktur pembelian.']);
            }

            $invNumber = DocumentNumber::next('purchase_invoices', 'PINV', 4);

            $invoice = PurchaseInvoice::create([
                'invoice_number' => $invNumber,
                'purchase_order_id' => $po->id,
                'supplier_id' => $po->supplier_id,
                'created_by' => auth()->id(),
                'supplier_invoice_no' => $validated['supplier_invoice_no'] ?? null,
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'subtotal' => $po->subtotal,
                'tax_amount' => $po->tax_amount,
                'total' => $po->total,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($po->items as $poItem) {
                $invoice->items()->create([
                    'item_id' => $poItem->item_id,
                    'qty' => $poItem->qty,
                    'unit_price' => $poItem->unit_price,
                    'subtotal' => $poItem->subtotal,
                ]);
            }
        });

        return to_route('apps.purchase-invoices.index')->with('success', 'Faktur Pembelian berhasil dibuat.');
    }

    public function show(PurchaseInvoice $purchaseInvoice)
    {
        return Inertia::render('Apps/PurchaseInvoices/Show', [
            'invoice' => $purchaseInvoice->load(['purchaseOrder', 'supplier', 'creator', 'items.item']),
        ]);
    }

    public function edit(PurchaseInvoice $purchaseInvoice)
    {
        return Inertia::render('Apps/PurchaseInvoices/Edit', [
            'invoice' => $purchaseInvoice->load(['purchaseOrder', 'supplier', 'items.item']),
        ]);
    }

    public function update(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        if (! in_array($purchaseInvoice->status, ['draft', 'pending'])) {
            return back()->withErrors(['status' => 'Hanya faktur berstatus draft/pending yang dapat diedit.']);
        }

        $validated = $request->validate([
            'supplier_invoice_no' => 'nullable|string|max:100',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
        ]);

        $purchaseInvoice->update($validated);

        return to_route('apps.purchase-invoices.index')->with('success', 'Faktur Pembelian berhasil diperbarui.');
    }

    public function destroy(PurchaseInvoice $purchaseInvoice)
    {
        if (! in_array($purchaseInvoice->status, ['draft', 'pending'])) {
            return back()->withErrors(['status' => 'Hanya faktur berstatus draft/pending yang dapat dihapus.']);
        }

        $purchaseInvoice->delete();

        return to_route('apps.purchase-invoices.index')->with('success', 'Faktur Pembelian berhasil dihapus.');
    }
}
