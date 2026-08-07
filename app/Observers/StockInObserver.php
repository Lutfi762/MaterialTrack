<?php

namespace App\Observers;

use App\Models\StockIn;
use App\Models\StockMovement;

class StockInObserver
{
    public function created(StockIn $stockIn): void
    {
        /** @var \App\Models\Item $item */
        $item = $stockIn->item;
        $stockBefore = $item->stock - $stockIn->qty;

        StockMovement::create([
            'item_id'        => $stockIn->item_id,
            'type'           => 'in',
            'qty'            => $stockIn->qty,
            'stock_before'   => $stockBefore,
            'stock_after'    => $item->stock,
            'reference_type' => 'StockIn',
            'reference_id'   => $stockIn->id,
            'notes'          => $stockIn->notes ?? null,
            'created_by'     => auth()->id(),
        ]);
    }
}