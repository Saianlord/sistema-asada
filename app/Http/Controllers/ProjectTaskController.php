<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectTaskRequest;
use App\Http\Requests\UpdateProjectTaskRequest;
use App\Models\Project;
use App\Models\ProjectTask;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectTaskController extends Controller
{
    public function index(Project $project): View|RedirectResponse
    {
        if (!in_array($project->status, ['in_progress', 'paused', 'closed'])) {
            return redirect()->route('projects.show', $project)->with('error', 'Solo se puede acceder al tablero Kanban de proyectos en ejecución, pausados o finalizados.');
        }

        $tasks = $project->tasks()->with('assignedUser')->get();

        $pendingTasks = $tasks->where('status', 'pending');
        $inProgressTasks = $tasks->where('status', 'in_progress');
        $completedTasks = $tasks->where('status', 'completed');

        $users = User::where('is_active', true)->get();

        return view('projects.kanban', compact('project', 'pendingTasks', 'inProgressTasks', 'completedTasks', 'users'));
    }

    public function store(StoreProjectTaskRequest $request, Project $project): RedirectResponse
    {
        if ($project->status !== 'in_progress') {
            return redirect()->route('projects.show', $project)->with('error', 'Solo se puede acceder al tablero Kanban de proyectos en ejecución.');
        }

        if (!auth()->user()->hasAnyRole(['admin', 'administration']) && auth()->id() !== $project->user_id) {
            abort(403);
        }

        $project->tasks()->create([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'assigned_user_id' => $request->assigned_user_id,
            'status' => 'pending',
        ]);

        return redirect()->route('projects.kanban.index', $project)->with('success', 'Tarea creada exitosamente.');
    }

    public function edit(Project $project, ProjectTask $task): View|RedirectResponse
    {
        if ($task->project_id !== $project->id) {
            abort(404);
        }

        if ($project->status !== 'in_progress') {
            return redirect()->route('projects.show', $project)->with('error', 'Solo se puede acceder al tablero Kanban de proyectos en ejecución.');
        }

        if (!auth()->user()->hasAnyRole(['admin', 'administration']) && auth()->id() !== $project->user_id) {
            abort(403);
        }

        $users = User::where('is_active', true)->get();

        return view('projects.tasks_edit', compact('project', 'task', 'users'));
    }

    public function update(UpdateProjectTaskRequest $request, Project $project, ProjectTask $task): RedirectResponse
    {
        if ($task->project_id !== $project->id) {
            abort(404);
        }

        if ($project->status !== 'in_progress') {
            return redirect()->route('projects.show', $project)->with('error', 'Solo se puede acceder al tablero Kanban de proyectos en ejecución.');
        }

        if (!auth()->user()->hasAnyRole(['admin', 'administration']) && auth()->id() !== $project->user_id) {
            abort(403);
        }

        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'assigned_user_id' => $request->assigned_user_id,
            'status' => $request->status,
        ]);

        return redirect()->route('projects.kanban.index', $project)->with('success', 'Tarea actualizada exitosamente.');
    }

    public function updateStatus(Request $request, Project $project, ProjectTask $task): RedirectResponse
    {
        if ($task->project_id !== $project->id) {
            abort(404);
        }

        if ($project->status !== 'in_progress') {
            return redirect()->route('projects.show', $project)->with('error', 'Solo se puede acceder al tablero Kanban de proyectos en ejecución.');
        }

        if (!auth()->user()->hasAnyRole(['admin', 'administration']) && auth()->id() !== $project->user_id) {
            abort(403);
        }

        $request->validate([
            'status' => 'required|string|in:pending,in_progress,completed',
        ]);

        $task->update(['status' => $request->status]);

        return redirect()->route('projects.kanban.index', $project)->with('success', 'Estado de la tarea actualizado exitosamente.');
    }
}
