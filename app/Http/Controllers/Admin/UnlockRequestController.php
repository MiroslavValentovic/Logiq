<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReportUnlockRequest;
use App\Notifications\UnlockRequestReviewedNotification;
use App\Services\ReportLockService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnlockRequestController extends Controller
{
    public function index(Request $request): Response
    {
        $query = ReportUnlockRequest::query()
            ->with('user:id,first_name,last_name,email')
            ->orderBy('created_at', 'desc');

        $status = $request->get('status', 'pending');
        if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $query->where('status', $status);
        }

        $requests = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/UnlockRequests/Index', [
            'requests' => $requests,
            'filterStatus' => $status,
        ]);
    }

    public function approve(ReportUnlockRequest $unlockRequest): RedirectResponse
    {
        if ($unlockRequest->status !== 'pending') {
            return redirect()->route('admin.unlock-requests.index')->with('error', 'Požiadavka už bola vybavená.');
        }

        $unlockRequest->load('user');
        $user = $unlockRequest->user;
        $month = Carbon::parse($unlockRequest->month);
        $year = (int) $month->format('Y');
        $monthNum = (int) $month->format('n');

        app(ReportLockService::class)->unlockMonth($user->id, $year, $monthNum);
        $unlockRequest->update(['status' => 'approved']);

        $user->notify(new UnlockRequestReviewedNotification(
            true,
            $month->locale('sk')->translatedFormat('F Y'),
            $month->format('Y-m-d')
        ));

        return redirect()->route('admin.unlock-requests.index', ['status' => 'pending'])
            ->with('success', 'Požiadavka bola schválená a výkaz odomknutý.');
    }

    public function reject(ReportUnlockRequest $unlockRequest): RedirectResponse
    {
        if ($unlockRequest->status !== 'pending') {
            return redirect()->route('admin.unlock-requests.index')->with('error', 'Požiadavka už bola vybavená.');
        }

        $unlockRequest->load('user');
        $user = $unlockRequest->user;
        $month = Carbon::parse($unlockRequest->month);

        $unlockRequest->update(['status' => 'rejected']);

        $user->notify(new UnlockRequestReviewedNotification(
            false,
            $month->locale('sk')->translatedFormat('F Y'),
            $month->format('Y-m-d')
        ));

        return redirect()->route('admin.unlock-requests.index', ['status' => 'pending'])
            ->with('success', 'Požiadavka bola zamietnutá.');
    }
}
