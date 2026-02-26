<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkLogRequest;
use App\Http\Requests\UpdateWorkLogRequest;
use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkLog;
use App\Services\MonthLockService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserWorkLogController extends Controller
{
    public function index(Request $request, User $user): Response
    {
        $this->authorize('viewAny', User::class);

        $request->session()->put('admin_selected_user_id', $user->id);

        $currentYear = now()->year;
        $year = (int) $request->get('year', $currentYear);
        $month = (int) $request->get('month', now()->month);
        if ($year < $currentYear || ($year === $currentYear && $month < 1)) {
            return redirect()->route('admin.users.work-logs.index', [$user, 'year' => $currentYear, 'month' => 1, 'view' => $request->get('view', 'calendar')]);
        }

        $workLogs = $user->workLogs()
            ->with('project')
            ->forMonth($year, $month)
            ->orderBy('work_date')
            ->orderBy('id')
            ->paginate(20);

        $projects = Project::forUser($user->id)->active()->orderBy('name')->get();
        $view = $request->get('view', 'calendar');
        $canEdit = auth()->id() !== $user->id;

        $lockService = app(MonthLockService::class);
        $isCurrentMonthLocked = $lockService->isMonthLocked($user->id, $year, $month);

        $usersForAdmin = User::where('id', '!=', auth()->id())->orderBy('first_name')->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email']);

        return Inertia::render('Admin/Users/WorkLogs', [
            'targetUser' => $user->only(['id', 'name', 'email']),
            'workLogs' => $workLogs,
            'projects' => $projects,
            'year' => $year,
            'month' => $month,
            'view' => $view,
            'canEdit' => $canEdit,
            'usersForAdmin' => $usersForAdmin,
            'isCurrentMonthLocked' => $isCurrentMonthLocked,
        ]);
    }

    public function events(Request $request, User $user): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $start = $request->get('start') ? Carbon::parse($request->get('start'))->startOfDay() : now()->startOfMonth();
        $end = $request->get('end') ? Carbon::parse($request->get('end'))->endOfDay() : now()->endOfMonth();

        $logs = $user->workLogs()
            ->with('project')
            ->whereBetween('work_date', [$start, $end])
            ->orderBy('work_date')
            ->orderBy('id')
            ->get();

        $canEdit = auth()->id() !== $user->id;

        $events = $logs->map(function (WorkLog $log) use ($user, $canEdit) {
            $h = $log->minutes / 60;
            $hStr = (abs(round($h, 2) - round($h, 0)) < 0.01) ? (string) (int) round($h) : number_format($h, 1, ',', '');
            return [
                'id' => (string) $log->id,
                'title' => $log->project->name . ' — ' . $hStr . ' h',
                'start' => $log->work_date->format('Y-m-d'),
                'allDay' => true,
                'url' => $canEdit ? route('admin.users.work-logs.edit', [$user, $log]) : null,
                'extendedProps' => [
                    'projectName' => $log->project->name,
                    'minutes' => $log->minutes,
                    'note' => $log->note,
                ],
            ];
        });

        return response()->json($events);
    }

    public function create(Request $request, User $user): Response
    {
        $this->authorize('viewAny', User::class);
        if (auth()->id() === $user->id) {
            return redirect()->route('work-logs.index')->with('info', __('Use your own calendar to add entries.'));
        }

        $projects = Project::forUser($user->id)->active()->orderBy('name')->get();
        $defaultDate = $request->get('date', now()->format('Y-m-d'));

        return Inertia::render('Admin/Users/WorkLogCreate', [
            'targetUser' => $user->only(['id', 'name', 'email']),
            'projects' => $projects,
            'defaultDate' => $defaultDate,
        ]);
    }

    public function store(StoreWorkLogRequest $request, User $user): RedirectResponse
    {
        $this->authorize('viewAny', User::class);
        if (auth()->id() === $user->id) {
            return redirect()->route('admin.users.index')->with('error', 'Tu nemôžete pridávať záznamy do vlastného kalendára.');
        }

        $data = $request->validated();
        $workLog = $user->workLogs()->create($data);
        $workLog->loadMissing('project');

        ActivityLog::log(
            'work_log.created',
            'Admin: pridaný záznam práce pre ' . $user->name . ': ' . $workLog->project->name . ', ' . $workLog->work_date->format('d.m.Y'),
            'App\Models\WorkLog',
            $workLog->id
        );

        $workDate = Carbon::parse($request->input('work_date'));

        return redirect()
            ->route('admin.users.work-logs.index', [$user, 'year' => $workDate->year, 'month' => $workDate->month])
            ->with('success', 'Záznam práce bol pridaný.');
    }

    public function edit(User $user, WorkLog $work_log): Response
    {
        $this->authorize('viewAny', User::class);
        if ($work_log->user_id !== $user->id) {
            abort(404);
        }
        if (auth()->id() === $user->id) {
            return redirect()->route('work-logs.edit', $work_log)->with('info', 'Upravte v svojom kalendári.');
        }

        $workLog = $work_log->loadMissing('project');
        $workLog->work_date = $work_log->work_date->format('Y-m-d');
        $projects = Project::forUser($user->id)->active()->orderBy('name')->get();

        return Inertia::render('Admin/Users/WorkLogEdit', [
            'targetUser' => $user->only(['id', 'name', 'email']),
            'workLog' => $workLog,
            'projects' => $projects,
        ]);
    }

    public function update(UpdateWorkLogRequest $request, User $user, WorkLog $work_log): RedirectResponse
    {
        $this->authorize('viewAny', User::class);
        if ($work_log->user_id !== $user->id) {
            abort(404);
        }
        if (auth()->id() === $user->id) {
            return redirect()->route('admin.users.work-logs.index', $user)->with('error', 'Tu nemôžete upravovať vlastný kalendár.');
        }

        $work_log->update($request->validated());
        $work_log->loadMissing('project');

        ActivityLog::log(
            'work_log.updated',
            'Admin: upravený záznam práce pre ' . $user->name . ': ' . $work_log->project->name . ', ' . $work_log->work_date->format('d.m.Y'),
            'App\Models\WorkLog',
            $work_log->id
        );

        $workDate = Carbon::parse($request->input('work_date'));

        return redirect()
            ->route('admin.users.work-logs.index', [$user, 'year' => $workDate->year, 'month' => $workDate->month])
            ->with('success', 'Záznam práce bol upravený.');
    }

    public function destroy(User $user, WorkLog $work_log): RedirectResponse
    {
        $this->authorize('viewAny', User::class);
        if ($work_log->user_id !== $user->id) {
            abort(404);
        }
        if (auth()->id() === $user->id) {
            return redirect()->route('admin.users.work-logs.index', $user)->with('error', 'Tu nemôžete mazať z vlastného kalendára.');
        }

        $work_log->loadMissing('project');
        $desc = 'Admin: zmazaný záznam práce pre ' . $user->name . ': ' . $work_log->project->name . ', ' . $work_log->work_date->format('d.m.Y');
        $year = $work_log->work_date->year;
        $month = $work_log->work_date->month;
        $work_log->delete();

        ActivityLog::log('work_log.deleted', $desc, 'App\Models\WorkLog', null);

        return redirect()
            ->route('admin.users.work-logs.index', [$user, 'year' => $year, 'month' => $month])
            ->with('success', 'Záznam práce bol zmazaný.');
    }
}
