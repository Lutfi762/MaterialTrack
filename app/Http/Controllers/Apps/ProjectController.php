<?php
namespace App\Http\Controllers\Apps;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller {
    public function index(Request $request) {
        $query = Project::query();
        if ($request->search) $query->where('name', 'like', "%{$request->search}%");
        if ($request->status) $query->where('status', $request->status);
        
        return Inertia::render('Apps/Projects/Index', [
            'projects' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only('search', 'status')
        ]);
    }
    public function create() { return Inertia::render('Apps/Projects/Create'); }
    public function store(Request $request) {
        $v = $request->validate([
            'name' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'description' => 'nullable|string'
        ]);
        $v['status'] = 'planning';
        DB::transaction(function () use ($v) {
            $v['code'] = DocumentNumber::next('projects', 'PRJ');
            Project::create($v);
        });
        return to_route('apps.projects.index')->with('success', 'Project created');
    }
    public function show(Project $project) {
        $project->load(['tasks.assignee']);
        return Inertia::render('Apps/Projects/Show', [
            'project' => $project,
            'users' => User::all(['id', 'name'])
        ]);
    }
    public function update(Request $request, Project $project) {
        $v = $request->validate(['status' => 'required|in:planning,in_progress,on_hold,completed']);
        $project->update($v);
        return back()->with('success', 'Project status updated');
    }
    public function destroy(Project $project) {
        $project->delete();
        return to_route('apps.projects.index')->with('success', 'Project deleted');
    }
}