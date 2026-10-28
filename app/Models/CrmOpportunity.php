<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CrmOpportunity extends Model {
    protected $guarded = [];
    protected $casts = ['expected_close_date' => 'date', 'expected_revenue' => 'decimal:2'];
    public function lead() { return $this->belongsTo(CrmLead::class, 'crm_lead_id'); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function assignee() { return $this->belongsTo(User::class, 'assigned_to'); }
}