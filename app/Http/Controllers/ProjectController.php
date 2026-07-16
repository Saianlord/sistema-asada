<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
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
        if (in_array($project->status, ['approved', 'closed'])) {
            return redirect()->route('projects.show', $project)->with('error', 'No se puede editar un proyecto que ya ha sido aprobado o cerrado.');
        }

        return view('projects.edit', compact('project'));
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        if (in_array($project->status, ['approved', 'closed'])) {
            return redirect()->route('projects.show', $project)->with('error', 'No se puede editar un proyecto que ya ha sido aprobado o cerrado.');
        }

        $project->update([
            'title' => $request->title,
            'description' => $request->description,
            'criticality' => $request->criticality,
            'priority' => $request->priority,
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Iniciativa de proyecto actualizada exitosamente.');
    }

    public function reports(Request $request): View
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

        $summary = [
            'total' => $projects->count(),
            'pending' => $projects->where('status', 'pending')->count(),
            'approved' => $projects->where('status', 'approved')->count(),
            'closed' => $projects->where('status', 'closed')->count(),
            'low' => $projects->where('criticality', 'low')->count(),
            'medium' => $projects->where('criticality', 'medium')->count(),
            'high' => $projects->where('criticality', 'high')->count(),
            'low_priority' => $projects->where('priority', 'low')->count(),
            'medium_priority' => $projects->where('priority', 'medium')->count(),
            'high_priority' => $projects->where('priority', 'high')->count(),
        ];

        return view('projects.reports', compact('projects', 'summary'));
    }

    public function budgetReport(): View
    {
        $projects = Project::with('user')
            ->whereNotNull('estimated_cost')
            ->orderByDesc('estimated_cost')
            ->get();

        $totalBudget = $projects->sum('estimated_cost');
        $averageProgress = $projects->isEmpty()
            ? 0
            : round($projects->map(fn ($project) => match ($project->priority) {
                'high' => 75,
                'medium' => 50,
                'low' => 25,
                default => 0,
            })->avg(), 2);

        return view('projects.budget-report', compact('projects', 'totalBudget', 'averageProgress'));
    }

    public function prioritization(): View
    {
        $projects = Project::with('evaluations')
            ->get()
            ->filter(fn ($project) => $project->evaluations->isNotEmpty())
            ->sortByDesc(fn ($project) => $project->average_viability_score);

        return view('projects.prioritization', compact('projects'));
    }
}
