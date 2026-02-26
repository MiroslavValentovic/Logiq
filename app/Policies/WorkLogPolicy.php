<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkLog;
use App\Services\MonthLockService;

class WorkLogPolicy
{
    public function __construct(
        protected MonthLockService $monthLockService
    ) {}

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, WorkLog $workLog): bool
    {
        return $workLog->user_id === $user->id || $user->is_admin;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, WorkLog $workLog): bool
    {
        if ($workLog->user_id !== $user->id && ! $user->is_admin) {
            return false;
        }
        return ! $this->monthLockService->isDateLocked($workLog->user_id, $workLog->work_date->format('Y-m-d'));
    }

    public function delete(User $user, WorkLog $workLog): bool
    {
        if ($workLog->user_id !== $user->id && ! $user->is_admin) {
            return false;
        }
        return ! $this->monthLockService->isDateLocked($workLog->user_id, $workLog->work_date->format('Y-m-d'));
    }
}
