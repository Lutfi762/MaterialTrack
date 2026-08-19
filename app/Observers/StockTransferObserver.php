<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\StockMovement;
use App\Models\StockTransfer;

class StockTransferObserver
{
    public function updated(StockTransfer $transfer): void
    {
        // Transfer mencatat 2 movement saat disetujui:
        // (1) pengurangan stok sumber, (2) penambahan stok tujuan (informasional).
        // Stok item sudah dikurangi controller sebelum update status.
        if ($transfer->status === 'approved' && $transfer->wasChanged('status')) {
            $this->recordMovements($transfer);
        }
    }

    private function recordMovements(StockTransfer $transfer): void
    {
        /** @var Item $item */
        $item = $transfer->item;

        StockMovement::create([
            'item_id' => $transfer->item_id,
            'user_id' => $transfer->approved_by ?? $transfer->user_id,
            'type' => 'out',
            'qty' => $transfer->qty,
            'stock_before' => $item->current_stock + $transfer->qty,
            'balance_after' => $item->current_stock,
            'ref_type' => StockTransfer::class,
            'ref_id' => $transfer->id,
            'notes' => 'Transfer keluar ke '.($transfer->toDepartment->name ?? '-'),
        ]);

        StockMovement::create([
            'item_id' => $transfer->item_id,
            'user_id' => $transfer->approved_by ?? $transfer->user_id,
            'type' => 'adjustment',
            'qty' => $transfer->qty,
            'stock_before' => $item->current_stock,
            'balance_after' => $item->current_stock,
            'ref_type' => StockTransfer::class,
            'ref_id' => $transfer->id,
            'notes' => 'Transfer masuk dari '.($transfer->fromDepartment->name ?? '-'),
        ]);
    }
}
