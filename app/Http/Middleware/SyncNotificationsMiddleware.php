<?php

namespace App\Http\Middleware;

use App\Models\Notification;
use App\Services\NotificationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class SyncNotificationsMiddleware
{
    public function __construct(private readonly NotificationService $notificationService)
    {
    }

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user) {
            $syncKey = 'notifications:sync:last:user:'.$user->id;
            $shouldSync = Cache::get($syncKey) === null;

            if ($shouldSync) {
                $this->notificationService->syncSystemNotifications($user);
                Cache::put($syncKey, now()->timestamp, now()->addMinutes(5));
            }

            $unreadCount = Cache::remember(
                'notifications:unread-count:user:'.$user->id,
                now()->addSeconds(30),
                fn (): int => Notification::query()
                    ->where('user_id', $user->id)
                    ->whereNull('read_at')
                    ->count()
            );

            View::share('unreadNotificationCount', $unreadCount);
        } else {
            View::share('unreadNotificationCount', 0);
        }

        return $next($request);
    }
}
