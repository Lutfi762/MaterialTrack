<?php

namespace App\Console\Commands;

use App\Models\AppNotification;
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
use App\Models\User;
use Illuminate\Console\Command;

class SendInvoiceDueReminders extends Command
{
    protected $signature = 'invoices:due-reminders';

    protected $description = 'Buat notifikasi pengingat invoice (beli/jual) jatuh tempo <=7 hari atau sudah lewat';

    public function handle(): int
    {
        // Semua status di luar draft/paid/cancelled dianggap masih menunggu pelunasan
        $unpaidStatuses = ['pending', 'approved', 'sent', 'partial_paid', 'overdue'];
        $adminIds = User::whereHas('roles', fn ($q) => $q->where('name', 'admin'))->pluck('id');

        if ($adminIds->isEmpty()) {
            $this->error('Tidak ada admin ditemukan.');

            return self::FAILURE;
        }

        $sources = [
            [PurchaseInvoice::query(), 'Invoice Pembelian'],
            [SalesInvoice::query(), 'Invoice Penjualan'],
        ];

        $created = 0;
        foreach ($sources as [$query, $label]) {
            $invoices = (clone $query)
                ->whereIn('status', $unpaidStatuses)
                ->whereBetween('due_date', [now()->subYear(), now()->addDays(7)])
                ->orderBy('due_date')
                ->get();

            foreach ($invoices as $inv) {
                $remaining = max(0, (float) $inv->total - (float) $inv->amount_paid);
                $overdue = $inv->due_date->isPast();
                $title = $overdue ? 'Invoice Jatuh Tempo (Terlambat)' : 'Invoice Mendekati Jatuh Tempo';
                $message = sprintf(
                    '%s %s: jatuh tempo %s (%s), sisa Rp %s',
                    $label,
                    $inv->invoice_number,
                    $inv->due_date->format('d M Y'),
                    $overdue ? 'TERLAMBAT ' . $inv->due_date->diffInDays(now()) . ' hari' : $inv->due_date->diffInDays(now()) . ' hari lagi',
                    number_format($remaining, 0, ',', '.')
                );

                foreach ($adminIds as $userId) {
                    $exists = AppNotification::where('user_id', $userId)
                        ->where('type', 'invoice_due')
                        ->where('message', 'like', '%' . $inv->invoice_number . '%')
                        ->where('created_at', '>=', now()->subDays(7))
                        ->exists();

                    if (!$exists) {
                        AppNotification::create([
                            'user_id' => $userId,
                            'type' => 'invoice_due',
                            'title' => $title,
                            'message' => $message,
                            'link' => $query instanceof \Illuminate\Database\Eloquent\Builder && $query->getModel() instanceof SalesInvoice ? '/apps/sales-invoices' : '/apps/purchase-invoices',
                        ]);
                        $created++;
                    }
                }
            }
        }

        $this->info($created > 0 ? "{$created} notifikasi pengingat dibuat." : 'Tidak ada invoice yang mendekati jatuh tempo.');

        return self::SUCCESS;
    }
}