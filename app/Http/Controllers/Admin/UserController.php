<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::query()->orderBy('first_name')->orderBy('last_name')->paginate(15);

        return Inertia::render('Admin/Users/Index', compact('users'));
    }

    public function create(): Response
    {
        $projects = Project::query()->orderBy('name')->get(['id', 'name', 'client_name']);

        return Inertia::render('Admin/Users/Create', compact('projects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required_unless:is_admin,true', 'nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_admin' => ['boolean'],
            'project_ids' => ['nullable', 'array'],
            'project_ids.*' => ['integer', 'exists:projects,id'],
        ]);
        $projectIds = $validated['project_ids'] ?? [];
        unset($validated['project_ids']);
        $validated['password'] = bcrypt($validated['password']);
        $validated['is_admin'] = $request->boolean('is_admin');

        if ($request->boolean('is_admin')) {
            $validated['last_name'] = $validated['last_name'] ?? null;
        }
        $user = User::create($validated);
        $user->assignedProjects()->sync($request->boolean('is_admin') ? [] : $projectIds);

        ActivityLog::log('user.created', 'Vytvorený používateľ: ' . $user->name, 'App\Models\User', $user->id);

        return redirect()->route('admin.users.index')->with('success', 'Používateľ bol vytvorený.');
    }

    public function edit(Request $request, User $user): Response
    {
        $user->project_ids = $user->assignedProjects()->pluck('id')->toArray();
        $projects = Project::query()->orderBy('name')->select(['id', 'name', 'client_name'])->paginate(10)->withQueryString();

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'projects' => $projects,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required_unless:is_admin,true', 'nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'is_admin' => ['boolean'],
            'project_ids' => ['nullable', 'array'],
            'project_ids.*' => ['integer', 'exists:projects,id'],
        ];
        if ($request->filled('password')) {
            $rules['password'] = ['string', 'min:8', 'confirmed'];
        }
        $validated = $request->validate($rules);
        $projectIds = $validated['project_ids'] ?? [];
        unset($validated['project_ids']);
        $validated['is_admin'] = $request->boolean('is_admin');
        if ($request->boolean('is_admin')) {
            $validated['last_name'] = $validated['last_name'] ?? null;
        }
        if (! empty($validated['password'] ?? null)) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        $user->assignedProjects()->sync($request->boolean('is_admin') ? [] : $projectIds);

        ActivityLog::log('user.updated', 'Upravený používateľ: ' . $user->name, 'App\Models\User', $user->id);

        return redirect()->route('admin.users.index')->with('success', 'Používateľ bol upravený.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'Nemôžete zmazať sám seba.');
        }
        $name = $user->name;
        $user->delete();

        ActivityLog::log('user.deleted', 'Zmazaný používateľ: ' . $name, 'App\Models\User', null);

        return redirect()->route('admin.users.index')->with('success', 'Používateľ bol zmazaný.');
    }
}
