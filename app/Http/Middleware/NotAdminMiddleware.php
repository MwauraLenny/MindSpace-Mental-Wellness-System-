<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotAdminMiddleware
{
    /**
     * Block admin users from end-user only pages.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role === 'admin') {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'This page is available to end users only.');
        }

        return $next($request);
    }
}
