<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectHistory;
use Illuminate\View\View;

class ProjectHistoryController extends Controller
{
    public function index(Project $project): View
    {
        $histories = $project->histories()->latest()->get();

        return view('projects.history.index', compact('project', 'histories'));
    }

    public function show(Project $project, ProjectHistory $history): View
    {
        if ($history->project_id !== $project->id) {
            abort(404);
        }

        return view('projects.history.show', compact('project', 'history'));
    }
}
