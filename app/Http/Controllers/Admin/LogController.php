<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LogController extends Controller
{
    public function index(Request $request): Response
    {
        $query = ActivityLog::query()
            ->with('user:id,first_name,last_name,email')
            ->orderBy('created_at', 'desc');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $activities = $query->paginate(10)->withQueryString();

        $users = User::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'email'])
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
            ]);

        $actionTypes = ActivityLog::query()
            ->distinct()
            ->pluck('action')
            ->filter()
            ->sort()
            ->values()
            ->all();

        return Inertia::render('Admin/Logs/Index', [
            'activities' => $activities,
            'users' => $users,
            'actionTypes' => $actionTypes,
            'filters' => $request->only(['user_id', 'date_from', 'date_to', 'action']),
        ]);
    }
}
