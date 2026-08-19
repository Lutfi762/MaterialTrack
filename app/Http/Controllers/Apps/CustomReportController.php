<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\StockMovement;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CustomReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Apps/CustomReports/Index', [
            'departments' => \App\Models\Department::orderBy('name')->get(['id', 'name']),
            'categories' => \App\Models\Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:stock_in,stock_out,movements,items',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'department_id' => 'nullable|exists:departments,id',
            'category_id' => 'nullable|exists:categories,id',
            'item_id' => 'nullable|exists:items,id',
        ]);

        $data = match ($validated['type']) {
            'stock_in' => $this->getStockInData($validated),
            'stock_out' => $this->getStockOutData($validated),
            'movements' => $this->getMovementsData($validated),
            'items' => $this->getItemsData($validated),
        };

        return Inertia::render('Apps/CustomReports/Index', [
            'departments' => \App\Models\Department::orderBy('name')->get(['id', 'name']),
            'categories' => \App\Models\Category::orderBy('name')->get(['id', 'name']),
            'report_data' => $data,
            'report_type' => $validated['type'],
            'filters' => $validated,
        ]);
    }

    private function getStockInData(array $filters)
    {
        $query = StockIn::with('item:id,name,code,unit')
            ->whereBetween('date', [$filters['date_from'], $filters['date_to']]);

        if (!empty($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }
        if (!empty($filters['category_id'])) {
            $query->whereHas('item', fn($q) => $q->where('category_id', $filters['category_id']));
        }
        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        $records = $query->orderBy('date', 'DESC')->get();

        return [
            'records' => $records->map(fn($r) => [
                'id' => $r->id,
                'date' => $r->date,
                'item_name' => $r->item->name ?? '-',
                'item_code' => $r->item->code ?? '-',
                'unit' => $r->item->unit ?? '-',
                'qty' => $r->qty,
                'source' => $r->source,
                'notes' => $r->notes,
            ]),
            'summary' => [
                'total_records' => $records->count(),
                'total_qty' => $records->sum('qty'),
            ],
        ];
    }

    private function getStockOutData(array $filters)
    {
        $query = StockOut::with('item:id,name,code,unit', 'department:id,name')
            ->whereBetween('date', [$filters['date_from'], $filters['date_to']]);

        if (!empty($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }
        if (!empty($filters['category_id'])) {
            $query->whereHas('item', fn($q) => $q->where('category_id', $filters['category_id']));
        }
        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        $records = $query->orderBy('date', 'DESC')->get();

        return [
            'records' => $records->map(fn($r) => [
                'id' => $r->id,
                'date' => $r->date,
                'item_name' => $r->item->name ?? '-',
                'item_code' => $r->item->code ?? '-',
                'unit' => $r->item->unit ?? '-',
                'qty' => $r->qty,
                'department' => $r->department->name ?? '-',
                'purpose' => $r->purpose,
                'status' => $r->status,
            ]),
            'summary' => [
                'total_records' => $records->count(),
                'total_qty' => $records->sum('qty'),
                'approved' => $records->where('status', 'approved')->count(),
                'pending' => $records->where('status', 'pending')->count(),
            ],
        ];
    }

    private function getMovementsData(array $filters)
    {
        $query = StockMovement::with('item:id,name,code,unit')
            ->whereBetween(DB::raw('DATE(created_at)'), [$filters['date_from'], $filters['date_to']]);

        if (!empty($filters['item_id'])) {
            $query->where('item_id', $filters['item_id']);
        }
        if (!empty($filters['category_id'])) {
            $query->whereHas('item', fn($q) => $q->where('category_id', $filters['category_id']));
        }

        $records = $query->orderBy('created_at', 'DESC')->get();

        return [
            'records' => $records->map(fn($r) => [
                'id' => $r->id,
                'date' => $r->created_at->format('Y-m-d H:i'),
                'item_name' => $r->item->name ?? '-',
                'item_code' => $r->item->code ?? '-',
                'type' => $r->type,
                'qty' => $r->qty,
                'stock_before' => $r->stock_before,
                'balance_after' => $r->balance_after,
            ]),
            'summary' => [
                'total_records' => $records->count(),
                'total_in' => $records->where('type', 'in')->sum('qty'),
                'total_out' => $records->where('type', 'out')->sum('qty'),
            ],
        ];
    }

    private function getItemsData(array $filters)
    {
        $query = Item::with('category:id,name');

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        $records = $query->orderBy('name')->get();

        $lowStockCount = $records->filter(fn($r) => $r->current_stock <= $r->min_stock)->count();

        return [
            'records' => $records->map(fn($r) => [
                'id' => $r->id,
                'code' => $r->code,
                'name' => $r->name,
                'category' => $r->category->name ?? '-',
                'unit' => $r->unit,
                'current_stock' => $r->current_stock,
                'min_stock' => $r->min_stock,
                'status' => $r->stock_status,
            ]),
            'summary' => [
                'total_items' => $records->count(),
                'total_stock' => $records->sum('current_stock'),
                'low_stock' => $lowStockCount,
            ],
        ];
    }
}
