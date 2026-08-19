<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\StockMovement;
use App\Models\StockReturn;

class StockReturnObserver
{
    public function updated(StockReturn $stockReturn): void
    {
        // Movement 'in' dibuat saat retur disetujui.
        // Stok item sudah ditambah controller sebelum update status.
        if ($stockReturn->status === 'approved' && $stockReturn->wasChanged('status')) {
            $this->recordMovement($stockReturn);
        }
    }

    private function recordMovement(StockReturn $stockReturn): void
    {
        /** @var Item $item */
        $item = $stockReturn->item;

        StockMovement::create([
            'item_id' => $stockReturn->item_id,
            'user_id' => $stockReturn->approved_by ?? $stockReturn->user_id,
            'type' => 'in',
            'qty' => $stockReturn->qty,
            'stock_before' => $item->current_stock - $stockReturn->qty,
            'balance_after' => $item->current_stock,
            'ref_type' => StockReturn::class,
            'ref_id' => $stockReturn->id,
            'notes' => 'Retur material disetujui: '.($stockReturn->reason ?? '-'),
        ]);
    }
}
