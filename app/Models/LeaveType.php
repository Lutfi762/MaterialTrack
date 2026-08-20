<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = ['name', 'default_days', 'is_paid'];

    protected $casts = [
        'default_days' => 'integer',
        'is_paid' => 'boolean',
    ];
}
