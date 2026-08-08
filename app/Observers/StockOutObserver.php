<?php

namespace App\Observers;

use App\Models\StockOut;
use App\Models\StockMovement;

class StockOutObserver
{
    public function created(StockOut $stockOut): void
    {
        /** @var \App\Models\Item $item */
        $item = $stockOut->item;
        $stockBefore = $item->current_stock + $stockOut->qty;

        StockMovement::create([
            'item_id'        => $stockOut->item_id,
            'user_id'        => $stockOut->user_id,
            'type'           => 'out',
            'qty'            => $stockOut->qty,
            'stock_before'   => $stockBefore,
            'balance_after'  => $item->current_stock,
            'ref_type'       => StockOut::class,
            'ref_id'         => $stockOut->id,
            'notes'          => $stockOut->notes ?? null,
        ]);
    }
}