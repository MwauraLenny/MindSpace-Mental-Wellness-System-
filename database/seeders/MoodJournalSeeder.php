<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MoodJournalSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->get(['id']);
        $now = now();

        foreach ($users as $user) {
            $moodRows = [
                [
                    'user_id' => $user->id,
                    'value' => 2,
                    'note' => 'Feeling low this morning but trying to reset.',
                    'recorded_at' => now()->subDays(2),
                    'source' => 'manual',
                ],
                [
                    'user_id' => $user->id,
                    'value' => 4,
                    'note' => 'Mood improved after a walk and hydration.',
                    'recorded_at' => now()->subDay(),
                    'source' => 'manual',
                ],
            ];

            foreach ($moodRows as $mood) {
                DB::table('moods')->updateOrInsert(
                    [
                        'user_id' => $mood['user_id'],
                        'recorded_at' => $mood['recorded_at'],
                    ],
                    [
                        'value' => $mood['value'],
                        'note' => $mood['note'],
                        'source' => $mood['source'],
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }

            $latestMoodId = DB::table('moods')
                ->where('user_id', $user->id)
                ->orderByDesc('recorded_at')
                ->value('id');

            DB::table('journals')->updateOrInsert(
                [
                    'user_id' => $user->id,
                    'entry_date' => now()->toDateString(),
                ],
                [
                    'mood_id' => $latestMoodId,
                    'title' => 'Daily Reflection',
                    'body' => 'Today I focused on one small win and practiced gratitude.',
                    'visibility' => 'private',
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
