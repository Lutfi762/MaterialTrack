<?php
namespace App\Http\Controllers\Apps;
use App\Http\Controllers\Controller;
use App\Models\CrmLead;
use App\Models\CrmOpportunity;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CrmController extends Controller {
    public function leads(Request $request) {
        $query = CrmLead::with('assignee');
        if ($request->search) $query->where('name', 'like', "%{$request->search}%")->orWhere('company', 'like', "%{$request->search}%");
        return Inertia::render('Apps/CRM/Leads', [
            'leads' => $query->paginate(15)->withQueryString(),
            'users' => User::all(['id', 'name'])
        ]);
    }
    public function storeLead(Request $request) {
        $v = $request->validate(['name'=>'required', 'company'=>'nullable', 'email'=>'nullable|email', 'phone'=>'nullable', 'status'=>'required|in:new,contacted,qualified,lost,converted', 'assigned_to'=>'nullable|exists:users,id', 'notes'=>'nullable']);
        CrmLead::create($v);
        return back()->with('success', 'Lead created');
    }
    public function updateLead(Request $request, CrmLead $lead) {
        $lead->update($request->validate(['status'=>'required|in:new,contacted,qualified,lost,converted']));
        return back();
    }
    
    public function opportunities(Request $request) {
        return Inertia::render('Apps/CRM/Opportunities', [
            'opportunities' => CrmOpportunity::with(['lead', 'customer', 'assignee'])->get(),
            'users' => User::all(['id', 'name']),
            'leads' => CrmLead::where('status', '!=', 'lost')->get(),
            'customers' => Customer::all()
        ]);
    }
    public function storeOpportunity(Request $request) {
        $v = $request->validate(['title'=>'required', 'crm_lead_id'=>'nullable|exists:crm_leads,id', 'customer_id'=>'nullable|exists:customers,id', 'expected_revenue'=>'required|numeric|min:0', 'stage'=>'required|in:prospecting,proposal,negotiation,closed_won,closed_lost', 'expected_close_date'=>'nullable|date', 'assigned_to'=>'nullable|exists:users,id']);
        CrmOpportunity::create($v);
        return back()->with('success', 'Opportunity created');
    }
    public function updateOpportunityStage(Request $request, CrmOpportunity $opportunity) {
        $opportunity->update($request->validate(['stage'=>'required|in:prospecting,proposal,negotiation,closed_won,closed_lost']));
        return back();
    }
}