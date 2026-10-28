<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HelpdeskTicketReply extends Model {
    protected $guarded = [];
    public function ticket() { return $this->belongsTo(HelpdeskTicket::class, 'helpdesk_ticket_id'); }
    public function user() { return $this->belongsTo(User::class); }
}