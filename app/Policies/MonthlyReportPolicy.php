<?php

namespace App\Policies;

use App\Models\MonthlyReport;
use App\Models\User;

class MonthlyReportPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MonthlyReport $report): bool
    {
        return $report->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, MonthlyReport $report): bool
    {
        return $report->user_id === $user->id;
    }

    public function delete(User $user, MonthlyReport $report): bool
    {
        return $report->user_id === $user->id;
    }

    public function lock(User $user, MonthlyReport $report): bool
    {
        return $user->is_admin || $report->user_id === $user->id;
    }

    /** Len administrátor môže odomknúť report. */
    public function unlock(User $user, MonthlyReport $report): bool
    {
        return $user->is_admin;
    }
}
