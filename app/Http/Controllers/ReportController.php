<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ReportUnlockRequest;
use App\Models\User;
use App\Notifications\UnlockRequestReviewedNotification;
use App\Services\PdfReportService;
use App\Services\ReportLockService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private const ADMIN_SELECTED_USER_KEY = 'admin_selected_user_id';

    private function reportUser(Request $request): User
    {
        if (! $request->user()->is_admin) {
            return $request->user();
        }
        $userId = $request->get('user_id') ?? $request->session()->get(self::ADMIN_SELECTED_USER_KEY);
        if ($userId) {
            $user = User::where('id', $userId)->where('is_admin', false)->first();
            if ($user) {
                $request->session()->put(self::ADMIN_SELECTED_USER_KEY, $user->id);

                return $user;
            }
        }
        $first = User::where('is_admin', false)->orderBy('first_name')->orderBy('last_name')->first();
        if ($first) {
            $request->session()->put(self::ADMIN_SELECTED_USER_KEY, $first->id);

            return $first;
        }

        return $request->user();
    }

    public function index(Request $request): Response
    {
        $currentYear = now()->year;
        $year = (int) $request->get('year', $currentYear);
        $month = (int) $request->get('month', now()->month);
        if ($year < $currentYear || ($year === $currentYear && $month < 1)) {
            return redirect()->route('reports.index', ['year' => $currentYear, 'month' => 1]);
        }
        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();

        $user = $this->reportUser($request);

        $report = $user->monthlyReports()
            ->where('month', $monthStart->format('Y-m-d'))
            ->first();

        $workLogs = $user->workLogs()
            ->with('project')
            ->forMonth($year, $month)
            ->orderBy('work_date')
            ->get();

        $totalMinutes = $workLogs->sum('minutes');
        $monthStartStr = $monthStart->format('Y-m-d');

        $payload = [
            'report' => $report,
            'workLogs' => $workLogs,
            'totalMinutes' => $totalMinutes,
            'year' => $year,
            'month' => $month,
            'monthStart' => $monthStartStr,
        ];

        if (! $request->user()->is_admin && $report?->status === 'locked') {
            $payload['unlockRequestPending'] = ReportUnlockRequest::where('user_id', $user->id)
                ->where('month', $monthStartStr)
                ->where('status', 'pending')
                ->exists();
        }

        if ($request->user()->is_admin) {
            $payload['usersForAdmin'] = User::where('is_admin', false)
                ->orderBy('first_name')->orderBy('last_name')
                ->get(['id', 'first_name', 'last_name', 'email']);
            $payload['selectedUserId'] = $user->id;
        }

        return Inertia::render('Reports/Index', $payload);
    }

    public function show(Request $request, string $monthParam): Response|RedirectResponse
    {
        $monthStart = Carbon::parse($monthParam)->startOfMonth();
        $year = (int) $monthStart->format('Y');
        $month = (int) $monthStart->format('m');
        $currentYear = now()->year;
        if ($year < $currentYear || ($year === $currentYear && $month < 1)) {
            return redirect()->route('reports.show', Carbon::createFromDate($currentYear, 1, 1)->format('Y-m-d'));
        }

        $user = $this->reportUser($request);

        $report = $user->monthlyReports()
            ->where('month', $monthStart->format('Y-m-d'))
            ->first();

        $workLogs = $user->workLogs()
            ->with('project')
            ->forMonth($year, $month)
            ->orderBy('work_date')
            ->get();

        $totalMinutes = $workLogs->sum('minutes');
        $monthStartStr = $monthStart->format('Y-m-d');

        $payload = [
            'report' => $report,
            'workLogs' => $workLogs,
            'totalMinutes' => $totalMinutes,
            'monthStart' => $monthStartStr,
            'year' => $year,
            'month' => $month,
        ];

        if ($request->user()->is_admin) {
            $payload['usersForAdmin'] = User::where('is_admin', false)
                ->orderBy('first_name')->orderBy('last_name')
                ->get(['id', 'first_name', 'last_name', 'email']);
            $payload['selectedUserId'] = $user->id;
        }

        return Inertia::render('Reports/Show', $payload);
    }

    public function lock(Request $request): RedirectResponse
    {
        $year = (int) $request->input('year');
        $month = (int) $request->input('month');
        $user = $request->user()->is_admin
            ? User::where('id', $request->input('user_id'))->where('is_admin', false)->firstOrFail()
            : $request->user();

        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $report = $user->monthlyReports()->where('month', $monthStart->format('Y-m-d'))->first();

        if ($report) {
            $this->authorize('lock', $report);
        }

        $lockService = app(ReportLockService::class);
        $report = $lockService->lockMonth($user->id, $year, $month);

        $pdfService = app(PdfReportService::class);
        $pdfService->generateAndSave($user, $year, $month);

        ActivityLog::log(
            'report.locked',
            'Zamknutý report: ' . $user->name . ', ' . $monthStart->translatedFormat('F Y'),
            'App\Models\MonthlyReport',
            $report->id
        );

        $params = ['month' => $monthStart->format('Y-m-d')];
        if ($request->user()->is_admin) {
            $params['user_id'] = $user->id;
        }

        return redirect()->route('reports.show', $params)->with('success', 'Mesiac bol uzamknutý a PDF vygenerované.');
    }

    public function download(Request $request, string $monthParam): StreamedResponse|RedirectResponse
    {
        $monthStart = Carbon::parse($monthParam)->startOfMonth();
        $currentUser = $request->user();

        $user = $currentUser->is_admin
            ? User::where('id', $request->get('user_id') ?? $request->session()->get(self::ADMIN_SELECTED_USER_KEY))
                ->where('is_admin', false)->firstOrFail()
            : $currentUser;

        $report = $user->monthlyReports()
            ->where('month', $monthStart->format('Y-m-d'))
            ->firstOrFail();

        if ($report->user_id !== $user->id) {
            abort(403);
        }

        if (! $report->pdf_path || ! Storage::disk('local')->exists($report->pdf_path)) {
            return redirect()->back()->with('error', 'PDF sa nenašlo. Skúste znova uzamknúť mesiac.');
        }

        $filename = sprintf('report-%s-%s.pdf', $user->name, $monthStart->format('Y-m'));

        return Storage::disk('local')->download(
            $report->pdf_path,
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    /** Odomknúť mesiac – len administrátor. */
    public function unlock(Request $request): RedirectResponse
    {
        $year = (int) $request->input('year');
        $month = (int) $request->input('month');
        $user = $request->user()->is_admin
            ? \App\Models\User::where('id', $request->input('user_id'))->where('is_admin', false)->firstOrFail()
            : $request->user();

        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $report = $user->monthlyReports()->where('month', $monthStart->format('Y-m-d'))->firstOrFail();

        $this->authorize('unlock', $report);

        app(ReportLockService::class)->unlockMonth($user->id, $year, $month);

        $updated = ReportUnlockRequest::where('user_id', $user->id)
            ->whereYear('month', $year)
            ->whereMonth('month', $month)
            ->where('status', 'pending')
            ->update(['status' => 'approved']);

        if ($updated > 0) {
            $user->notify(new UnlockRequestReviewedNotification(
                true,
                $monthStart->locale('sk')->translatedFormat('F Y'),
                $monthStart->format('Y-m-d')
            ));
        }

        ActivityLog::log(
            'report.unlocked',
            'Odomknutý report: ' . $user->name . ', ' . $monthStart->locale('sk')->translatedFormat('F Y'),
            'App\Models\MonthlyReport',
            $report->id
        );

        $params = ['year' => $year, 'month' => $month];
        if ($request->user()->is_admin) {
            $params['user_id'] = $user->id;
        }

        return redirect()->route('reports.index', $params)->with('success', 'Report bol odomknutý.');
    }
}
