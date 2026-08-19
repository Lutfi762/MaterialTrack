<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\StockAdjustment;
use App\Models\StockMovement;

class StockAdjustmentObserver
{
    public function updated(StockAdjustment $adjustment): void
    {
        // Movement 'adjustment' dibuat saat penyesuaian disetujui.
        // Stok item sudah disesuaikan controller sebelum update status.
        if ($adjustment->status === 'approved' && $adjustment->wasChanged('status')) {
            $this->recordMovement($adjustment);
        }
    }

    private function recordMovement(StockAdjustment $adjustment): void
    {
        /** @var Item $item */
        $item = $adjustment->item;

        StockMovement::create([
            'item_id' => $adjustment->item_id,
            'user_id' => $adjustment->approved_by ?? $adjustment->user_id,
            'type' => 'adjustment',
            'qty' => abs($adjustment->qty),
            'stock_before' => $item->current_stock - $adjustment->qty,
            'balance_after' => $item->current_stock,
            'ref_type' => StockAdjustment::class,
            'ref_id' => $adjustment->id,
            'notes' => 'Penyesuaian stok: '.$adjustment->reason
                .($adjustment->qty > 0 ? ' (surplus)' : ' (defisit)'),
        ]);
    }
}
