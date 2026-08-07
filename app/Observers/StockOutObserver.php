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
        $stockBefore = $item->stock + $stockOut->qty;

        StockMovement::create([
            'item_id'        => $stockOut->item_id,
            'type'           => 'out',
            'qty'            => $stockOut->qty,
            'stock_before'   => $stockBefore,
            'stock_after'    => $item->stock,
            'reference_type' => 'StockOut',
            'reference_id'   => $stockOut->id,
            'notes'          => $stockOut->notes ?? null,
            'created_by'     => auth()->id(),
        ]);
    }
}