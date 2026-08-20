<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\ApprovalWorkflow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ApprovalWorkflowController extends Controller
{
    public function index()
    {
        $workflows = ApprovalWorkflow::with('steps')->latest()->get();
        $pendingRequests = ApprovalRequest::with(['workflow', 'requester', 'steps.approver'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return Inertia::render('Apps/ApprovalWorkflows/Index', [
            'workflows' => $workflows,
            'pendingRequests' => $pendingRequests,
        ]);
    }

    public function create()
    {
        return Inertia::render('Apps/ApprovalWorkflows/Create', [
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'module' => 'required|string',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'steps' => 'required|array|min:1',
            'steps.*.approver_type' => 'required|in:user,role,manager',
            'steps.*.approver_value' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            $workflow = ApprovalWorkflow::create([
                'name' => $validated['name'],
                'module' => $validated['module'],
                'min_amount' => $validated['min_amount'] ?? 0,
                'max_amount' => $validated['max_amount'] ?? null,
                'is_active' => true,
                'description' => $validated['description'] ?? null,
            ]);

            foreach ($validated['steps'] as $idx => $step) {
                $workflow->steps()->create([
                    'step_order' => $idx + 1,
                    'approver_type' => $step['approver_type'],
                    'approver_value' => $step['approver_value'] ?? null,
                ]);
            }
        });

        return to_route('apps.approval-workflows.index')->with('success', 'Alur persetujuan (Workflow) berhasil didaftarkan.');
    }
}
