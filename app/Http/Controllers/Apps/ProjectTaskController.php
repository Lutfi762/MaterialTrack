<?php
namespace App\Http\Controllers\Apps;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectTask;
use Illuminate\Http\Request;

class ProjectTaskController extends Controller {
    public function store(Request $request, Project $project) {
        $v = $request->validate([
            'name' => 'required|string',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date'
        ]);
        $project->tasks()->create($v);
        return back()->with('success', 'Task added');
    }
    public function update(Request $request, ProjectTask $task) {
        $v = $request->validate(['status' => 'required|in:todo,in_progress,done']);
        $task->update($v);
        return back();
    }
    public function destroy(ProjectTask $task) {
        $task->delete();
        return back()->with('success', 'Task removed');
    }
}