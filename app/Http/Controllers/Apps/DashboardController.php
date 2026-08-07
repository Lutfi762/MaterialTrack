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
    public function __invoke(Request $request)
    {
        $today    = Carbon::today()->toDateTimeString();
        $tomorrow = Carbon::tomorrow()->toDateTimeString();
        $week     = Carbon::now()->subDays(7)->toDateTimeString();

        // 1. Chart Pengeluaran Material 7 Hari Terakhir
        $chart_stock_out_week = DB::table('stock_outs')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(qty) as grand_total'))
            ->where('created_at', '>=', $week)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'ASC')
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

        // 2. Material Keluar Hari Ini
        $material_out_today = DB::table('stock_outs')
            ->where('created_at', '>=', $today)
            ->where('created_at', '<', $tomorrow)
            ->selectRaw('COUNT(*) as total_transaction, SUM(qty) as total_qty')
            ->first();

        // 3. Material Masuk Hari Ini
        $material_in_today = DB::table('stock_ins')
            ->where('created_at', '>=', $today)
            ->where('created_at', '<', $tomorrow)
            ->selectRaw('COUNT(*) as total_transaction, SUM(qty) as total_qty')
            ->first();

        // 4. Top 5 Material Terbanyak Dikeluarkan
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

        // 5. Status Stok (Aman / Menipis / Habis)
        $stock_aman    = Item::whereColumn('current_stock', '>', 'min_stock')->count();
        $stock_menipis = Item::where('current_stock', '>', 0)
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->count();
        $stock_habis   = Item::where('current_stock', '<=', 0)->count();

        // 6. Tabel Material Stok Menipis
        $low_stock_items = Item::with('category')
            ->whereColumn('current_stock', '<=', 'min_stock')
            ->orderBy('current_stock', 'ASC')
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'id'            => $item->id,
                'name'          => $item->name,
                'code'          => $item->code,
                'unit'          => $item->unit,
                'current_stock' => $item->current_stock,
                'min_stock'     => $item->min_stock,
            ]);

        return Inertia::render('Dashboard/Index', [
            'sales_date'  => $sales_date,
            'grand_total' => $grand_total,

            'material_out_today' => [
                'total_transaction' => (int) ($material_out_today->total_transaction ?? 0),
                'total_qty'         => (int) ($material_out_today->total_qty ?? 0),
            ],
            'material_in_today' => [
                'total_transaction' => (int) ($material_in_today->total_transaction ?? 0),
                'total_qty'         => (int) ($material_in_today->total_qty ?? 0),
            ],

            'product' => $product,
            'total'   => $total,

            'stock_status' => [
                'labels' => ['Aman', 'Menipis', 'Habis'],
                'data'   => [$stock_aman, $stock_menipis, $stock_habis],
            ],

            'low_stock_items' => $low_stock_items,
            'low_stock_count' => $stock_menipis + $stock_habis,
        ]);
    }
}