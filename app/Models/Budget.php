<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Budget extends Model
{
    protected $fillable = [
        "fiscal_year_id", "department_id", "chart_of_account_id",
        "budget_amount", "realized_amount", "notes"
    ];

    protected $casts = [
        "budget_amount" => "decimal:2",
        "realized_amount" => "decimal:2",
    ];

    protected $appends = ["remaining", "usage_percent"];

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, "chart_of_account_id");
    }

    public function getRemainingAttribute()
    {
        return $this->budget_amount - $this->realized_amount;
    }

    public function getUsagePercentAttribute()
    {
        return $this->budget_amount > 0 ? round(($this->realized_amount / $this->budget_amount) * 100, 2) : 0;
    }
}
