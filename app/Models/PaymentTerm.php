<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTerm extends Model
{
    protected $fillable = ['name', 'code', 'days', 'description', 'is_active'];

    protected $casts = [
        'days' => 'integer',
        'is_active' => 'boolean',
    ];
}
