<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\StockOpname;
use App\Models\StockMovement;
use App\Models\Item;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Stock In summary
        $stockInCount = StockIn::whereBetween('date', [$dateFrom, $dateTo])->count();
        $stockInQty = StockIn::whereBetween('date', [$dateFrom, $dateTo])->sum('qty');

        // Stock Out summary
        $stockOutCount = StockOut::whereBetween('date', [$dateFrom, $dateTo])->count();
        $stockOutQty = StockOut::whereBetween('date', [$dateFrom, $dateTo])->sum('qty');
        $pendingApproval = StockOut::where('status', 'pending')->count();

        // Stock Opname summary
        $opnameCount = StockOpname::whereBetween('date', [$dateFrom, $dateTo])->count();
        $opnameSurplus = StockOpname::whereBetween('date', [$dateFrom, $dateTo])->where('difference', '>', 0)->count();
        $opnameDefisit = StockOpname::whereBetween('date', [$dateFrom, $dateTo])->where('difference', '<', 0)->count();

        // Stock Movements
        $movementCount = StockMovement::whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])->count();

        // Item stats
        $totalItems = Item::count();
        $lowStockItems = Item::whereColumn('current_stock', '<=', 'min_stock')->count();
        $outOfStock = Item::where('current_stock', '<=', 0)->count();

        return Inertia::render('Apps/Reports/Index', [
            'summary' => [
                'stock_in_count' => $stockInCount,
                'stock_in_qty' => $stockInQty,
                'stock_out_count' => $stockOutCount,
                'stock_out_qty' => $stockOutQty,
                'pending_approval' => $pendingApproval,
                'opname_count' => $opnameCount,
                'opname_surplus' => $opnameSurplus,
                'opname_defisit' => $opnameDefisit,
                'movement_count' => $movementCount,
                'total_items' => $totalItems,
                'low_stock_items' => $lowStockItems,
                'out_of_stock' => $outOfStock,
            ],
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
        ]);
    }
}
