<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockAlertConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'alert_type',
        'email_enabled',
        'browser_enabled',
        'threshold_days',
    ];

    protected $casts = [
        'email_enabled' => 'boolean',
        'browser_enabled' => 'boolean',
        'threshold_days' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
