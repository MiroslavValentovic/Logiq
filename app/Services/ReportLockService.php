<?php

namespace App\Services;

use App\Models\MonthlyReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportLockService
{
    /**
     * Lock a month for the user: create/update monthly_report (status=locked, locked_at=now).
     * After this, work_logs for that month cannot be created/updated/deleted.
     */
    public function lockMonth(int $userId, int $year, int $month): MonthlyReport
    {
        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();

        return DB::transaction(function () use ($userId, $monthStart) {
            $report = MonthlyReport::firstOrCreate(
                [
                    'user_id' => $userId,
                    'month' => $monthStart->format('Y-m-d'),
                ],
                [
                    'status' => 'open',
                ]
            );

            $report->update([
                'status' => 'locked',
                'locked_at' => now(),
            ]);

            return $report;
        });
    }

    /**
     * Unlock a month (only for admin): set status=open, clear locked_at.
     * Work logs for that month can be edited again.
     */
    public function unlockMonth(int $userId, int $year, int $month): MonthlyReport
    {
        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();

        $report = MonthlyReport::where('user_id', $userId)
            ->where('month', $monthStart->format('Y-m-d'))
            ->firstOrFail();

        $report->update([
            'status' => 'open',
            'locked_at' => null,
        ]);

        return $report;
    }
}
