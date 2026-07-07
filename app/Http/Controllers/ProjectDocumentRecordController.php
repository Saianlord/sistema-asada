<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectDocumentRecordController extends Controller
{
    public function index(Project $project): View
    {
        $project->load([
            'documents' => function ($query) {
                $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
            },
            'documents.uploadedBy'
        ]);

        return view('projects.document_record', compact('project'));
    }

    public function download(Project $project, ProjectDocument $document): StreamedResponse
    {
        if ($document->project_id !== $project->id) {
            abort(404);
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download($document->file_path, $document->original_name);
    }
}
