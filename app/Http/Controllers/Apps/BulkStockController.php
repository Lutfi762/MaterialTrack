<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\BulkStockOperation;
use App\Models\Item;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BulkStockController extends Controller
{
    public function index()
    {
        $operations = BulkStockOperation::with('user', 'items.item')->latest()->paginate(20);

        return Inertia::render('Apps/BulkStock/Index', ['operations' => $operations]);
    }

    public function create()
    {
        $items = Item::orderBy('name')->get(['id', 'name', 'code', 'current_stock', 'unit']);

        return Inertia::render('Apps/BulkStock/Create', ['items' => $items]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.source' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $operation = BulkStockOperation::create([
                'user_id' => auth()->id(),
                'type' => $validated['type'],
                'status' => 'completed',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);
                $operation->items()->create($itemData);

                if ($validated['type'] === 'in') {
                    $item->increment('current_stock', $itemData['qty']);
                    StockIn::create([
                        'item_id' => $item->id,
                        'qty' => $itemData['qty'],
                        'source' => $itemData['source'] ?? 'Bulk Input',
                        'user_id' => auth()->id(),
                        'reference_no' => 'IN-'.now()->format('Ymd').'-'.str_pad(StockIn::whereDate('created_at', now())->count() + 1, 4, '0', STR_PAD_LEFT),
                        'date' => now()->toDateString(),
                    ]);
                } else {
                    if ($item->current_stock < $itemData['qty']) {
                        throw new \Exception("Stok {$item->name} tidak cukup. Tersedia: {$item->current_stock}");
                    }
                    $item->decrement('current_stock', $itemData['qty']);
                    StockOut::create([
                        'item_id' => $item->id,
                        'qty' => $itemData['qty'],
                        'user_id' => auth()->id(),
                        'department_id' => auth()->user()->department_id,
                        'reference_no' => 'OUT-'.now()->format('Ymd').'-'.str_pad(StockOut::whereDate('created_at', now())->count() + 1, 4, '0', STR_PAD_LEFT),
                        'date' => now()->toDateString(),
                        'status' => 'approved',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);
                }
            }

            ActivityLogService::log('bulk_stock.'.$validated['type'], $operation, [
                'item_count' => count($validated['items']),
            ]);
        });

        return to_route('apps.bulk-stock.index')->with('success', 'Bulk operation completed.');
    }

    public function show(BulkStockOperation $bulkOperation)
    {
        $bulkOperation->load('user', 'items.item');

        return Inertia::render('Apps/BulkStock/Show', ['operation' => $bulkOperation]);
    }
}
