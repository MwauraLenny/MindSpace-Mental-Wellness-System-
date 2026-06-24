<?php

namespace Database\Factories;

use App\Models\MoodLog;
use App\Models\RecommendationHistory;
use App\Models\Routine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<RecommendationHistory>
 */
class RecommendationHistoryFactory extends Factory
{
    protected $model = RecommendationHistory::class;

    public function definition(): array
    {
        $shownAt = fake()->dateTimeBetween('-14 days', 'now');

        return [
            'user_id' => User::factory(),
            'routine_id' => DB::table('routines')->inRandomOrder()->value('id'),
            'mood_log_id' => DB::table('mood_logs')->inRandomOrder()->value('id'),
            'reason' => fake()->randomElement(['Mood match', 'Trending routine', 'Category preference', 'Similar users']),
            'score' => fake()->numberBetween(1, 100),
            'shown_at' => $shownAt,
            'acted_at' => fake()->optional(0.5)->dateTimeBetween($shownAt, 'now'),
            'action_taken' => fake()->optional(0.6)->randomElement(['viewed', 'liked', 'saved', 'ignored']),
            'metadata' => [
                'source' => fake()->randomElement(['feed', 'dashboard', 'notification']),
            ],
        ];
    }
}
