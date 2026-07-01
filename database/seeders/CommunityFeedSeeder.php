<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Routine;
use App\Models\RoutineCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CommunityFeedSeeder extends Seeder
{
    public function run(): void
    {
        $userRoleId = Role::query()->where('slug', 'user')->value('id');
        $categoryIds = RoutineCategory::query()->pluck('id')->values();
        $openingActions = [
            'Sunrise Reset',
            'Evening Wind-Down',
            'Midday Focus Lift',
            'Calm Reboot',
            'Gentle Restart',
            'Stress Soother',
            'Mindful Momentum',
            'Quiet Energy Boost',
            'Clarity Break',
            'Anchor and Breathe',
        ];
        $followThrough = [
            'for Busy Days',
            'Before Study Sessions',
            'After Work Reset',
            'for Low-Energy Moments',
            'for Social Recharge',
            'to Ease Anxiety',
            'for Better Sleep',
            'for Mood Balance',
            'for Focus Recovery',
            'for Emotional Grounding',
        ];
        $bodyTemplates = [
            'I start with two minutes of slow breathing, then do one focused task for 20 minutes and finish with a short walk.',
            'This routine begins with hydration and stretching, followed by a distraction-free work sprint and a 5-minute reflection.',
            'I dim notifications, journal one page, then do gentle movement to settle my mood before returning to tasks.',
            'I pause for grounding breaths, set one realistic intention, and close with light music to keep my stress lower.',
            'I use a quick reset: breathing, tidy one small area, and then complete a single meaningful activity.',
        ];

        if ($categoryIds->isEmpty()) {
            return;
        }

        Routine::query()
            ->where('title', 'like', 'Community Starter Routine %')
            ->delete();

        for ($index = 1; $index <= 10; $index++) {
            $user = User::updateOrCreate(
                ['email' => sprintf('community%d@mindspace.demo', $index)],
                [
                    'name' => sprintf('Community User %d', $index),
                    'password' => Hash::make('password'),
                    'role' => 'user',
                    'role_id' => $userRoleId,
                    'anonymous_sharing' => false,
                    'email_verified_at' => now()->subDays($index),
                ]
            );

            for ($routineNumber = 1; $routineNumber <= 2; $routineNumber++) {
                $routineSeed = (($index - 1) * 2) + $routineNumber;
                $title = sprintf(
                    '%s %s #%02d',
                    $openingActions[($routineSeed - 1) % count($openingActions)],
                    $followThrough[($routineSeed - 1) % count($followThrough)],
                    $routineSeed
                );

                Routine::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'mood_tag' => (($routineSeed - 1) % 5) + 1,
                        'status' => 'active',
                    ],
                    [
                        'title' => $title,
                        'routine_category_id' => $categoryIds[($routineSeed - 1) % $categoryIds->count()],
                        'body' => $bodyTemplates[($routineSeed - 1) % count($bodyTemplates)],
                        'is_anonymous' => false,
                        'upvote_count' => 0,
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
