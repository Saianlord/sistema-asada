<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Models\Project;
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
    public function index(): View
    {
        $projects = Project::with('user')->get();

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
        return view('projects.show', compact('project'));
    }
}
