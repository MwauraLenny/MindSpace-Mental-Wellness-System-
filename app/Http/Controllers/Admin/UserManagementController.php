<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Journal;
use App\Models\MoodLog;
use App\Models\Routine;
use App\Models\UserSession;
use App\Services\NotificationService;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->orderBy('name', 'asc')
            ->get()
            ->map(function (User $user) {
                $lastMoodLogAt = MoodLog::query()->where('user_id', $user->id)->max('logged_at');
                $lastJournalAt = Journal::query()->where('user_id', $user->id)->max('created_at');
                $lastRoutineAt = Routine::query()->where('user_id', $user->id)->max('created_at');
                $lastCommentAt = Comment::query()->where('user_id', $user->id)->max('created_at');
                $lastNotificationAt = DB::table('notifications')->where('user_id', $user->id)->max('created_at');

                $activityDates = collect([
                    $lastMoodLogAt,
                    $lastJournalAt,
                    $lastRoutineAt,
                    $lastCommentAt,
                    $lastNotificationAt,
                ])->filter();

                $user->setAttribute('activity_events_count',
                    MoodLog::query()->where('user_id', $user->id)->count()
                    + Journal::query()->where('user_id', $user->id)->count()
                    + Routine::query()->where('user_id', $user->id)->count()
                    + Comment::query()->where('user_id', $user->id)->count()
                );
                $user->setAttribute('last_activity_at', $activityDates->isNotEmpty() ? $activityDates->max() : null);

                return $user;
            });

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

    public function suspend(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->id === $user->id) {
            return back()->with('error', 'You cannot suspend your own account.');
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user->update([
            'suspended_at' => now(),
            'suspension_reason' => $validated['reason'] ?? null,
        ]);

        UserSession::endAllForUser((int) $user->id);
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();

        /** @var NotificationService $notificationService */
        $notificationService = app(NotificationService::class);
        $notificationService->createForUser(
            (int) $user->id,
            'admin_notification',
            'Account suspended',
            'Your account has been suspended by an administrator.',
            [
                'suspended_by' => $request->user()?->id,
                'reason' => $validated['reason'] ?? null,
            ]
        );

        return back()->with('success', 'User suspended successfully.');
    }

    public function unsuspend(Request $request, User $user): RedirectResponse
    {
        if (! $user->suspended_at) {
            return back()->with('success', 'User account is already active.');
        }

        $user->update([
            'suspended_at' => null,
            'suspension_reason' => null,
        ]);

        /** @var NotificationService $notificationService */
        $notificationService = app(NotificationService::class);
        $notificationService->createForUser(
            (int) $user->id,
            'admin_notification',
            'Account reactivated',
            'Your account has been reactivated by an administrator.',
            [
                'reactivated_by' => $request->user()?->id,
            ]
        );

        return back()->with('success', 'User account reactivated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()?->id === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        UserSession::endAllForUser((int) $user->id);
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->delete();

        User::query()->whereKey($user->id)->delete();

        return back()->with('success', 'User deleted successfully.');
    }

    public function activity(User $user): View
    {
        return view('admin.users.activity', [
            'user' => $user,
            'activitySummary' => [
                'mood_logs' => MoodLog::query()->where('user_id', $user->id)->count(),
                'journals' => Journal::query()->where('user_id', $user->id)->count(),
                'routines' => Routine::query()->where('user_id', $user->id)->count(),
                'comments' => Comment::query()->where('user_id', $user->id)->count(),
                'notifications' => DB::table('notifications')->where('user_id', $user->id)->count(),
            ],
            'activityTimeline' => $this->buildActivityTimeline($user->id),
        ]);
    }

    private function buildActivityTimeline(int $userId): Collection
    {
        $entries = collect();

        MoodLog::query()
            ->where('user_id', $userId)
            ->orderByDesc('logged_at')
            ->limit(10)
            ->get()
            ->each(function (MoodLog $log) use ($entries): void {
                $entries->push([
                    'type' => 'Mood log',
                    'description' => 'Logged mood: '.$log->mood_label,
                    'at' => $log->logged_at,
                ]);
            });

        Journal::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->each(function (Journal $journal) use ($entries): void {
                $entries->push([
                    'type' => 'Journal',
                    'description' => 'Journal entry: '.$journal->title,
                    'at' => $journal->created_at,
                ]);
            });

        Routine::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->each(function (Routine $routine) use ($entries): void {
                $entries->push([
                    'type' => 'Routine',
                    'description' => 'Shared routine: '.$routine->display_title,
                    'at' => $routine->created_at,
                ]);
            });

        Comment::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->each(function (Comment $comment) use ($entries): void {
                $entries->push([
                    'type' => 'Comment',
                    'description' => 'Posted a community comment',
                    'at' => $comment->created_at,
                ]);
            });

        DB::table('notifications')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->each(function ($notification) use ($entries): void {
                $entries->push([
                    'type' => 'Notification',
                    'description' => 'Received: '.$notification->title,
                    'at' => $notification->created_at,
                ]);
            });

        return $entries
            ->filter(fn ($entry) => ! empty($entry['at']))
            ->sortByDesc('at')
            ->take(30)
            ->values();
    }
}
