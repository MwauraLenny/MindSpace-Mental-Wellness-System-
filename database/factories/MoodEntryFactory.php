<?php

namespace Database\Factories;

use App\Models\MoodEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MoodEntry>
 */
class MoodEntryFactory extends Factory
{
    protected $model = MoodEntry::class;

    public function definition(): array
    {
        $categoryOptions = ['happy', 'sad', 'angry', 'stressed', 'anxious', 'relaxed', 'excited', 'tired'];

        return [
            'user_id' => User::factory(),
            'mood_category' => fake()->randomElement($categoryOptions),
            'mood_value' => fake()->numberBetween(1, 5),
            'journal_note' => fake()->optional()->sentence(),
            'logged_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
