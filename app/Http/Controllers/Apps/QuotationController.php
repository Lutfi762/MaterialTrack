<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Item;
use App\Support\DocumentNumber;
use App\Models\Quotation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $query = Quotation::with(['customer', 'creator'])->latest();

        if ($request->filled('search')) {
            $query->where('quotation_number', 'like', "%{$request->search}%");
        }

        return Inertia::render('Apps/Quotations/Index', [
            'quotations' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/Quotations/Create', [
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(['id', 'name', 'code']),
            'items' => Item::orderBy('name')->get(['id', 'name', 'code', 'unit', 'price']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'valid_until' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $qNumber = DocumentNumber::next('quotations', 'QUO');

            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                $subtotal += $item['qty'] * $item['unit_price'];
            }
            $taxAmount = $subtotal * 0.11;
            $total = $subtotal + $taxAmount;

            $quotation = Quotation::create([
                'quotation_number' => $qNumber,
                'customer_id' => $validated['customer_id'],
                'created_by' => auth()->id(),
                'quotation_date' => now(),
                'valid_until' => $validated['valid_until'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $quotation->items()->create([
                    'item_id' => $item['item_id'],
                    'qty' => $item['qty'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['qty'] * $item['unit_price'],
                ]);
            }
        });

        return to_route('apps.quotations.index')->with('success', 'Penawaran Harga (Quotation) berhasil dibuat.');
    }

    public function show(Quotation $quotation)
    {
        return Inertia::render('Apps/Quotations/Show', [
            'quotation' => $quotation->load(['customer', 'creator', 'items.item']),
        ]);
    }
}
