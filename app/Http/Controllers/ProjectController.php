<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\StoreProjectApprovalRequest;
use App\Models\Project;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $query = Project::with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('criticality')) {
            $query->where('criticality', $request->criticality);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $projects = $query->get();

        return view('projects.index', compact('projects'));
    }

    public function create(): View
    {
        return view('projects.create');
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        Project::create([
            'title' => $request->title,
            'description' => $request->description,
            'criticality' => $request->criticality,
            'priority' => $request->priority,
            'user_id' => auth()->id(),
            'status' => 'pending',
        ]);

        return redirect()->route('projects.index')->with('success', 'Iniciativa de proyecto registrada exitosamente.');
    }

    public function show(Project $project): View
    {
        $project->load(['evaluations.user']);

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        if (in_array($project->status, ['approved', 'rejected', 'closed'])) {
            return redirect()->route('projects.show', $project)->with('error', 'No se puede editar un proyecto que ya ha sido aprobado, rechazado o cerrado.');
        }

        return view('projects.edit', compact('project'));
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        if (in_array($project->status, ['approved', 'rejected', 'closed'])) {
            return redirect()->route('projects.show', $project)->with('error', 'No se puede editar un proyecto que ya ha sido aprobado, rechazado o cerrado.');
        }

        $project->update([
            'title' => $request->title,
            'description' => $request->description,
            'criticality' => $request->criticality,
            'priority' => $request->priority,
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Iniciativa de proyecto actualizada exitosamente.');
    }

    public function prioritization(): View
    {
        $projects = Project::with('evaluations')
            ->get()
            ->filter(fn ($project) => $project->evaluations->isNotEmpty())
            ->sortByDesc(fn ($project) => $project->average_viability_score);

        return view('projects.prioritization', compact('projects'));
    }

    public function approve(Project $project): RedirectResponse
    {
        if ($project->status !== 'pending') {
            return redirect()->route('projects.show', $project)->with('error', 'Solo los proyectos en estado pendiente pueden ser aprobados o rechazados.');
        }

        if (is_null($project->estimated_cost) || $project->estimated_cost <= 0) {
            return redirect()->route('projects.show', $project)->with('error', 'No se puede aprobar el proyecto porque no tiene presupuesto disponible.');
        }

        $project->update(['status' => 'approved']);

        return redirect()->route('projects.show', $project)->with('success', 'Proyecto aprobado exitosamente.');
    }

    public function reject(Project $project): RedirectResponse
    {
        if ($project->status !== 'pending') {
            return redirect()->route('projects.show', $project)->with('error', 'Solo los proyectos en estado pendiente pueden ser aprobados o rechazados.');
        }

        $project->update(['status' => 'rejected']);

        return redirect()->route('projects.show', $project)->with('success', 'Proyecto rechazado exitosamente.');
    }

    public function approvalForm(Project $project): View|RedirectResponse
    {
        if ($project->status !== 'approved') {
            return redirect()->route('projects.show', $project)->with('error', 'Solo se puede registrar el acuerdo en proyectos aprobados.');
        }

        return view('projects.approval', compact('project'));
    }

    public function storeApproval(StoreProjectApprovalRequest $request, Project $project): RedirectResponse
    {
        if ($project->status !== 'approved') {
            return redirect()->route('projects.show', $project)->with('error', 'Solo se puede registrar el acuerdo en proyectos aprobados.');
        }

        $project->update([
            'approval_agreement' => $request->approval_agreement,
            'approval_date' => $request->approval_date,
            'approval_responsible' => $request->approval_responsible,
            'approval_justification' => $request->approval_justification,
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Registro de aprobación guardado correctamente.');
    }

    public function updateStatus(Request $request, Project $project): RedirectResponse
    {
        if (!auth()->user()->hasAnyRole(['admin', 'administration']) && auth()->id() !== $project->user_id) {
            abort(403);
        }

        if ($project->status === 'closed') {
            return redirect()->route('projects.show', $project)->with('error', 'No se puede cambiar el estado de un proyecto finalizado.');
        }

        $request->validate([
            'status' => 'required|string|in:pending,en_analisis,prioritized,approved,in_progress,paused,closed',
        ]);

        if ($request->status === 'approved') {
            if (is_null($project->estimated_cost) || $project->estimated_cost <= 0) {
                return redirect()->route('projects.show', $project)->with('error', 'No se puede aprobar el proyecto porque no tiene presupuesto disponible.');
            }
        }

        $project->update(['status' => $request->status]);

        return redirect()->route('projects.show', $project)->with('success', 'Estado del proyecto actualizado exitosamente.');
    }
}
