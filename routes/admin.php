<?php

use App\Http\Controllers\Admin\LogController as AdminLogController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\UnlockRequestController as AdminUnlockRequestController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\UserWorkLogController;
use App\Http\Controllers\Admin\WorkLogController as AdminWorkLogController;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkLog;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('logs', [AdminLogController::class, 'index'])->name('logs.index');
Route::get('unlock-requests', [AdminUnlockRequestController::class, 'index'])->name('unlock-requests.index');
Route::post('unlock-requests/{unlockRequest}/approve', [AdminUnlockRequestController::class, 'approve'])->name('unlock-requests.approve');
Route::post('unlock-requests/{unlockRequest}/reject', [AdminUnlockRequestController::class, 'reject'])->name('unlock-requests.reject');

Route::get('/', function () {
    return Inertia::render('Admin/Dashboard', [
        'usersCount' => User::count(),
        'projectsCount' => Project::count(),
    ]);
})->name('dashboard');

Route::resource('users', AdminUserController::class);

Route::get('users/{user}/work-logs/events', [UserWorkLogController::class, 'events'])->name('users.work-logs.events');
Route::get('users/{user}/work-logs', [UserWorkLogController::class, 'index'])->name('users.work-logs.index');
Route::get('users/{user}/work-logs/create', [UserWorkLogController::class, 'create'])->name('users.work-logs.create');
Route::post('users/{user}/work-logs', [UserWorkLogController::class, 'store'])->name('users.work-logs.store');
Route::get('users/{user}/work-logs/{work_log}/edit', [UserWorkLogController::class, 'edit'])->name('users.work-logs.edit');
Route::patch('users/{user}/work-logs/{work_log}', [UserWorkLogController::class, 'update'])->name('users.work-logs.update');
Route::delete('users/{user}/work-logs/{work_log}', [UserWorkLogController::class, 'destroy'])->name('users.work-logs.destroy');

Route::bind('work_log', function (string $value) {
    $user = request()->route('user');
    $userId = $user instanceof User ? $user->id : $user;
    return $userId ? WorkLog::where('user_id', $userId)->findOrFail($value) : WorkLog::findOrFail($value);
});

Route::get('projects', [AdminProjectController::class, 'index'])->name('projects.index');
Route::get('projects/{project}', [AdminProjectController::class, 'show'])->name('projects.show');
Route::get('work-logs', [AdminWorkLogController::class, 'index'])->name('work-logs.index');
Route::get('work-logs/{work_log}', [AdminWorkLogController::class, 'show'])->name('work-logs.show');
