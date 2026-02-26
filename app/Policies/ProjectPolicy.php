<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return $user->is_admin || $project->users()->where('users.id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Project $project): bool
    {
        return $user->is_admin || $project->users()->where('users.id', $user->id)->exists();
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->is_admin;
    }
}
