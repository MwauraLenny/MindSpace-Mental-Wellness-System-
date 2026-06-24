<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\UserSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Display the admin login view.
     */
    public function createAdmin(): View
    {
        return view('auth.login', [
            'isAdminLogin' => true,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        if ($request->boolean('admin_login') && $request->user()?->role !== 'admin') {
            Auth::guard('web')->logout();

            throw ValidationException::withMessages([
                'email' => __('Only admin accounts can sign in here.'),
            ]);
        }

        $request->session()->regenerate();

        if ($request->user()) {
            UserSession::trackActivity(
                (int) $request->user()->id,
                $request->session()->getId(),
                $request->ip(),
                $request->userAgent(),
                ['guard' => 'web']
            );
        }

        $redirectRoute = $request->user()?->role === 'admin'
            ? 'admin.dashboard'
            : 'dashboard';

        return redirect()->intended(route($redirectRoute, absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if ($request->hasSession()) {
            UserSession::endBySessionId($request->session()->getId());
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
