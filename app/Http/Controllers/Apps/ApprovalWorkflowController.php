<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\ApprovalWorkflow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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
            'module' => 'required|string|in:purchase_request,purchase_order,sales_order,leave,stock_out,other',
            'min_amount' => 'nullable|numeric|min:0',
            'max_amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'steps' => 'required|array|min:1',
            'steps.*.approver_type' => 'required|in:user,role,manager',
            'steps.*.approver_value' => 'required_if:steps.*.approver_type,user|required_if:steps.*.approver_type,role|nullable|string',
        ]);

        if ($validated['max_amount'] !== null && $validated['max_amount'] < ($validated['min_amount'] ?? 0)) {
            throw ValidationException::withMessages(['max_amount' => 'Nominal maksimal tidak boleh lebih kecil dari minimal.']);
        }

        $exists = ApprovalWorkflow::where('module', $validated['module'])
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages(['name' => 'Workflow dengan nama tersebut sudah terdaftar untuk modul ini.']);
        }

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
