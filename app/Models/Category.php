<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code_prefix', 'description'];

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'category_department');
    }
}