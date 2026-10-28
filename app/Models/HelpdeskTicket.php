<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HelpdeskTicket extends Model {
    protected $guarded = [];
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
    public function department() { return $this->belongsTo(Department::class); }
    public function replies() { return $this->hasMany(HelpdeskTicketReply::class); }
}