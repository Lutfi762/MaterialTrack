<?php

namespace App\Console\Commands;

use App\Mail\LowStockAlertMail;
use App\Models\Item;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendLowStockAlert extends Command
{
    protected $signature = 'report:low-stock';
    protected $description = 'Kirim email alert ke admin jika ada stok menipis/habis';

    public function handle(): int
    {
        $lowStockItems = Item::lowStock()->orderBy('name')->get();
        $outOfStockItems = Item::outOfStock()->orderBy('name')->get();

        if ($lowStockItems->isEmpty() && $outOfStockItems->isEmpty()) {
            $this->info('Tidak ada stok menipis atau habis. Email tidak dikirim.');
            return self::SUCCESS;
        }

        $adminEmails = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))
            ->pluck('email')
            ->toArray();

        if (empty($adminEmails)) {
            $this->error('Tidak ada admin ditemukan.');
            return self::FAILURE;
        }

        $data = [
            'low_stock_items' => $lowStockItems,
            'out_of_stock_items' => $outOfStockItems,
            'total_low' => $lowStockItems->count(),
            'total_out' => $outOfStockItems->count(),
        ];

        foreach ($adminEmails as $email) {
            Mail::to($email)->send(new LowStockAlertMail($data));
        }

        $this->info("Email alert dikirim ke " . count($adminEmails) . " admin.");
        $this->info("  Stok menipis: {$lowStockItems->count()}");
        $this->info("  Stok habis: {$outOfStockItems->count()}");

        return self::SUCCESS;
    }
}
