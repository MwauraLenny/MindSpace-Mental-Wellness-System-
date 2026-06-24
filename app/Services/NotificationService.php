<?php

namespace App\Services;

use App\Models\MoodLog;
use App\Models\Notification;
use App\Models\Routine;
use App\Models\User;
use Carbon\Carbon;

class NotificationService
{
    public function createForUser(
        int $userId,
        string $type,
        string $title,
        string $message,
        array $data = [],
        bool $dedupePerDay = false
    ): Notification {
        if ($dedupePerDay) {
            $existing = Notification::query()
                ->where('user_id', $userId)
                ->where('type', $type)
                ->where('title', $title)
                ->whereDate('created_at', Carbon::today())
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function syncSystemNotifications(User $user): void
    {
        $this->createMoodReminder($user);
        $this->createWellnessCheckInReminder($user);
        $this->createRecommendationAvailabilityNotification($user);
    }

    public function notifyRoutineInteraction(
        Routine $routine,
        User $actor,
        string $type,
        string $title,
        string $message,
        array $data = []
    ): void {
        if ((int) $routine->user_id === (int) $actor->id) {
            return;
        }

        $this->createForUser(
            (int) $routine->user_id,
            $type,
            $title,
            $message,
            array_merge([
                'routine_id' => $routine->id,
                'actor_id' => $actor->id,
            ], $data)
        );
    }

    public function notifyAdmins(string $type, string $title, string $message, array $data = []): void
    {
        $adminIds = User::query()
            ->where('role', 'admin')
            ->pluck('id');

        foreach ($adminIds as $adminId) {
            $this->createForUser((int) $adminId, $type, $title, $message, $data);
        }
    }

    public function createRoutineRecommendationNotifications(Routine $routine): void
    {
        $targetUserIds = MoodLog::query()
            ->where('user_id', '!=', $routine->user_id)
            ->where('mood_value', (int) $routine->mood_tag)
            ->pluck('user_id')
            ->unique()
            ->values();

        foreach ($targetUserIds as $targetUserId) {
            $this->createForUser(
                (int) $targetUserId,
                'recommendation_notification',
                'New routines available',
                'A new community routine matches your recent mood and may help right now.',
                [
                    'routine_id' => $routine->id,
                    'mood_tag' => (int) $routine->mood_tag,
                ],
                true
            );
        }
    }

    private function createMoodReminder(User $user): void
    {
        $hasMoodToday = MoodLog::query()
            ->where('user_id', $user->id)
            ->whereDate('logged_at', Carbon::today())
            ->exists();

        if ($hasMoodToday) {
            return;
        }

        $this->createForUser(
            (int) $user->id,
            'mood_reminder',
            'Mood reminder',
            "You haven't logged your mood today.",
            [
                'action' => 'mood_log',
            ],
            true
        );
    }

    private function createWellnessCheckInReminder(User $user): void
    {
        $latestMoodLogAt = MoodLog::query()
            ->where('user_id', $user->id)
            ->max('logged_at');

        if ($latestMoodLogAt && Carbon::parse($latestMoodLogAt)->greaterThanOrEqualTo(Carbon::now()->subDays(2))) {
            return;
        }

        $this->createForUser(
            (int) $user->id,
            'wellness_checkin',
            'Wellness check-in reminder',
            'Take a quick wellness check-in: log your mood and pick one supportive routine.',
            [
                'action' => 'wellness_checkin',
            ],
            true
        );
    }

    private function createRecommendationAvailabilityNotification(User $user): void
    {
        $latestMoodValue = MoodLog::query()
            ->where('user_id', $user->id)
            ->orderByDesc('logged_at')
            ->value('mood_value');

        if ($latestMoodValue === null) {
            return;
        }

        $hasNewMatchingRoutines = Routine::query()
            ->where('status', 'active')
            ->where('user_id', '!=', $user->id)
            ->where('mood_tag', (int) $latestMoodValue)
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->exists();

        if (! $hasNewMatchingRoutines) {
            return;
        }

        $this->createForUser(
            (int) $user->id,
            'recommendation_notification',
            'New routines available',
            'New routines are available based on your latest mood. Check your recommendations.',
            [
                'action' => 'community_feed',
                'mood_value' => (int) $latestMoodValue,
            ],
            true
        );
    }
}
