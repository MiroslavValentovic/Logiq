<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkLogController extends Controller
{
    public function index(Request $request): Response
    {
        $query = WorkLog::with(['user', 'project']);
        if ($request->filled('year')) {
            $query->whereYear('work_date', $request->integer('year'));
        }
        if ($request->filled('month')) {
            $query->whereMonth('work_date', $request->integer('month'));
        }
        $workLogs = $query->orderBy('work_date', 'desc')->orderBy('id', 'desc')->paginate(30);

        return Inertia::render('Admin/WorkLogs/Index', [
            'workLogs' => $workLogs,
            'year' => $request->get('year'),
            'month' => $request->get('month'),
        ]);
    }

    public function show(WorkLog $work_log): Response
    {
        $work_log->load('user', 'project');
        $workLog = $work_log->toArray();
        $workLog['work_date'] = $work_log->work_date->format('Y-m-d');

        return Inertia::render('Admin/WorkLogs/Show', ['workLog' => $workLog]);
    }
}
