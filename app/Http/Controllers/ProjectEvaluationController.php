<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectEvaluationRequest;
use App\Models\Project;
use App\Models\ProjectEvaluation;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProjectEvaluationController extends Controller
{
    public function create(Project $project): View|RedirectResponse
    {
        if (in_array($project->status, ['approved', 'rejected', 'closed'])) {
            return redirect()->route('projects.show', $project)
                ->with('error', 'No se puede registrar ni editar la evaluación de un proyecto que ya fue aprobado, rechazado o cerrado.');
        }

        $existingEvaluation = ProjectEvaluation::where('project_id', $project->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existingEvaluation) {
            return redirect()->route('projects.show', $project)
                ->with('info', 'Ya has registrado una evaluación para este proyecto.');
        }

        return view('evaluations.create', compact('project'));
    }

    public function store(StoreProjectEvaluationRequest $request, Project $project): RedirectResponse
    {
        if (in_array($project->status, ['approved', 'rejected', 'closed'])) {
            return redirect()->route('projects.show', $project)
                ->with('error', 'No se puede registrar ni editar la evaluación de un proyecto que ya fue aprobado, rechazado o cerrado.');
        }

        $existingEvaluation = ProjectEvaluation::where('project_id', $project->id)
            ->where('user_id', auth()->id())
            ->first();

        if ($existingEvaluation) {
            return redirect()->route('projects.show', $project)
                ->with('info', 'Ya has registrado una evaluación para este proyecto.');
        }

        ProjectEvaluation::create([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'technical_score' => $request->technical_score,
            'financial_score' => $request->financial_score,
            'operational_score' => $request->operational_score,
            'regulatory_score' => $request->regulatory_score,
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Evaluación registrada exitosamente.');
    }

    public function edit(Project $project, ProjectEvaluation $evaluation): View|RedirectResponse
    {
        if (in_array($project->status, ['approved', 'rejected', 'closed'])) {
            return redirect()->route('projects.show', $project)
                ->with('error', 'No se puede registrar ni editar la evaluación de un proyecto que ya fue aprobado, rechazado o cerrado.');
        }

        if ($evaluation->user_id !== auth()->id()) {
            abort(403);
        }

        return view('evaluations.edit', compact('project', 'evaluation'));
    }

    public function update(StoreProjectEvaluationRequest $request, Project $project, ProjectEvaluation $evaluation): RedirectResponse
    {
        if (in_array($project->status, ['approved', 'rejected', 'closed'])) {
            return redirect()->route('projects.show', $project)
                ->with('error', 'No se puede registrar ni editar la evaluación de un proyecto que ya fue aprobado, rechazado o cerrado.');
        }

        if ($evaluation->user_id !== auth()->id()) {
            abort(403);
        }

        $evaluation->update([
            'technical_score' => $request->technical_score,
            'financial_score' => $request->financial_score,
            'operational_score' => $request->operational_score,
            'regulatory_score' => $request->regulatory_score,
        ]);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Evaluación actualizada exitosamente.');
    }
}
