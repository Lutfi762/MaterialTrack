<?php

namespace App\Observers;

use App\Models\Item;
use App\Models\StockIn;
use App\Models\StockMovement;

class StockInObserver
{
    public function created(StockIn $stockIn): void
    {
        /** @var Item $item */
        $item = $stockIn->item;
        $stockBefore = $item->current_stock - $stockIn->qty;

        StockMovement::create([
            'item_id' => $stockIn->item_id,
            'user_id' => $stockIn->user_id,
            'type' => 'in',
            'qty' => $stockIn->qty,
            'stock_before' => $stockBefore,
            'balance_after' => $item->current_stock,
            'ref_type' => StockIn::class,
            'ref_id' => $stockIn->id,
            'notes' => $stockIn->notes ?? null,
        ]);
    }
}
