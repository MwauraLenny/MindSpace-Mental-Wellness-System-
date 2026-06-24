<?php

namespace App\Http\Middleware;

use App\Services\NotificationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SyncNotificationsMiddleware
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user) {
            $this->notificationService->syncSystemNotifications($user);
        }

        return $next($request);
    }
}
