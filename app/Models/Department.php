<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function stockOuts()
    {
        return $this->hasMany(StockOut::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}