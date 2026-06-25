<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->banned_at) {
            return $next($request);
        }

        if ($user->banned_until && now()->greaterThanOrEqualTo($user->banned_until)) {
            $user->update([
                'banned_at' => null,
                'banned_until' => null,
                'ban_reason' => null,
                'suspended_at' => null,
                'suspended_until' => null,
                'suspension_reason' => null,
            ]);

            return $next($request);
        }

        $periodMessage = $user->banned_until
            ? ' Ban ends '.$user->banned_until->diffForHumans().'.'
            : ' Ban period is currently open-ended.';

        if ($request->hasSession()) {
            UserSession::endBySessionId($request->session()->getId());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('error', 'Your account has been banned.'.$periodMessage.' Please contact support if you believe this is a mistake.');
    }
}
