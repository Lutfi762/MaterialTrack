<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulkStockOperationItem extends Model
{
    protected $fillable = ['bulk_operation_id', 'item_id', 'qty', 'source'];

    public function operation(): BelongsTo { return $this->belongsTo(BulkStockOperation::class, 'bulk_operation_id'); }
    public function item(): BelongsTo { return $this->belongsTo(Item::class); }
}
