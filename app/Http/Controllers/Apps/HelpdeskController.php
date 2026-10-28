<?php
namespace App\Http\Controllers\Apps;
use App\Http\Controllers\Controller;
use App\Models\{HelpdeskTicket, HelpdeskTicketReply, Department, User};
use App\Support\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class HelpdeskController extends Controller {
    public function index(Request $request) {
        $query = HelpdeskTicket::with(['creator', 'assignee', 'department']);
        if ($request->search) $query->where('subject', 'like', "%{$request->search}%")->orWhere('ticket_number', 'like', "%{$request->search}%");
        return Inertia::render('Apps/Helpdesk/Index', [
            'tickets' => $query->paginate(15)->withQueryString(),
            'departments' => Department::all(['id','name']),
            'users' => User::all(['id','name'])
        ]);
    }
    public function store(Request $request) {
        $v = $request->validate(['subject'=>'required', 'description'=>'required', 'priority'=>'required|in:low,medium,high,urgent', 'department_id'=>'nullable|exists:departments,id', 'assigned_to'=>'nullable|exists:users,id']);
        $v['created_by'] = auth()->id();
        DB::transaction(function () use ($v) {
            $v['ticket_number'] = DocumentNumber::next('helpdesk_tickets', 'TKT');
            HelpdeskTicket::create($v);
        });
        return back()->with('success', 'Ticket created');
    }
    public function show(HelpdeskTicket $ticket) {
        $ticket->load(['creator', 'assignee', 'department', 'replies.user']);
        return Inertia::render('Apps/Helpdesk/Show', ['ticket' => $ticket, 'users' => User::all(['id','name'])]);
    }
    public function reply(Request $request, HelpdeskTicket $ticket) {
        $v = $request->validate(['message' => 'required']);
        $ticket->replies()->create(['message' => $v['message'], 'user_id' => auth()->id()]);
        return back();
    }
    public function update(Request $request, HelpdeskTicket $ticket) {
        $ticket->update($request->validate(['status'=>'required|in:open,in_progress,resolved,closed', 'assigned_to'=>'nullable|exists:users,id']));
        return back();
    }
}