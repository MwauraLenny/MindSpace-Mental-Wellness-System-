<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoutineSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('routines')
            ->where('title', 'REGEXP', '^(Steadying|Recovery|Balance|Momentum|Peak) Mood [1-5] Routine [0-9]{2}$')
            ->delete();

        $users = User::query()->get(['id']);

        if ($users->isEmpty()) {
            return;
        }

        $now = now();

        $categoryMap = DB::table('routine_categories')
            ->pluck('id', 'slug');

        $durations = ['7 minutes', '10 minutes', '12 minutes', '15 minutes', '20 minutes', '25 minutes', '30 minutes', '35 minutes'];
        $titleIntents = [
            'Reset Plan',
            'Focus Flow',
            'Grounding Ritual',
            'Recovery Sequence',
            'Momentum Builder',
            'Calm Practice',
            'Clarity Routine',
            'Energy Rhythm',
        ];
        $timeAnchors = ['Morning', 'Afternoon', 'Evening', 'Night'];

        $actionByMood = [
            1 => [
                'body' => 'do box breathing and write three grounding observations',
                'aftercare' => 'then silence notifications for one hour',
                'prefix' => 'Steadying',
            ],
            2 => [
                'body' => 'take a gentle walk and stretch shoulders and jaw',
                'aftercare' => 'then set one realistic priority for today',
                'prefix' => 'Recovery',
            ],
            3 => [
                'body' => 'run a focused work block and hydrate between tasks',
                'aftercare' => 'then reset my desk to reduce cognitive clutter',
                'prefix' => 'Balance',
            ],
            4 => [
                'body' => 'channel energy into exercise and check in with a friend',
                'aftercare' => 'then prepare tomorrow\'s schedule before bed',
                'prefix' => 'Momentum',
            ],
            5 => [
                'body' => 'start with deep work and follow with intentional recovery',
                'aftercare' => 'then share one encouragement message in community',
                'prefix' => 'Peak',
            ],
        ];

        $categoryRotation = [
            'exercise-routines',
            'study-habits',
            'meditation-routines',
            'social-connection',
            'music-recommendations',
            'hobbies',
            'relaxation-techniques',
        ];

        foreach (range(1, 5) as $moodTag) {
            $moodMeta = $actionByMood[$moodTag];

            foreach (range(1, 30) as $index) {
                $user = $users[($index + $moodTag - 2) % $users->count()];
                $duration = $durations[($index + $moodTag - 1) % count($durations)];
                $categorySlug = $categoryRotation[($index + $moodTag - 1) % count($categoryRotation)];

                $title = sprintf(
                    '%s %s (%s) M%d-%02d',
                    $moodMeta['prefix'],
                    $titleIntents[($index + $moodTag - 2) % count($titleIntents)],
                    $timeAnchors[($index + $moodTag - 2) % count($timeAnchors)],
                    $moodTag,
                    $index
                );
                $body = sprintf(
                    'For mood score %d, I spend %s to %s, %s.',
                    $moodTag,
                    $duration,
                    $moodMeta['body'],
                    $moodMeta['aftercare']
                );

                DB::table('routines')->updateOrInsert(
                    [
                        'mood_tag' => $moodTag,
                        'status' => 'active',
                        'is_anonymous' => false,
                        'user_id' => $user->id,
                        'routine_category_id' => $categoryMap[$categorySlug] ?? null,
                    ],
                    [
                        'title' => $title,
                        'user_id' => $user->id,
                        'mood_tag' => $moodTag,
                        'routine_category_id' => $categoryMap[$categorySlug] ?? null,
                        'body' => $body,
                        'is_anonymous' => false,
                        'upvote_count' => 0,
                        'status' => 'active',
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }
    }
}
