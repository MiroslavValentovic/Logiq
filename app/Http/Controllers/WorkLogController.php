<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkLogRequest;
use App\Http\Requests\UpdateWorkLogRequest;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\ReportUnlockRequest;
use App\Models\WorkLog;
use App\Services\MonthLockService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkLogController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $this->authorize('viewAny', WorkLog::class);

        $currentYear = now()->year;
        $year = (int) $request->get('year', $currentYear);
        $month = (int) $request->get('month', now()->month);
        if ($year < $currentYear || ($year === $currentYear && $month < 1)) {
            return redirect()->route('work-logs.index', ['year' => $currentYear, 'month' => 1, 'view' => $request->get('view', 'calendar')]);
        }

        $workLogs = auth()->user()
            ->workLogs()
            ->with('project')
            ->forMonth($year, $month)
            ->orderBy('work_date')
            ->orderBy('id')
            ->paginate(20);

        $projects = Project::forUser(auth()->id())->active()->orderBy('name')->get();

        $view = $request->get('view', 'calendar');

        $usersForAdmin = auth()->user()->is_admin
            ? \App\Models\User::where('id', '!=', auth()->id())->orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email'])
            : [];

        if (auth()->user()->is_admin && $usersForAdmin->isNotEmpty()) {
            return redirect()->route('admin.users.work-logs.index', [$usersForAdmin->first()]);
        }

        $lockService = app(MonthLockService::class);
        $isCurrentMonthLocked = $lockService->isMonthLocked(auth()->id(), $year, $month);
        $monthStart = Carbon::createFromDate($year, $month, 1)->format('Y-m-d');
        $hasPendingUnlockRequest = $isCurrentMonthLocked
            ? ReportUnlockRequest::where('user_id', auth()->id())
                ->where('month', $monthStart)
                ->where('status', 'pending')
                ->exists()
            : false;

        return Inertia::render('WorkLogs/Index', compact(
            'workLogs', 'projects', 'year', 'month', 'view', 'usersForAdmin',
            'isCurrentMonthLocked', 'hasPendingUnlockRequest'
        ));
    }

    /**
     * JSON events for FullCalendar (start/end in query string, ISO format).
     */
    public function events(Request $request): JsonResponse
    {
        $this->authorize('viewAny', WorkLog::class);

        $start = $request->get('start') ? \Carbon\Carbon::parse($request->get('start'))->startOfDay() : now()->startOfMonth();
        $end = $request->get('end') ? \Carbon\Carbon::parse($request->get('end'))->endOfDay() : now()->endOfMonth();

        $logs = auth()->user()
            ->workLogs()
            ->with('project')
            ->whereBetween('work_date', [$start, $end])
            ->orderBy('work_date')
            ->orderBy('id')
            ->get();

        $events = $logs->map(function (WorkLog $log) {
            $h = $log->minutes / 60;
            $hStr = (abs(round($h, 2) - round($h, 0)) < 0.01) ? (string) (int) round($h) : number_format($h, 1, ',', '');
            return [
                'id' => (string) $log->id,
                'title' => $log->project->name . ' — ' . $hStr . ' h',
                'start' => $log->work_date->format('Y-m-d'),
                'allDay' => true,
                'url' => route('work-logs.edit', $log),
                'extendedProps' => [
                    'projectName' => $log->project->name,
                    'minutes' => $log->minutes,
                    'note' => $log->note,
                ],
            ];
        });

        return response()->json($events);
    }

    public function create(Request $request): Response|RedirectResponse
    {
        $this->authorize('create', WorkLog::class);

        $defaultDate = $request->get('date', now()->format('Y-m-d'));
        $minDate = now()->startOfYear()->format('Y-m-d');
        if ($defaultDate < $minDate) {
            $defaultDate = $minDate;
        }
        $lockService = app(MonthLockService::class);
        if ($lockService->isDateLocked(auth()->id(), $defaultDate)) {
            $d = Carbon::parse($defaultDate);

            return redirect()
                ->route('work-logs.index', ['year' => $d->year, 'month' => $d->month])
                ->with('error', 'Výkaz za tento mesiac je uzamknutý. Nemôžete pridávať záznamy.');
        }

        $projects = Project::forUser(auth()->id())->active()->orderBy('name')->get();

        return Inertia::render('WorkLogs/Create', compact('projects', 'defaultDate'));
    }

    public function store(StoreWorkLogRequest $request): RedirectResponse
    {
        $workLog = $request->user()->workLogs()->create($request->validated());

        ActivityLog::log(
            'work_log.created',
            'Pridaný záznam práce: ' . $workLog->project->name . ', ' . $workLog->work_date->format('d.m.Y'),
            'App\Models\WorkLog',
            $workLog->id
        );

        $workDate = Carbon::parse($request->input('work_date'));

        return redirect()
            ->route('work-logs.index', ['year' => $workDate->year, 'month' => $workDate->month])
            ->with('success', 'Záznam práce bol pridaný.');
    }

    public function edit(WorkLog $work_log): Response|RedirectResponse
    {
        $this->authorize('update', $work_log);

        $lockService = app(MonthLockService::class);
        if ($lockService->isDateLocked($work_log->user_id, $work_log->work_date->format('Y-m-d'))) {
            return redirect()
                ->route('work-logs.index', ['year' => $work_log->work_date->year, 'month' => $work_log->work_date->month])
                ->with('error', 'Výkaz za tento mesiac je uzamknutý. Nemôžete upravovať záznamy.');
        }

        $workLog = $work_log->loadMissing('project');
        $workLog->work_date = $work_log->work_date->format('Y-m-d');
        $projects = Project::forUser(auth()->id())->active()->orderBy('name')->get();

        return Inertia::render('WorkLogs/Edit', compact('workLog', 'projects'));
    }

    public function update(UpdateWorkLogRequest $request, WorkLog $work_log): RedirectResponse
    {
        $work_log->update($request->validated());

        ActivityLog::log(
            'work_log.updated',
            'Upravený záznam práce: ' . $work_log->project->name . ', ' . $work_log->work_date->format('d.m.Y'),
            'App\Models\WorkLog',
            $work_log->id
        );

        $workDate = Carbon::parse($request->input('work_date'));

        return redirect()
            ->route('work-logs.index', ['year' => $workDate->year, 'month' => $workDate->month])
            ->with('success', 'Záznam práce bol upravený.');
    }

    public function destroy(WorkLog $work_log): RedirectResponse
    {
        $this->authorize('delete', $work_log);

        $lockService = app(MonthLockService::class);
        if ($lockService->isDateLocked($work_log->user_id, $work_log->work_date->format('Y-m-d'))) {
            return redirect()
                ->route('work-logs.index', ['year' => $work_log->work_date->year, 'month' => $work_log->work_date->month])
                ->with('error', 'Výkaz za tento mesiac je uzamknutý. Nemôžete mazať záznamy.');
        }

        $work_log->loadMissing('project');
        $desc = 'Zmazaný záznam práce: ' . $work_log->project->name . ', ' . $work_log->work_date->format('d.m.Y');
        $year = $work_log->work_date->year;
        $month = $work_log->work_date->month;
        $work_log->delete();

        ActivityLog::log('work_log.deleted', $desc, 'App\Models\WorkLog', null);

        return redirect()
            ->route('work-logs.index', ['year' => $year, 'month' => $month])
            ->with('success', 'Záznam práce bol zmazaný.');
    }
}
