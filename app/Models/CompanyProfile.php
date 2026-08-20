<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $fillable = [
        'name', 'legal_name', 'tax_id', 'email', 'phone',
        'address', 'city', 'province', 'postal_code', 'country',
        'logo', 'website', 'currency', 'date_format',
    ];
}
