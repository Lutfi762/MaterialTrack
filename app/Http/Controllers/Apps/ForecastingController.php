<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ForecastingController extends Controller
{
    public function index(Request $request)
    {
        $items = Item::with('stockMovements')
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->orWhere(function ($q) {
                $q->whereHas('stockMovements', function ($sq) {
                    $sq->where('created_at', '>=', now()->subDays(30));
                });
            })
            ->get()
            ->map(function ($item) {
                $recentOut = $item->stockMovements->where('type', 'out')
                    ->where('created_at', '>=', now()->subDays(30))->sum('qty');
                $dailyAvg = $recentOut / 30;
                $daysUntilEmpty = $dailyAvg > 0 ? floor($item->current_stock / $dailyAvg) : null;

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'current_stock' => $item->current_stock,
                    'min_stock' => $item->min_stock,
                    'daily_avg_usage' => round($dailyAvg, 2),
                    'days_until_empty' => $daysUntilEmpty,
                    'suggested_restock' => max(0, ($dailyAvg * 30) - $item->current_stock + $item->min_stock),
                    'status' => $daysUntilEmpty !== null && $daysUntilEmpty <= 7 ? 'urgent' : ($daysUntilEmpty !== null && $daysUntilEmpty <= 30 ? 'warning' : 'ok'),
                ];
            })
            ->sortBy('days_until_empty')
            ->values();

        return Inertia::render('Apps/Forecasting/Index', ['items' => $items]);
    }
}
