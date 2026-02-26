<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Project::class);

        $projects = $request->user()->is_admin
            ? Project::withCount('users')->orderBy('name')->paginate(15)
            : Project::forUser($request->user()->id)->withCount('users')->orderBy('name')->paginate(15);

        return Inertia::render('Projects/Index', compact('projects'));
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Project::class);

        $users = $request->user()->is_admin
            ? \App\Models\User::where('is_admin', false)->orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email'])
            : [];

        return Inertia::render('Projects/Create', compact('users'));
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $userIds = $data['user_ids'] ?? [];
        unset($data['user_ids']);

        $project = Project::create($data);
        if (! $request->user()->is_admin) {
            $userIds = array_unique(array_merge($userIds, [$request->user()->id]));
        }
        $project->users()->sync($userIds);

        $prefix = $request->user()->is_admin ? 'Admin: ' : '';
        ActivityLog::log(
            'project.created',
            $prefix . 'Vytvorený projekt: ' . $project->name,
            'App\Models\Project',
            $project->id
        );

        $redirectTo = $request->user()->is_admin
            ? route('admin.projects.index')
            : route('projects.index');

        return redirect($redirectTo)->with('success', 'Projekt bol úspešne vytvorený.');
    }

    public function edit(Request $request, Project $project): Response
    {
        $this->authorize('update', $project);

        $project->user_ids = $project->users()->where('users.is_admin', false)->pluck('users.id')->toArray();
        $users = \App\Models\User::where('is_admin', false)->orderBy('first_name')->orderBy('last_name')->select(['id', 'first_name', 'last_name', 'email'])->paginate(10)->withQueryString();
        $work_logs = $project->workLogs()
            ->with('user:id,name')
            ->orderByDesc('work_date')
            ->limit(30)
            ->get()
            ->map(fn ($w) => [
                'id' => $w->id,
                'work_date' => $w->work_date->format('Y-m-d'),
                'minutes' => $w->minutes,
                'note' => $w->note,
                'user_name' => $w->user?->name,
            ]);

        return Inertia::render('Projects/Edit', [
            'project' => $project,
            'users' => $users,
            'work_logs' => $work_logs,
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project): RedirectResponse
    {
        $data = $request->validated();
        $userIds = $request->user()->is_admin ? ($data['user_ids'] ?? []) : null;
        unset($data['user_ids']);

        $project->update($data);

        if ($request->user()->is_admin && $userIds !== null) {
            $project->users()->sync(array_unique($userIds));
        }

        $prefix = $request->user()->is_admin ? 'Admin: ' : '';
        ActivityLog::log(
            'project.updated',
            $prefix . 'Upravený projekt: ' . $project->name,
            'App\Models\Project',
            $project->id
        );

        return redirect()->route('projects.index')
            ->with('success', 'Projekt bol úspešne upravený.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $name = $project->name;
        $project->delete();

        $prefix = auth()->user()->is_admin ? 'Admin: ' : '';
        ActivityLog::log(
            'project.deleted',
            $prefix . 'Zmazaný projekt: ' . $name,
            'App\Models\Project',
            null
        );

        return redirect()->route('projects.index')
            ->with('success', 'Projekt bol úspešne zmazaný.');
    }
}
