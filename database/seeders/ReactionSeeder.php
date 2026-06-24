<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Reaction;
use App\Models\Routine;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReactionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->pluck('id');
        $routines = Routine::query()->pluck('id');
        $comments = Comment::query()->pluck('id');

        foreach ($users as $userId) {
            foreach ($routines->take(3) as $routineId) {
                Reaction::updateOrCreate(
                    [
                        'user_id' => (int) $userId,
                        'reactable_type' => Routine::class,
                        'reactable_id' => (int) $routineId,
                    ],
                    [
                        'reaction' => fake()->randomElement(['like', 'love', 'support', 'insightful', 'clap']),
                    ]
                );
            }

            foreach ($comments->take(2) as $commentId) {
                Reaction::updateOrCreate(
                    [
                        'user_id' => (int) $userId,
                        'reactable_type' => Comment::class,
                        'reactable_id' => (int) $commentId,
                    ],
                    [
                        'reaction' => fake()->randomElement(['like', 'support', 'insightful']),
                    ]
                );
            }
        }
    }
}
