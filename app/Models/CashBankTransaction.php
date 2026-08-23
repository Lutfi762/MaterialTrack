<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashBankTransaction extends Model
{
    protected $table = 'cash_bank_transactions';

    protected $fillable = [
        'transaction_number', 'transaction_date', 'type',
        'account_id', 'counter_account_id', 'amount',
        'reference_no', 'description', 'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function counterAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'counter_account_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}