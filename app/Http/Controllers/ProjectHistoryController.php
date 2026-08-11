<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectHistoryController extends Controller
{
    public function index(Project $project): View
    {
        if (!auth()->user()->hasAnyRole(['admin', 'administration', 'fiscal']) && auth()->id() !== $project->user_id) {
            abort(403, 'No tiene permisos para ver el historial de este proyecto.');
        }

        $auditLogs = $project->auditLogs()->with('user')->get();

        return view('projects.history', compact('project', 'auditLogs'));
    }
}
