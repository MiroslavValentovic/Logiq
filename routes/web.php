<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportUnlockRequestController;
use App\Http\Controllers\WorkLogController;
use App\Models\Project;
use App\Models\WorkLog;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::bind('project', fn (string $value) => auth()->user()->is_admin ? Project::findOrFail($value) : Project::where('user_id', auth()->id())->findOrFail($value));
    Route::resource('projects', ProjectController::class);

    Route::bind('work_log', fn (string $value) => WorkLog::where('user_id', auth()->id())->findOrFail($value));
    Route::get('work-logs/events', [WorkLogController::class, 'events'])->name('work-logs.events');
    Route::resource('work-logs', WorkLogController::class)->parameters(['work-logs' => 'work_log']);

    Route::post('report-unlock-request', [ReportUnlockRequestController::class, 'store'])->name('report-unlock-request.store');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::post('reports/lock', [ReportController::class, 'lock'])->name('reports.lock');
    Route::post('reports/unlock', [ReportController::class, 'unlock'])->name('reports.unlock');
    Route::get('reports/{month}/download', [ReportController::class, 'download'])->name('reports.download')->where('month', '[0-9]{4}-[0-9]{2}-[0-9]{2}');
    Route::get('reports/{month}', [ReportController::class, 'show'])->name('reports.show')->where('month', '[0-9]{4}-[0-9]{2}-[0-9]{2}');
});

require __DIR__.'/auth.php';
