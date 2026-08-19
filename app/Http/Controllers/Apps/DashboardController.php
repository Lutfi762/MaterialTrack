<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Item;
use App\Models\StockIn;
use App\Models\StockMovement;
use App\Models\StockOut;
use App\Services\CacheService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user();

        // Date range filter
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Determine department filter
        $departmentId = null;
        if ($user->hasRole('admin')) {
            // Admin can filter by department via query param
            $departmentId = $request->input('department_id');
        } else {
            // Non-admin users are auto-filtered to their own department
            $departmentId = $user->department_id;
        }

        // Use CacheService untuk dashboard statistics
        $dashboardStats = CacheService::getDashboardStats($departmentId, $dateFrom, $dateTo);

        // Extract data dari cached stats (arrays after toArray())
        $stockOutStats = $dashboardStats['stock_out_stats'] ?? [];
        $topMaterials = $dashboardStats['top_materials'] ?? [];
        $stockSummary = $dashboardStats['stock_summary'] ?? [];

        // Format data untuk charts dari cache
        $sales_date = [];
        $grand_total = [];

        if (count($stockOutStats) > 0) {
            foreach ($stockOutStats as $result) {
                // Normalisasi data cache (array / stdClass / __PHP_Incomplete_Class) ke array
                $result = (array) $result;
                $sales_date[] = $result['day'] ?? date('Y-m-d');
                $grand_total[] = (int) ($result['total_qty'] ?? 0);
            }
        } else {
            $sales_date[] = 'Belum Ada Transaksi';
            $grand_total[] = 0;
        }

        // Format top materials dari cache
        $product = [];
        $total = [];

        if (count($topMaterials) > 0) {
            foreach ($topMaterials as $data) {
                // Normalisasi data cache (array / stdClass / __PHP_Incomplete_Class) ke array
                $data = (array) $data;
                $product[] = $data['name'] ?? 'Unknown';
                $total[] = (int) ($data['total_out'] ?? 0);
            }
        } else {
            $product[] = 'Tidak Ada Data';
            $total[] = 0;
        }

        // Calculate stock in/out totals dari cached summary
        $material_in_today = (object) [
            'total_transaction' => 0,
            'total_qty' => 0,
        ];

        $material_out_today = (object) [
            'total_transaction' => 0,
            'total_qty' => 0,
        ];

        foreach ($stockSummary as $summary) {
            // Normalisasi data cache (array / stdClass / __PHP_Incomplete_Class) ke array
            $summary = (array) $summary;
            $type = $summary['type'] ?? '';
            $transactionCount = $summary['transaction_count'] ?? 0;
            $totalQty = $summary['total_qty'] ?? 0;

            if ($type === 'in') {
                $material_in_today->total_transaction = $transactionCount;
                $material_in_today->total_qty = $totalQty;
            } elseif ($type === 'out') {
                $material_out_today->total_transaction = $transactionCount;
                $material_out_today->total_qty = $totalQty;
            }
        }

        // Calculate stock status - kita perlu hitung dari database untuk sekarang
        // (ini bisa di-improve dengan caching di CacheService)
        $stock_aman = 0;
        $stock_menipis = 0;
        $stock_habis = 0;

        // Get item IDs untuk department filter (jika diperlukan untuk additional queries)
        $itemIds = null;
        if ($departmentId) {
            $itemIds = DB::table('items')
                ->join('categories', 'items.category_id', '=', 'categories.id')
                ->join('category_department', 'categories.id', '=', 'category_department.category_id')
                ->where('category_department.department_id', $departmentId)
                ->pluck('items.id')
                ->toArray();
        }

        // Hitung stock stats dari database
        $stockStatsQuery = DB::table('items')
            ->selectRaw('
                SUM(CASE WHEN current_stock > min_stock THEN 1 ELSE 0 END) as aman,
                SUM(CASE WHEN current_stock > 0 AND current_stock <= min_stock THEN 1 ELSE 0 END) as menipis,
                SUM(CASE WHEN current_stock <= 0 THEN 1 ELSE 0 END) as habis
            ');

        if ($itemIds !== null) {
            $stockStatsQuery->whereIn('id', $itemIds);
        }

        $stockStats = $stockStatsQuery->first();

        $stock_aman = (int) ($stockStats->aman ?? 0);
        $stock_menipis = (int) ($stockStats->menipis ?? 0);
        $stock_habis = (int) ($stockStats->habis ?? 0);

        // 6. Tabel Material Stok Menipis (only select needed columns)
        $lowStockQuery = Item::with('category:id,name')
            ->select(['id', 'name', 'code', 'unit', 'current_stock', 'min_stock', 'category_id'])
            ->whereColumn('current_stock', '<=', 'min_stock');

        if ($itemIds !== null) {
            $lowStockQuery->whereIn('id', $itemIds);
        }

        $low_stock_items = $lowStockQuery
            ->orderBy('current_stock', 'ASC')
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'unit' => $item->unit,
                'current_stock' => $item->current_stock,
                'min_stock' => $item->min_stock,
            ]);

        // 6b. Material Mendekati Kedaluwarsa (30 hari)
        $expiringQuery = StockIn::with('item:id,name,code,unit')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', Carbon::now()->addDays(30))
            ->where('expiry_date', '>=', Carbon::now())
            ->orderBy('expiry_date', 'ASC');

        if ($itemIds !== null) {
            $expiringQuery->whereIn('item_id', $itemIds);
        }

        $expiring_items = $expiringQuery->limit(10)->get()
            ->map(fn ($si) => [
                'id' => $si->id,
                'item_name' => $si->item->name ?? '-',
                'item_code' => $si->item->code ?? '-',
                'unit' => $si->item->unit ?? '-',
                'qty' => $si->qty,
                'expiry_date' => $si->expiry_date,
                'days_left' => Carbon::now()->diffInDays(Carbon::parse($si->expiry_date), false),
            ]);

        // 6c. Reorder Suggestion (berbasis rata-rata pemakaian 30 hari)
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $outMovementQuery = StockMovement::where('type', 'out')
            ->where('created_at', '>=', $thirtyDaysAgo);
        if ($itemIds !== null) {
            $outMovementQuery->whereIn('item_id', $itemIds);
        }
        $avgUsage = $outMovementQuery
            ->select('item_id', DB::raw('SUM(qty) as total_out'))
            ->groupBy('item_id')
            ->get()
            ->mapWithKeys(fn ($row) => [
                $row->item_id => round($row->total_out / 30, 2),
            ]);

        $restockSuggestions = Item::whereIn('id', array_keys($avgUsage->toArray()))
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->get(['id', 'name', 'code', 'unit', 'current_stock', 'min_stock'])
            ->map(function ($item) use ($avgUsage) {
                $daily = $avgUsage[$item->id] ?? 0;
                $daysLeft = $daily > 0 ? floor($item->current_stock / $daily) : null;
                $reorderQty = max(0, ($item->min_stock * 2) - $item->current_stock);

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'unit' => $item->unit,
                    'current_stock' => $item->current_stock,
                    'min_stock' => $item->min_stock,
                    'avg_daily' => $daily,
                    'days_left' => $daysLeft,
                    'reorder_qty' => $reorderQty,
                ];
            })
            ->sortBy('days_left')
            ->values()
            ->take(5);

        // 7. Perbandingan Stok Antar Periode (Bulan Ini vs Bulan Lalu)
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $movementQuery = StockMovement::query();
        if ($itemIds !== null) {
            $movementQuery->whereIn('item_id', $itemIds);
        }

        // Total In/Out bulan ini
        $inThisMonth = (clone $movementQuery)
            ->where('type', 'in')
            ->where('created_at', '>=', $thisMonth)
            ->sum('qty');

        $outThisMonth = (clone $movementQuery)
            ->where('type', 'out')
            ->where('created_at', '>=', $thisMonth)
            ->sum('qty');

        // Total In/Out bulan lalu
        $inLastMonth = (clone $movementQuery)
            ->where('type', 'in')
            ->where('created_at', '>=', $lastMonth)
            ->where('created_at', '<=', $lastMonthEnd)
            ->sum('qty');

        $outLastMonth = (clone $movementQuery)
            ->where('type', 'out')
            ->where('created_at', '>=', $lastMonth)
            ->where('created_at', '<=', $lastMonthEnd)
            ->sum('qty');

        $calcChange = function ($current, $previous) {
            if ($previous == 0) {
                return $current > 0 ? 100 : 0;
            }

            return round((($current - $previous) / $previous) * 100, 1);
        };

        // Role-based: Recent stock movements for department users
        $recentMovements = [];
        if (! $user->hasRole('admin') && $departmentId) {
            $recentMovements = StockMovement::with('item')
                ->whereIn('item_id', $itemIds)
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn ($m) => [
                    'id' => $m->id,
                    'type' => $m->type,
                    'qty' => $m->qty,
                    'item_name' => $m->item->name ?? '-',
                    'created_at' => $m->created_at->format('d M Y H:i'),
                ])
                ->toArray();
        }

        // Role-based: My pending approvals for department users
        $myPendingApprovals = 0;
        if (! $user->hasRole('admin')) {
            $myPendingApprovals = StockOut::where('status', 'pending')
                ->whereIn('item_id', $itemIds ?? [])
                ->count();
        }

        return Inertia::render('Dashboard/Index', [
            'sales_date' => $sales_date,
            'grand_total' => $grand_total,

            'material_out_today' => [
                'total_transaction' => (int) ($material_out_today->total_transaction ?? 0),
                'total_qty' => (int) ($material_out_today->total_qty ?? 0),
            ],
            'material_in_today' => [
                'total_transaction' => (int) ($material_in_today->total_transaction ?? 0),
                'total_qty' => (int) ($material_in_today->total_qty ?? 0),
            ],

            'product' => $product,
            'total' => $total,

            'stock_status' => [
                'labels' => ['Aman', 'Menipis', 'Habis'],
                'data' => [$stock_aman, $stock_menipis, $stock_habis],
            ],

            'low_stock_items' => $low_stock_items->toArray(),
            'low_stock_count' => $stock_menipis + $stock_habis,

            'expiring_items' => $expiring_items->toArray(),

            'restock_suggestions' => $restockSuggestions->toArray(),

            // Total nilai inventaris
            'total_inventory_value' => (float) Item::whereNotNull('price')
                ->selectRaw('SUM(price * current_stock) as total')
                ->value('total') ?? 0,

            'period_comparison' => [
                'stock_in' => [
                    'this_month' => (int) $inThisMonth,
                    'last_month' => (int) $inLastMonth,
                    'change' => $calcChange($inThisMonth, $inLastMonth),
                ],
                'stock_out' => [
                    'this_month' => (int) $outThisMonth,
                    'last_month' => (int) $outLastMonth,
                    'change' => $calcChange($outThisMonth, $outLastMonth),
                ],
            ],

            'recent_movements' => $recentMovements,
            'my_pending_approvals' => $myPendingApprovals,

            'date_from' => $dateFrom,
            'date_to' => $dateTo,

            // For filter dropdown
            'departments' => $user->hasRole('admin') ? Department::orderBy('name')->get(['id', 'name']) : [],
            'current_department_id' => $departmentId,
            'is_admin' => $user->hasRole('admin'),
        ]);
    }

    public function downloadPdf(Request $request)
    {
        $user = auth()->user();
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        $totalItems = Item::count();
        $lowStockItems = Item::whereColumn('current_stock', '<=', 'min_stock')
            ->orderBy('name')
            ->get();
        $availableStock = Item::whereColumn('current_stock', '>', 'min_stock')->count();
        $lowStock = $lowStockItems->count();
        $outOfStock = Item::where('current_stock', '<=', 0)->count();

        $stockInCount = StockIn::whereBetween('date', [$dateFrom, $dateTo])->count();
        $stockInQty = StockIn::whereBetween('date', [$dateFrom, $dateTo])->sum('qty');
        $stockOutCount = StockOut::whereBetween('date', [$dateFrom, $dateTo])->count();
        $stockOutQty = StockOut::whereBetween('date', [$dateFrom, $dateTo])->sum('qty');
        $pendingApproval = StockOut::where('status', 'pending')->count();

        $topMaterials = StockOut::where('stock_outs.created_at', '>=', now()->subDays(7))
            ->join('items', 'stock_outs.item_id', '=', 'items.id')
            ->select('items.name', DB::raw('SUM(stock_outs.qty) as total'))
            ->groupBy('items.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $pdf = Pdf::loadView('pdf.dashboard', [
            'totalItems' => $totalItems,
            'availableStock' => $availableStock,
            'lowStock' => $lowStock,
            'outOfStock' => $outOfStock,
            'lowStockItems' => $lowStockItems,
            'stockInCount' => $stockInCount,
            'stockInQty' => $stockInQty,
            'stockOutCount' => $stockOutCount,
            'stockOutQty' => $stockOutQty,
            'pendingApproval' => $pendingApproval,
            'topMaterials' => $topMaterials,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);

        return $pdf->download('MaterialTrack-Dashboard-'.now()->format('Ymd-His').'.pdf');
    }
}
