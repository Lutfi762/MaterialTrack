<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Item;
use App\Models\SalesInvoice;
use App\Models\SalesReturn;
use App\Models\StockMovement;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SalesReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesReturn::with(['customer', 'creator', 'approver', 'items.item'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('return_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('return_date', '<=', $request->date_to);
        }

        return Inertia::render('Apps/SalesReturns/Index', [
            'salesReturns' => $query->paginate(15)->withQueryString(),
            'customers' => Customer::orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['status', 'customer_id', 'date_from', 'date_to']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/SalesReturns/Create', [
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'code']),
            'salesInvoices' => SalesInvoice::with('customer:id,name')
                ->latest()
                ->get(['id', 'invoice_number', 'invoice_date', 'customer_id', 'total']),
            'items' => Item::orderBy('name')->get(['id', 'name', 'code', 'unit', 'price', 'current_stock']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'return_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'sales_invoice_id' => 'nullable|exists:sales_invoices,id',
            'reason' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();

        $salesReturn = DB::transaction(function () use ($validated, $user) {
            $salesReturn = SalesReturn::create([
                'return_number' => DocumentNumber::next('sales_returns', 'SR'),
                'return_date' => $validated['return_date'],
                'sales_invoice_id' => $validated['sales_invoice_id'] ?? null,
                'customer_id' => $validated['customer_id'],
                'created_by' => $user->id,
                'status' => 'pending',
                'reason' => $validated['reason'] ?? null,
            ]);

            foreach ($validated['items'] as $line) {
                $subtotal = round((float) $line['qty'] * (float) $line['unit_price'], 2);
                $salesReturn->items()->create([
                    'item_id' => $line['item_id'],
                    'qty' => $line['qty'],
                    'unit' => Item::find($line['item_id'])->unit ?? null,
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $subtotal,
                    'notes' => $line['notes'] ?? null,
                ]);
            }

            return $salesReturn;
        });

        return to_route('apps.sales-returns.show', $salesReturn->id);
    }
    public function show(SalesReturn $salesReturn)
    {
        return Inertia::render('Apps/SalesReturns/Show', [
            'salesReturn' => $salesReturn->load(['customer', 'creator', 'approver', 'salesInvoice:id,invoice_number', 'items.item']),
        ]);
    }

    public function edit(SalesReturn $salesReturn)
    {
        if ($salesReturn->status !== 'pending') {
            return back()->withErrors(['error' => 'Hanya retur berstatus pending yang dapat diedit.']);
        }

        return Inertia::render('Apps/SalesReturns/Edit', [
            'salesReturn' => $salesReturn->load(['items']),
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'code']),
            'salesInvoices' => SalesInvoice::with('customer:id,name')->latest()->get(['id', 'invoice_number', 'invoice_date', 'customer_id', 'total']),
            'items' => Item::orderBy('name')->get(['id', 'name', 'code', 'unit', 'price', 'current_stock']),
        ]);
    }

    public function update(Request $request, SalesReturn $salesReturn)
    {
        if ($salesReturn->status !== 'pending') {
            return back()->withErrors(['error' => 'Hanya retur berstatus pending yang dapat diedit.']);
        }

        $validated = $request->validate([
            'return_date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'sales_invoice_id' => 'nullable|exists:sales_invoices,id',
            'reason' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $salesReturn) {
            $salesReturn->update([
                'return_date' => $validated['return_date'],
                'sales_invoice_id' => $validated['sales_invoice_id'] ?? null,
                'customer_id' => $validated['customer_id'],
                'reason' => $validated['reason'] ?? null,
            ]);

            $salesReturn->items()->delete();

            foreach ($validated['items'] as $line) {
                $subtotal = round((float) $line['qty'] * (float) $line['unit_price'], 2);
                $salesReturn->items()->create([
                    'item_id' => $line['item_id'],
                    'qty' => $line['qty'],
                    'unit' => Item::find($line['item_id'])->unit ?? null,
                    'unit_price' => $line['unit_price'],
                    'subtotal' => $subtotal,
                    'notes' => $line['notes'] ?? null,
                ]);
            }
        });

        return to_route('apps.sales-returns.show', $salesReturn->id)->with('success', 'Retur penjualan berhasil diperbarui.');
    }

    public function destroy(SalesReturn $salesReturn)
    {
        if (! in_array($salesReturn->status, ['pending', 'rejected'])) {
            return back()->withErrors(['error' => 'Retur yang sudah disetujui tidak dapat dihapus.']);
        }

        $salesReturn->delete();

        return to_route('apps.sales-returns.index')->with('success', 'Retur penjualan berhasil dihapus.');
    }

    public function approve(SalesReturn $salesReturn)
    {
        if ($salesReturn->status !== 'pending') {
            return back()->withErrors(['error' => 'Retur sudah diproses sebelumnya.']);
        }

        $user = auth()->user();

        try {
            DB::transaction(function () use ($salesReturn, $user) {
                foreach ($salesReturn->items as $line) {
                    $item = Item::lockForUpdate()->findOrFail($line->item_id);

                    $stockBefore = $item->current_stock;
                    $item->current_stock = $stockBefore + $line->qty;
                    $item->save();

                    StockMovement::create([
                        'item_id' => $item->id,
                        'user_id' => $user->id,
                        'type' => 'in',
                        'qty' => $line->qty,
                        'stock_before' => $stockBefore,
                        'balance_after' => $item->current_stock,
                        'ref_type' => SalesReturn::class,
                        'ref_id' => $salesReturn->id,
                        'notes' => 'Retur penjualan ' . $salesReturn->return_number,
                    ]);
                }

                $salesReturn->update([
                    'status' => 'approved',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                ]);
            });

            return back()->with('success', 'Retur disetujui, stok barang dikembalikan ke gudang.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menyetujui retur: ' . $e->getMessage()]);
        }
    }

    public function reject(SalesReturn $salesReturn)
    {
        if ($salesReturn->status !== 'pending') {
            return back()->withErrors(['error' => 'Retur sudah diproses sebelumnya.']);
        }

        $salesReturn->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Retur ditolak.');
    }
}