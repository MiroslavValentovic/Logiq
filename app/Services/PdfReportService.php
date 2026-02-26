<?php

namespace App\Services;

use App\Models\MonthlyReport;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class PdfReportService
{
    /**
     * Generate PDF for the given user and month, save to storage, update monthly_report.pdf_path.
     */
    public function generateAndSave(User $user, int $year, int $month): string
    {
        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $workLogs = $user->workLogs()
            ->with('project')
            ->forMonth($year, $month)
            ->orderBy('work_date')
            ->orderBy('id')
            ->get();

        $totalMinutes = $workLogs->sum('minutes');
        $byProject = $workLogs->groupBy('project_id')->map(function ($logs, $projectId) {
            $project = $logs->first()->project;
            return [
                'name' => $project->name,
                'minutes' => $logs->sum('minutes'),
                'entries' => $logs->count(),
            ];
        })->values();

        $workLogsByDay = $workLogs->groupBy(fn ($log) => $log->work_date->format('Y-m-d'));

        $pdf = Pdf::loadView('reports.pdf', [
            'user' => $user,
            'month' => $monthStart,
            'workLogs' => $workLogs,
            'workLogsByDay' => $workLogsByDay,
            'totalMinutes' => $totalMinutes,
            'byProject' => $byProject,
        ]);

        $dir = "reports/{$user->id}";
        $filename = sprintf('%04d-%02d.pdf', $year, $month);
        $path = "{$dir}/{$filename}";

        Storage::disk('local')->put($path, $pdf->output());

        $report = MonthlyReport::where('user_id', $user->id)
            ->where('month', $monthStart->format('Y-m-d'))
            ->first();

        if ($report) {
            $report->update(['pdf_path' => $path]);
        }

        return $path;
    }
}
