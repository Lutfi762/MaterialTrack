<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name', 'code', 'contact_person', 'email', 'phone',
        'address', 'lead_time_days', 'rating', 'notes', 'is_active',
    ];

    protected $casts = [
        'lead_time_days' => 'integer',
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
