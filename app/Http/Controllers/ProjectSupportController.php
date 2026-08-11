<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProjectSupportRequest;
use App\Models\Project;
use App\Models\ProjectHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProjectSupportController extends Controller
{
    public function edit(Project $project): View
    {
        return view('projects.support', compact('project'));
    }

    public function update(UpdateProjectSupportRequest $request, Project $project): RedirectResponse
    {
        $data = [
            'technical_justification' => $request->technical_justification,
            'estimated_cost' => $request->estimated_cost,
            'impact' => $request->impact,
            'risk' => $request->risk,
        ];

        if ($request->hasFile('evidence')) {
            if ($project->evidence_path) {
                Storage::disk('public')->delete($project->evidence_path);
            }
            $path = $request->file('evidence')->store('projects/evidences', 'public');
            $data['evidence_path'] = $path;
        }

        $project->update($data);

        ProjectHistory::create([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'action_type' => 'support_updated',
            'title' => 'Información de respaldo actualizada',
            'description' => 'Se actualizó la información de respaldo del proyecto.',
            'details' => "Justificación técnica: {$request->technical_justification}\nCosto estimado: {$request->estimated_cost}\nImpacto: {$request->impact}\nRiesgo: {$request->risk}",
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Información de respaldo guardada exitosamente.');
    }

    public function downloadEvidence(Project $project)
    {
        if (!$project->evidence_path || !Storage::disk('public')->exists($project->evidence_path)) {
            abort(404);
        }

        return Storage::disk('public')->response($project->evidence_path);
    }
}
