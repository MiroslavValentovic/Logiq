<?php

namespace App\Services;

use App\Models\MonthlyReport;
use Carbon\Carbon;

class MonthLockService
{
    /**
     * Check if the given year/month is locked for the user (no work log changes allowed).
     */
    public function isMonthLocked(int $userId, int $year, int $month): bool
    {
        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();

        return MonthlyReport::query()
            ->where('user_id', $userId)
            ->where('month', $monthStart->format('Y-m-d'))
            ->where('status', 'locked')
            ->whereNotNull('locked_at')
            ->exists();
    }

    /**
     * Check if the date's month is locked for the user.
     */
    public function isDateLocked(int $userId, string $date): bool
    {
        $d = Carbon::parse($date);

        return $this->isMonthLocked($userId, (int) $d->format('Y'), (int) $d->format('m'));
    }
}
