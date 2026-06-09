<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoutineSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->get(['id']);
        $now = now();

        $categoryMap = DB::table('routine_categories')
            ->pluck('id', 'slug');

        $routineTemplates = [
            1 => [
                ['body' => 'When things feel very heavy, I do :duration of box breathing, then write three grounding observations from my room.', 'category_slug' => 'mindfulness'],
                ['body' => 'I take a warm shower, sip water slowly, and do a :duration phone-free reset before checking messages.', 'category_slug' => 'sleep-hygiene'],
                ['body' => 'I dim lights and follow a :duration guided breathing session to reduce overwhelm before bed.', 'category_slug' => 'mindfulness'],
            ],
            2 => [
                ['body' => 'I go for a gentle :duration walk with no headphones, then journal one thing that felt manageable.', 'category_slug' => 'movement'],
                ['body' => 'I do a :duration stretch flow and prepare tomorrow\'s to-do list with only three realistic priorities.', 'category_slug' => 'movement'],
                ['body' => 'I stop caffeine after lunch and start a :duration wind-down routine with a calm playlist.', 'category_slug' => 'sleep-hygiene'],
            ],
            3 => [
                ['body' => 'I use a :duration focus block, then take a short sunlight break and drink water before the next task.', 'category_slug' => 'movement'],
                ['body' => 'I do :duration of mindful breathing, then clean one small space to reduce mental clutter.', 'category_slug' => 'mindfulness'],
                ['body' => 'I check in with a friend for :duration and share one win and one challenge from the day.', 'category_slug' => 'social-connection'],
            ],
            4 => [
                ['body' => 'I schedule a :duration walk-and-talk with a friend and finish with a gratitude voice note.', 'category_slug' => 'social-connection'],
                ['body' => 'I do a :duration strength workout, then meal prep for tomorrow to keep momentum.', 'category_slug' => 'movement'],
                ['body' => 'I set a :duration evening reflection ritual: low lights, no social apps, and journaling.', 'category_slug' => 'sleep-hygiene'],
            ],
            5 => [
                ['body' => 'On high-energy days I do :duration of deep work first, then reward myself with outdoor time.', 'category_slug' => 'movement'],
                ['body' => 'I use a :duration creative sprint, then document what worked so I can repeat it on low days.', 'category_slug' => 'mindfulness'],
                ['body' => 'I share a :duration encouragement call with someone and end the day with a sleep-protecting routine.', 'category_slug' => 'social-connection'],
            ],
        ];

        $durations = ['10 minutes', '15 minutes', '20 minutes', '25 minutes', '30 minutes'];

        foreach ($users as $userIndex => $user) {
            foreach (range(1, 5) as $moodTag) {
                $templates = $routineTemplates[$moodTag];
                $template = $templates[($userIndex + $moodTag) % count($templates)];
                $duration = $durations[($user->id + $moodTag) % count($durations)];
                $body = str_replace(':duration', $duration, $template['body']);

                DB::table('routines')->updateOrInsert(
                    [
                        'user_id' => $user->id,
                        'mood_tag' => $moodTag,
                    ],
                    [
                        'routine_category_id' => $categoryMap[$template['category_slug']] ?? null,
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
