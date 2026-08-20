<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SalesInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesInvoice::with(['salesOrder', 'customer', 'creator'])->latest();

        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', "%{$request->search}%");
        }

        return Inertia::render('Apps/SalesInvoices/Index', [
            'invoices' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/SalesInvoices/Create', [
            'salesOrders' => SalesOrder::whereIn('status', ['delivered', 'partial_delivered'])
                ->with(['customer', 'items.item'])
                ->orderBy('so_number')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $so = SalesOrder::with(['items', 'customer'])->lockForUpdate()->findOrFail($validated['sales_order_id']);

            // Satu SO = satu faktur (cegah faktur ganda)
            if (SalesInvoice::where('sales_order_id', $so->id)->exists()) {
                throw ValidationException::withMessages(['sales_order_id' => 'SO ini sudah pernah diterbitkan faktur penjualan.']);
            }

            $invNumber = DocumentNumber::next('sales_invoices', 'SINV', 4);

            $invoice = SalesInvoice::create([
                'invoice_number' => $invNumber,
                'sales_order_id' => $so->id,
                'customer_id' => $so->customer_id,
                'created_by' => auth()->id(),
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'],
                'subtotal' => $so->subtotal,
                'tax_amount' => $so->tax_amount,
                'total' => $so->total,
                'status' => 'sent',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($so->items as $soItem) {
                $invoice->items()->create([
                    'item_id' => $soItem->item_id,
                    'qty' => $soItem->qty,
                    'unit_price' => $soItem->unit_price,
                    'subtotal' => $soItem->subtotal,
                ]);
            }
        });

        return to_route('apps.sales-invoices.index')->with('success', 'Faktur Penjualan berhasil diterbitkan.');
    }

    public function show(SalesInvoice $salesInvoice)
    {
        return Inertia::render('Apps/SalesInvoices/Show', [
            'invoice' => $salesInvoice->load(['salesOrder', 'customer', 'creator', 'items.item']),
        ]);
    }
}
