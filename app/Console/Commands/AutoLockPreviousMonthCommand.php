<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\MonthLockService;
use App\Services\PdfReportService;
use App\Services\ReportLockService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutoLockPreviousMonthCommand extends Command
{
    protected $signature = 'reports:auto-lock-previous-month';

    protected $description = 'Automaticky uzamkne výkazy od januára 2020 po aktuálny mesiac (vrátane) pre všetkých používateľov, aj bez záznamov.';

    public function handle(MonthLockService $monthLock, ReportLockService $lockService, PdfReportService $pdfService): int
    {
        @ini_set('memory_limit', '512M');

        $today = Carbon::today();
        $currentYear = (int) $today->format('Y');
        $currentMonth = (int) $today->format('m');

        // Všetky mesiace od januára 2020 po aktuálny mesiac vrátane (aj bez záznamov, pre všetkých používateľov)
        $pastMonths = [];
        for ($y = 2020; $y <= $currentYear; $y++) {
            $endMonth = ($y === $currentYear) ? $currentMonth : 12;
            for ($m = 1; $m <= $endMonth; $m++) {
                $pastMonths[] = ['year' => $y, 'month' => $m];
            }
        }

        $users = User::where('is_admin', false)->get();
        $locked = 0;

        foreach ($pastMonths as $row) {
            $year = $row['year'];
            $month = $row['month'];

            foreach ($users as $user) {
                if ($monthLock->isMonthLocked($user->id, $year, $month)) {
                    continue;
                }

                $report = $lockService->lockMonth($user->id, $year, $month);
                $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();

                try {
                    $pdfService->generateAndSave($user, $year, $month);
                } catch (\Throwable $e) {
                    $this->warn("PDF pre {$user->name} {$monthStart->translatedFormat('F Y')}: {$e->getMessage()}");
                }

                ActivityLog::log(
                    'report.auto_locked',
                    'Automaticky uzamknutý výkaz: ' . $user->name . ', ' . $monthStart->translatedFormat('F Y'),
                    'App\Models\MonthlyReport',
                    $report->id
                );

                $locked++;
                $this->line("Uzamknutý: {$user->name}, {$monthStart->translatedFormat('F Y')}");
                gc_collect_cycles();
            }
        }

        $this->info("Hotovo. Automaticky uzamknutých výkazov: {$locked}.");

        return self::SUCCESS;
    }
}
