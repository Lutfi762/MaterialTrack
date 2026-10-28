<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Send weekly inventory report every Monday at 08:00
Schedule::command('report:weekly')->weekly()->mondays()->at('08:00');

// Email alert stok menipis/habis setiap hari jam 07:30
Schedule::command('report:low-stock')->daily()->at('07:30');

// Regenerasi notifikasi in-app (stok, approval, expiring) tiap jam, menit :15
// (offset dari job harian lain agar tidak start bersamaan)
Schedule::call(fn () => \App\Services\NotificationService::generateAll())->hourlyAt(15);

// Pengingat invoice pembelian & penjualan mendekati/telah jatuh tempo
// Digeser ke 08:20 agar tidak tumpang tindih dengan report:weekly (Senin 08:00)
Schedule::command('invoices:due-reminders')->daily()->at('08:20');


// Generate auto-PR for low stock items
Schedule::command('reorder:generate-pr')->daily()->at('06:45');

