<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->suspended_at) {
            return $next($request);
        }

        if ($user->suspended_until && now()->greaterThanOrEqualTo($user->suspended_until)) {
            $user->update([
                'suspended_at' => null,
                'suspended_until' => null,
                'suspension_reason' => null,
            ]);

            return $next($request);
        }

        $periodMessage = $user->suspended_until
            ? ' Suspension ends '.$user->suspended_until->diffForHumans().'.'
            : ' Suspension period is currently open-ended.';

        if ($request->hasSession()) {
            UserSession::endBySessionId($request->session()->getId());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('error', 'Your account is currently suspended.'.$periodMessage.' Please contact support if you believe this is a mistake.');
    }
}
