<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FixedAsset extends Model
{
    protected $fillable = [
        "code", "name", "description", "category", "acquisition_date", "acquisition_cost",
        "useful_life_months", "salvage_value", "depreciation_method", "accumulated_depreciation",
        "current_book_value", "status", "department_id", "asset_account_id",
        "depreciation_account_id", "accumulated_dep_account_id", "disposed_at", "notes",
    ];

    protected $casts = [
        "acquisition_date" => "date",
        "acquisition_cost" => "decimal:2",
        "salvage_value" => "decimal:2",
        "accumulated_depreciation" => "decimal:2",
        "current_book_value" => "decimal:2",
        "useful_life_months" => "integer",
        "disposed_at" => "datetime",
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function assetAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, "asset_account_id");
    }

    public function depreciationAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, "depreciation_account_id");
    }

    public function accumulatedDepAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, "accumulated_dep_account_id");
    }

    public function monthlyDepreciation(): float
    {
        if ($this->useful_life_months > 0) {
            return (float) ($this->acquisition_cost - $this->salvage_value) / $this->useful_life_months;
        }
        return 0.0;
    }
}
