<?php

namespace App\Http\Controllers\Apps;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $today    = Carbon::today()->toDateTimeString();
        $tomorrow = Carbon::tomorrow()->toDateTimeString();
        $week     = Carbon::now()->subDays(7)->toDateTimeString();

        // 1. Chart Pengeluaran Material (StockOut) 7 Hari Terakhir
        $chart_stock_out_week = DB::table('stock_outs')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(qty) as grand_total'))
            ->where('created_at', '>=', $week)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get();

        $sales_date  = [];
        $grand_total = [];

        if (count($chart_stock_out_week) > 0) {
            foreach ($chart_stock_out_week as $result) {
                $sales_date[]  = $result->date;
                $grand_total[] = (int) $result->grand_total;
            }
        } else {
            $sales_date[]  = "Belum Ada Transaksi";
            $grand_total[] = 0;
        }

        // 2. Ringkasan Transaksi Hari Ini
        $count_sales_today = DB::table('stock_outs')
            ->where('created_at', '>=', $today)
            ->where('created_at', '<', $tomorrow)
            ->count();

        $sum_sales_today = DB::table('stock_outs')
            ->where('created_at', '>=', $today)
            ->where('created_at', '<', $tomorrow)
            ->sum('qty');

        $sum_profits_today = DB::table('stock_ins')
            ->where('created_at', '>=', $today)
            ->where('created_at', '<', $tomorrow)
            ->sum('qty');

        // 3. Material dengan Stok Menipis (Diambil untuk Tabel Kanan Bawah)
        $products_limit_stock = Item::with('category')->limit(5)->get();

        // 4. Top Material Terbanyak Dikeluarkan (Disiapkan untuk Donut Chart)
        $chart_best_products = DB::table('stock_outs')
            ->select('items.name as title', DB::raw('SUM(stock_outs.qty) as total'))
            ->join('items', 'items.id', '=', 'stock_outs.item_id')
            ->groupBy('stock_outs.item_id', 'items.name')
            ->orderBy('total', 'DESC')
            ->limit(5)
            ->get();

        $product = [];
        $total   = [];

        if (count($chart_best_products) > 0) {
            foreach ($chart_best_products as $data) {
                $product[] = $data->title;
                $total[]   = (int) $data->total;
            }
        } else {
            $product[] = "Tidak Ada Data";
            $total[]   = 0;
        }

        return Inertia::render('Dashboard/Index', [
            'sales_date'           => $sales_date,
            'grand_total'          => $grand_total,
            'count_sales_today'    => (int) $count_sales_today,
            'sum_sales_today'      => (int) $sum_sales_today,
            'sum_profits_today'    => (int) $sum_profits_today,
            'products_limit_stock' => $products_limit_stock,
            'product'              => $product,
            'total'                => $total,
        ]);
    }
}