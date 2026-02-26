<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        $projects = Project::with('users')->orderBy('name')->paginate(20);

        return Inertia::render('Admin/Projects/Index', compact('projects'));
    }

    public function show(Project $project): Response
    {
        $project->load('users', 'workLogs');

        return Inertia::render('Admin/Projects/Show', compact('project'));
    }
}
