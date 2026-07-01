<?php

namespace Database\Seeders;

use App\Models\MoodLog;
use App\Models\Notification;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PresentationUserSeeder extends Seeder
{
    /**
     * Seed a long-term demo user with realistic app activity.
     */
    public function run(): void
    {
        $createdAt = Carbon::now()->subMonths(11)->subDays(10)->startOfDay();
        $userRoleId = Role::query()->where('slug', 'user')->value('id');

        $user = User::updateOrCreate(
            ['email' => 'avery@mindspace.com'],
            [
                'name' => 'Avery',
                'password' => Hash::make('MindspaceDemo!2026'),
                'role' => 'user',
                'role_id' => $userRoleId,
                'anonymous_sharing' => false,
                'email_verified_at' => Carbon::now()->subMonths(11),
                'created_at' => $createdAt,
                'updated_at' => Carbon::now()->subHours(4),
            ]
        );

        $moodKeys = array_keys(MoodLog::categories());

        MoodLog::query()->where('user_id', $user->id)->delete();

        // Keep 111 entries, but make them daily so the presenter account shows a true streak.
        for ($dayOffset = 110; $dayOffset >= 0; $dayOffset--) {
            $keyIndex = (int) ($dayOffset % count($moodKeys));
            $category = $moodKeys[$keyIndex];
            $loggedAt = Carbon::now()->subDays($dayOffset)->setTime(20, 15);

            MoodLog::create([
                'user_id' => $user->id,
                'mood_category' => $category,
                'mood_value' => MoodLog::scoreFromCategory($category),
                'journal_note' => 'Presentation account check-in for '.$loggedAt->format('M d, Y').'.',
                'routine_shared' => $dayOffset % 12 === 0,
                'logged_at' => $loggedAt,
            ]);
        }

        Notification::query()->where('user_id', $user->id)->delete();

        Notification::query()->create([
            'user_id' => $user->id,
            'type' => 'achievement',
            'title' => 'Streak milestone reached',
            'message' => 'Amazing consistency. You just reached another streak milestone.',
            'data' => ['kind' => 'streak', 'days' => 14],
            'read_at' => null,
            'created_at' => Carbon::now()->subHours(8),
            'updated_at' => Carbon::now()->subHours(8),
        ]);

        Notification::query()->create([
            'user_id' => $user->id,
            'type' => 'routine',
            'title' => 'New routine match',
            'message' => 'A community routine was recommended based on your recent mood pattern.',
            'data' => ['kind' => 'recommendation'],
            'read_at' => null,
            'created_at' => Carbon::now()->subDays(1),
            'updated_at' => Carbon::now()->subDays(1),
        ]);

        Notification::query()->create([
            'user_id' => $user->id,
            'type' => 'community',
            'title' => 'Someone liked your shared routine',
            'message' => 'Your routine is helping others. Keep sharing what works for you.',
            'data' => ['kind' => 'engagement'],
            'read_at' => Carbon::now()->subHours(2),
            'created_at' => Carbon::now()->subDays(2),
            'updated_at' => Carbon::now()->subHours(2),
        ]);
    }
}
