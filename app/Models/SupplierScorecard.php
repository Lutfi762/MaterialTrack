<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierScorecard extends Model
{
    protected $fillable = [
        "supplier_id", "period", "total_po", "total_po_value",
        "on_time_deliveries", "late_deliveries", "total_qty_received",
        "total_qty_rejected", "delivery_score", "quality_score", "overall_score"
    ];

    protected $casts = [
        "total_po" => "integer",
        "total_po_value" => "decimal:2",
        "on_time_deliveries" => "integer",
        "late_deliveries" => "integer",
        "total_qty_received" => "integer",
        "total_qty_rejected" => "integer",
        "delivery_score" => "decimal:2",
        "quality_score" => "decimal:2",
        "overall_score" => "decimal:2",
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
