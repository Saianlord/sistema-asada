<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectDocumentRequest;
use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ProjectDocumentController extends Controller
{
    public function store(Project $project, StoreProjectDocumentRequest $request): RedirectResponse
    {
        if ($project->status !== 'in_progress') {
            return redirect()->route('projects.show', $project)->with('error', 'Solo se pueden gestionar documentos en proyectos en ejecución.');
        }

        if (!auth()->user()->hasAnyRole(['admin', 'administration']) && auth()->id() !== $project->user_id) {
            abort(403);
        }

        $file = $request->file('document');
        $path = $file->store("project-documents/{$project->id}", 'public');

        $project->documents()->create([
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()->route('projects.show', $project)->with('success', 'Documento adjuntado correctamente.');
    }

    public function destroy(Project $project, ProjectDocument $document): RedirectResponse
    {
        if ($document->project_id !== $project->id) {
            abort(404);
        }

        if ($project->status !== 'in_progress') {
            return redirect()->route('projects.show', $project)->with('error', 'Solo se pueden gestionar documentos en proyectos en ejecución.');
        }

        if (!auth()->user()->hasAnyRole(['admin', 'administration']) && auth()->id() !== $project->user_id) {
            abort(403);
        }

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('projects.show', $project)->with('success', 'Documento eliminado correctamente.');
    }
}
