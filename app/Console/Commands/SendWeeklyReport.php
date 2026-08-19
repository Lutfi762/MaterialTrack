<?php

namespace App\Console\Commands;

use App\Mail\WeeklyReportMail;
use App\Models\Item;
use App\Models\StockIn;
use App\Models\StockOut;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklyReport extends Command
{
    protected $signature = 'report:weekly';
    protected $description = 'Send weekly inventory report to admin users';

    public function handle(): int
    {
        $weekAgo = Carbon::now()->subWeek();
        $now = Carbon::now();

        // Gather stats
        $data = [
            'period' => $weekAgo->format('d M Y') . ' - ' . $now->format('d M Y'),
            'total_items' => Item::count(),
            'low_stock_items' => Item::whereRaw('current_stock > 0 AND current_stock <= min_stock')->count(),
            'out_of_stock_items' => Item::where('current_stock', '<=', 0)->count(),
            'stock_in_count' => StockIn::where('created_at', '>=', $weekAgo)->count(),
            'stock_in_qty' => StockIn::where('created_at', '>=', $weekAgo)->sum('qty'),
            'stock_out_count' => StockOut::where('created_at', '>=', $weekAgo)->where('status', 'approved')->count(),
            'stock_out_qty' => StockOut::where('created_at', '>=', $weekAgo)->where('status', 'approved')->sum('qty'),
            'pending_approvals' => StockOut::where('status', 'pending')->count(),
            'top_out_items' => StockOut::selectRaw('item_id, SUM(qty) as total_qty')
                ->where('created_at', '>=', $weekAgo)
                ->where('status', 'approved')
                ->groupBy('item_id')
                ->orderByDesc('total_qty')
                ->limit(5)
                ->with('item:id,name,code,unit')
                ->get(),
            'low_stock_list' => Item::whereRaw('current_stock > 0 AND current_stock <= min_stock')
                ->orderBy('current_stock')
                ->limit(10)
                ->get(['id', 'code', 'name', 'current_stock', 'min_stock', 'unit']),
        ];

        // Send to all admin users
        $admins = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->get();

        if ($admins->isEmpty()) {
            $this->warn('No admin users found. Report not sent.');
            return self::SUCCESS;
        }

        foreach ($admins as $admin) {
            Mail::to($admin->email)->queue(new WeeklyReportMail($data));
            $this->info("Report queued for: {$admin->email}");
        }

        $this->info('Weekly report sent successfully.');
        return self::SUCCESS;
    }
}
