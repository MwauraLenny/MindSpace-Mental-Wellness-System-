<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $users = User::orderBy('name')->get();

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:user,admin'],
        ]);

        if ($request->user()?->id === $user->id) {
            return back()->with('error', 'You cannot change your own role.');
        }

        $user->update([
            'role' => $validated['role'],
        ]);

        /** @var NotificationService $notificationService */
        $notificationService = app(NotificationService::class);
        $notificationService->createForUser(
            (int) $user->id,
            'admin_notification',
            'Admin updated your account',
            'Your account role was updated to '.$validated['role'].'.',
            [
                'new_role' => $validated['role'],
                'updated_by' => $request->user()?->id,
            ]
        );

        return back()->with('success', 'User role updated successfully.');
    }
}
