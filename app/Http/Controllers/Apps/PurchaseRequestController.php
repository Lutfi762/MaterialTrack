<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Item;
use App\Models\PurchaseRequest;
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseRequest::with(['department', 'requester', 'approver'])->latest();

        if ($request->filled('search')) {
            $query->where('pr_number', 'like', "%{$request->search}%");
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return Inertia::render('Apps/PurchaseRequests/Index', [
            'purchaseRequests' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/PurchaseRequests/Create', [
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'items' => Item::orderBy('name')->get(['id', 'name', 'code', 'unit', 'price']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'needed_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.estimated_price' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $prNumber = DocumentNumber::next('purchase_requests', 'PR');

            $pr = PurchaseRequest::create([
                'pr_number' => $prNumber,
                'department_id' => $validated['department_id'],
                'requested_by' => auth()->id(),
                'request_date' => now(),
                'needed_date' => $validated['needed_date'] ?? null,
                'priority' => $validated['priority'],
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $pr->items()->create([
                    'item_id' => $item['item_id'],
                    'qty' => $item['qty'],
                    'estimated_price' => $item['estimated_price'] ?? 0,
                ]);
            }
        });

        return to_route('apps.purchase-requests.index')->with('success', 'Permintaan Pembelian (PR) berhasil diajukan.');
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        return Inertia::render('Apps/PurchaseRequests/Show', [
            'purchaseRequest' => $purchaseRequest->load(['department', 'requester', 'approver', 'items.item']),
        ]);
    }

    public function edit(PurchaseRequest $purchaseRequest)
    {
        return Inertia::render('Apps/PurchaseRequests/Edit', [
            'purchaseRequest' => $purchaseRequest->load(['items']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'items' => Item::orderBy('name')->get(['id', 'name', 'code', 'unit', 'price']),
        ]);
    }

    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (! in_array($purchaseRequest->status, ['draft', 'pending', 'rejected'])) {
            return back()->withErrors(['status' => 'Hanya PR berstatus draft/pending/rejected yang dapat diedit.']);
        }

        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'needed_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high,urgent',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.estimated_price' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $purchaseRequest) {
            $purchaseRequest->update([
                'department_id' => $validated['department_id'],
                'needed_date' => $validated['needed_date'] ?? null,
                'priority' => $validated['priority'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $purchaseRequest->items()->delete();
            foreach ($validated['items'] as $item) {
                $purchaseRequest->items()->create([
                    'item_id' => $item['item_id'],
                    'qty' => $item['qty'],
                    'estimated_price' => $item['estimated_price'] ?? 0,
                ]);
            }
        });

        return to_route('apps.purchase-requests.index')->with('success', 'Permintaan Pembelian (PR) berhasil diperbarui.');
    }

    public function destroy(PurchaseRequest $purchaseRequest)
    {
        if (! in_array($purchaseRequest->status, ['draft', 'pending', 'rejected'])) {
            return back()->withErrors(['status' => 'PR berstatus approved tidak dapat dihapus.']);
        }
        if (\App\Models\PurchaseOrder::where('purchase_request_id', $purchaseRequest->id)->exists()) {
            return back()->withErrors(['status' => 'PR ini sudah dipakai sebagai referensi Purchase Order, tidak dapat dihapus.']);
        }

        $purchaseRequest->delete();

        return to_route('apps.purchase-requests.index')->with('success', 'Permintaan Pembelian (PR) berhasil dihapus.');
    }

    public function approve(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (! in_array($purchaseRequest->status, ['pending', 'draft'])) {
            return back()->withErrors(['status' => 'Hanya PR berstatus pending yang dapat disetujui.']);
        }

        $purchaseRequest->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Purchase Request berhasil disetujui.');
    }

    public function reject(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (! in_array($purchaseRequest->status, ['pending', 'draft'])) {
            return back()->withErrors(['status' => 'Hanya PR berstatus pending yang dapat ditolak.']);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $purchaseRequest->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', 'Purchase Request ditolak.');
    }
}
