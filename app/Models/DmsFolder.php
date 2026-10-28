<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DmsFolder extends Model {
    protected $guarded = [];
    public function department() { return $this->belongsTo(Department::class); }
    public function subfolders() { return $this->hasMany(DmsFolder::class, 'parent_id'); }
    public function documents() { return $this->hasMany(DmsDocument::class); }
}