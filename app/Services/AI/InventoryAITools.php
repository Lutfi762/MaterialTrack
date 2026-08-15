<?php

namespace App\Services\AI;

use App\Models\Item;
use App\Models\StockMovement;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\Department;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryAITools
{
    protected ?int $departmentId;
    protected bool $isAdmin;
    protected int $resultLimit;

    public function __construct(?int $departmentId = null, bool $isAdmin = false)
    {
        $this->departmentId = $departmentId;
        $this->isAdmin = $isAdmin;
        $this->resultLimit = config('ai.assistant.query_result_limit', 20);
    }

    /**
     * Get stock summary statistics.
     */
    public function getStockSummary(): array
    {
        $query = Item::query();

        if (!$this->isAdmin && $this->departmentId) {
            $query->whereHas('category.departments', function ($q) {
                $q->where('departments.id', $this->departmentId);
            });
        }

        $totalItems = $query->count();
        $stockAman = (clone $query)->where('current_stock', '>=', DB::raw('min_stock'))->count();
        $stockMenipis = (clone $query)->whereRaw('current_stock < min_stock')->where('current_stock', '>', 0)->count();
        $stockHabis = (clone $query)->where('current_stock', 0)->count();

        return [
            'total_materials' => $totalItems,
            'stock_aman' => $stockAman,
            'stock_menipis' => $stockMenipis,
            'stock_habis' => $stockHabis,
        ];
    }

    /**
     * Get items with low stock.
     */
    public function getLowStockItems(): array
    {
        $query = Item::with(['category', 'location'])
            ->select(['id', 'code', 'name', 'current_stock', 'min_stock', 'unit', 'category_id', 'location_id'])
            ->whereRaw('current_stock < min_stock')
            ->where('current_stock', '>', 0)
            ->orderBy('current_stock', 'asc');

        if (!$this->isAdmin && $this->departmentId) {
            $query->whereHas('category.departments', function ($q) {
                $q->where('departments.id', $this->departmentId);
            });
        }

        $items = $query->limit($this->resultLimit)->get();

        return $items->map(function ($item) {
            return [
                'code' => $item->code,
                'name' => $item->name,
                'current_stock' => $item->current_stock,
                'min_stock' => $item->min_stock,
                'unit' => $item->unit,
                'category' => $item->category->name ?? '-',
                'location' => $item->location->name ?? '-',
                'stock_status' => $item->stock_status,
            ];
        })->toArray();
    }

    /**
     * Get items that are out of stock.
     */
    public function getOutOfStockItems(): array
    {
        $query = Item::with(['category', 'location'])
            ->select(['id', 'code', 'name', 'current_stock', 'min_stock', 'unit', 'category_id', 'location_id'])
            ->where('current_stock', 0)
            ->orderBy('name', 'asc');

        if (!$this->isAdmin && $this->departmentId) {
            $query->whereHas('category.departments', function ($q) {
                $q->where('departments.id', $this->departmentId);
            });
        }

        $items = $query->limit($this->resultLimit)->get();

        return $items->map(function ($item) {
            return [
                'code' => $item->code,
                'name' => $item->name,
                'min_stock' => $item->min_stock,
                'unit' => $item->unit,
                'category' => $item->category->name ?? '-',
                'location' => $item->location->name ?? '-',
            ];
        })->toArray();
    }

    /**
     * Find item stock by name or code.
     */
    public function findItemStock(string $search): array
    {
        $query = Item::with(['category', 'location'])
            ->select(['id', 'code', 'name', 'current_stock', 'min_stock', 'unit', 'category_id', 'location_id'])
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });

        if (!$this->isAdmin && $this->departmentId) {
            $query->whereHas('category.departments', function ($q) {
                $q->where('departments.id', $this->departmentId);
            });
        }

        $items = $query->limit($this->resultLimit)->get();

        if ($items->isEmpty()) {
            return [];
        }

        return $items->map(function ($item) {
            return [
                'code' => $item->code,
                'name' => $item->name,
                'current_stock' => $item->current_stock,
                'min_stock' => $item->min_stock,
                'unit' => $item->unit,
                'category' => $item->category->name ?? '-',
                'location' => $item->location->name ?? '-',
                'stock_status' => $item->stock_status,
            ];
        })->toArray();
    }

    /**
     * Get recent stock movements.
     */
    public function getRecentStockMovements(int $limit = 10): array
    {
        $query = StockMovement::with(['item', 'user', 'pic'])
            ->select(['id', 'item_id', 'type', 'qty', 'stock_before', 'balance_after', 'notes', 'user_id', 'pic_id', 'created_at'])
            ->orderBy('created_at', 'desc');

        if (!$this->isAdmin && $this->departmentId) {
            $query->whereHas('item.category.departments', function ($q) {
                $q->where('departments.id', $this->departmentId);
            });
        }

        $movements = $query->limit(min($limit, $this->resultLimit))->get();

        return $movements->map(function ($mov) {
            return [
                'date' => $mov->created_at->format('Y-m-d H:i'),
                'material' => $mov->item->name ?? '-',
                'type' => $mov->type === 'in' ? 'Masuk' : 'Keluar',
                'qty' => $mov->qty,
                'stock_before' => $mov->stock_before,
                'stock_after' => $mov->balance_after,
                'notes' => $mov->notes ?? '-',
                'by' => $mov->pic->name ?? $mov->user->name ?? '-',
            ];
        })->toArray();
    }

    /**
     * Get stock in summary for a period.
     */
    public function getStockInSummary(string $period = 'today'): array
    {
        $query = StockIn::with('item');

        [$startDate, $endDate] = $this->getPeriodDates($period);
        $query->whereBetween('date', [$startDate, $endDate]);

        if (!$this->isAdmin && $this->departmentId) {
            $query->whereHas('item.category.departments', function ($q) {
                $q->where('departments.id', $this->departmentId);
            });
        }

        $totalTransactions = $query->count();
        $totalQty = $query->sum('qty');

        return [
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_transactions' => $totalTransactions,
            'total_qty' => $totalQty,
        ];
    }

    /**
     * Get stock out summary for a period.
     */
    public function getStockOutSummary(string $period = 'today'): array
    {
        $query = StockOut::with(['item', 'department']);

        [$startDate, $endDate] = $this->getPeriodDates($period);
        $query->whereBetween('date', [$startDate, $endDate]);

        if (!$this->isAdmin && $this->departmentId) {
            $query->where('department_id', $this->departmentId);
        }

        $totalTransactions = $query->count();
        $totalQty = $query->sum('qty');

        return [
            'period' => $period,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_transactions' => $totalTransactions,
            'total_qty' => $totalQty,
        ];
    }

    /**
     * Get department inventory summary.
     */
    public function getDepartmentInventory(?int $departmentId = null): array
    {
        $targetDeptId = $departmentId ?? $this->departmentId;

        if (!$this->isAdmin && $targetDeptId !== $this->departmentId) {
            return ['error' => 'Access denied to other department data'];
        }

        if (!$targetDeptId) {
            return ['error' => 'Department not specified'];
        }

        $department = Department::find($targetDeptId);
        if (!$department) {
            return ['error' => 'Department not found'];
        }

        $items = Item::whereHas('category.departments', function ($q) use ($targetDeptId) {
            $q->where('departments.id', $targetDeptId);
        })->get();

        return [
            'department' => $department->name,
            'total_materials' => $items->count(),
            'stock_aman' => $items->where('stock_status', 'aman')->count(),
            'stock_menipis' => $items->where('stock_status', 'menipis')->count(),
            'stock_habis' => $items->where('stock_status', 'habis')->count(),
        ];
    }

    /**
     * Get category inventory summary.
     */
    public function getCategoryInventory(?int $categoryId = null): array
    {
        $query = Item::with('category');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if (!$this->isAdmin && $this->departmentId) {
            $query->whereHas('category.departments', function ($q) {
                $q->where('departments.id', $this->departmentId);
            });
        }

        $items = $query->get();

        $categories = $items->groupBy('category_id')->map(function ($group) {
            $category = $group->first()->category;
            return [
                'category' => $category->name ?? '-',
                'total_materials' => $group->count(),
                'stock_aman' => $group->where('stock_status', 'aman')->count(),
                'stock_menipis' => $group->where('stock_status', 'menipis')->count(),
                'stock_habis' => $group->where('stock_status', 'habis')->count(),
            ];
        })->values()->toArray();

        return $categories;
    }

    /**
     * Get location inventory summary.
     */
    public function getLocationInventory(?int $locationId = null): array
    {
        $query = Item::with('location');

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        if (!$this->isAdmin && $this->departmentId) {
            $query->whereHas('category.departments', function ($q) {
                $q->where('departments.id', $this->departmentId);
            });
        }

        $items = $query->get();

        $locations = $items->groupBy('location_id')->map(function ($group) {
            $location = $group->first()->location;
            return [
                'location' => $location->name ?? 'Belum ditentukan',
                'total_materials' => $group->count(),
                'stock_aman' => $group->where('stock_status', 'aman')->count(),
                'stock_menipis' => $group->where('stock_status', 'menipis')->count(),
                'stock_habis' => $group->where('stock_status', 'habis')->count(),
            ];
        })->values()->toArray();

        return $locations;
    }

    /**
     * Get top outgoing materials with optional period filter.
     */
    public function getTopOutgoingMaterials(int $limit = 10, string $period = 'all'): array
    {
        $query = StockOut::with('item')
            ->select('item_id', DB::raw('SUM(qty) as total_qty'), DB::raw('COUNT(*) as total_transactions'))
            ->groupBy('item_id')
            ->orderBy('total_qty', 'desc');

        // Apply period filter if not 'all'
        if ($period !== 'all') {
            [$startDate, $endDate] = $this->getPeriodDates($period);
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        if (!$this->isAdmin && $this->departmentId) {
            $query->where('department_id', $this->departmentId);
        }

        $topMaterials = $query->limit(min($limit, $this->resultLimit))->get();

        return $topMaterials->map(function ($record) {
            return [
                'material' => $record->item->name ?? '-',
                'code' => $record->item->code ?? '-',
                'total_qty' => $record->total_qty,
                'total_transactions' => $record->total_transactions,
                'unit' => $record->item->unit ?? '-',
            ];
        })->toArray();
    }
    /**
     * Parse period string to date range.
     */
    protected function getPeriodDates(string $period): array
    {
        $today = Carbon::today();

        return match (strtolower($period)) {
            'today', 'hari ini' => [$today->format('Y-m-d'), $today->format('Y-m-d')],
            'week', 'minggu ini' => [Carbon::now()->startOfWeek()->format('Y-m-d'), Carbon::now()->endOfWeek()->format('Y-m-d')],
            'month', 'bulan ini' => [Carbon::now()->startOfMonth()->format('Y-m-d'), Carbon::now()->endOfMonth()->format('Y-m-d')],
            'semester' => [Carbon::now()->subMonths(6)->format('Y-m-d'), $today->format('Y-m-d')],
            'year', 'tahun ini' => [Carbon::now()->startOfYear()->format('Y-m-d'), Carbon::now()->endOfYear()->format('Y-m-d')],
            default => [$today->format('Y-m-d'), $today->format('Y-m-d')],
        };
    }
}
