<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectTrackingRequest;
use App\Http\Requests\UpdateProjectTrackingRequest;
use App\Models\Project;
use App\Models\ProjectTracking;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectTrackingController extends Controller
{
    public function create(Project $project): View|RedirectResponse
    {
        if ($project->status !== 'in_progress') {
            return redirect()->route('projects.show', $project)->with('error', 'Solo se puede registrar o actualizar el seguimiento de proyectos en ejecución.');
        }

        if (!auth()->user()->hasAnyRole(['admin', 'administration']) && auth()->id() !== $project->user_id) {
            abort(403);
        }

        return view('projects.tracking_create', compact('project'));
    }

    public function store(StoreProjectTrackingRequest $request, Project $project): RedirectResponse
    {
        if ($project->status !== 'in_progress') {
            return redirect()->route('projects.show', $project)->with('error', 'Solo se puede registrar o actualizar el seguimiento de proyectos en ejecución.');
        }

        if (!auth()->user()->hasAnyRole(['admin', 'administration']) && auth()->id() !== $project->user_id) {
            abort(403);
        }

        $project->trackings()->create([
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Registro de seguimiento creado exitosamente.');
    }

    public function edit(Project $project, ProjectTracking $tracking): View|RedirectResponse
    {
        if ($tracking->project_id !== $project->id) {
            abort(404);
        }

        if ($project->status !== 'in_progress') {
            return redirect()->route('projects.show', $project)->with('error', 'Solo se puede registrar o actualizar el seguimiento de proyectos en ejecución.');
        }

        if (!auth()->user()->hasAnyRole(['admin', 'administration']) && auth()->id() !== $project->user_id) {
            abort(403);
        }

        return view('projects.tracking_edit', compact('project', 'tracking'));
    }

    public function update(UpdateProjectTrackingRequest $request, Project $project, ProjectTracking $tracking): RedirectResponse
    {
        if ($tracking->project_id !== $project->id) {
            abort(404);
        }

        if ($project->status !== 'in_progress') {
            return redirect()->route('projects.show', $project)->with('error', 'Solo se puede registrar o actualizar el seguimiento de proyectos en ejecución.');
        }

        if (!auth()->user()->hasAnyRole(['admin', 'administration']) && auth()->id() !== $project->user_id) {
            abort(403);
        }

        $tracking->update([
            'type' => $request->type,
            'title' => $request->title,
            'description' => $request->description,
            'date' => $request->date,
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Registro de seguimiento actualizado exitosamente.');
    }
}
