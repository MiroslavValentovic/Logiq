<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $notifications = $user->notifications()->paginate(20);

        $user->unreadNotifications->markAsRead();

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }
}
