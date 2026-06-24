<?php

namespace Database\Seeders;

use App\Models\MoodLog;
use App\Models\RecommendationHistory;
use App\Models\Routine;
use App\Models\User;
use Illuminate\Database\Seeder;

class RecommendationHistorySeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->pluck('id');
        $routineIds = Routine::query()->pluck('id');

        foreach ($users as $userId) {
            $moodLogId = MoodLog::query()
                ->where('user_id', (int) $userId)
                ->orderByDesc('logged_at')
                ->value('id');

            foreach ($routineIds->shuffle()->take(3) as $routineId) {
                RecommendationHistory::create([
                    'user_id' => (int) $userId,
                    'routine_id' => (int) $routineId,
                    'mood_log_id' => $moodLogId,
                    'reason' => fake()->randomElement(['Mood match', 'Trending routine', 'Similar users']),
                    'score' => fake()->numberBetween(35, 100),
                    'shown_at' => now()->subDays(fake()->numberBetween(0, 10)),
                    'acted_at' => fake()->boolean(60) ? now()->subDays(fake()->numberBetween(0, 8)) : null,
                    'action_taken' => fake()->randomElement(['viewed', 'liked', 'saved', 'ignored']),
                    'metadata' => [
                        'source' => fake()->randomElement(['dashboard', 'feed', 'notification']),
                    ],
                ]);
            }
        }
    }
}
