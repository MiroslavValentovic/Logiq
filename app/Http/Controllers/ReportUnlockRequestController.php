<?php

namespace App\Http\Controllers;

use App\Models\ReportUnlockRequest;
use App\Services\MonthLockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportUnlockRequestController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2030'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $user = $request->user();
        if ($user->is_admin) {
            return redirect()->back()->with('error', 'Administrátor nemôže žiadať o odomknutie.');
        }

        $year = (int) $request->input('year');
        $month = (int) $request->input('month');
        $monthStart = now()->setYear($year)->setMonth($month)->startOfMonth();

        $lockService = app(MonthLockService::class);
        if (! $lockService->isMonthLocked($user->id, $year, $month)) {
            return redirect()->back()->with('info', 'Výkaz pre tento mesiac nie je zamknutý.');
        }

        $existing = ReportUnlockRequest::where('user_id', $user->id)
            ->where('month', $monthStart->format('Y-m-d'))
            ->first();

        if ($existing) {
            if ($existing->status === 'pending') {
                return redirect()->back()->with('info', 'Požiadavka na odomknutie pre tento mesiac už bola odoslaná.');
            }
            // Umožniť znovu požiadať: zmeniť stav späť na pending
            $existing->update(['status' => 'pending']);
            return redirect()->back()->with('success', 'Požiadavka na odomknutie bola znova odoslaná administrátorovi.');
        }

        ReportUnlockRequest::create([
            'user_id' => $user->id,
            'month' => $monthStart->format('Y-m-d'),
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Požiadavka na odomknutie bola odoslaná administrátorovi.');
    }
}
