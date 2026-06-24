<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackUserSessionActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user && $request->hasSession()) {
            UserSession::trackActivity(
                (int) $user->id,
                $request->session()->getId(),
                $request->ip(),
                $request->userAgent(),
                [
                    'guard' => 'web',
                ]
            );
        }

        return $next($request);
    }
}
