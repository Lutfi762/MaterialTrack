<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Project extends Model {
    protected $guarded = [];
    protected $casts = ['start_date' => 'date', 'end_date' => 'date', 'budget' => 'decimal:2'];
    
    public function tasks() { return $this->hasMany(ProjectTask::class); }
}