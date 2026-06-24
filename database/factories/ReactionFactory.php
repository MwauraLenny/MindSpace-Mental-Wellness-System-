<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Reaction;
use App\Models\Routine;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<Reaction>
 */
class ReactionFactory extends Factory
{
    protected $model = Reaction::class;

    public function definition(): array
    {
        $fallbackUserId = DB::table('users')->inRandomOrder()->value('id');

        if (! $fallbackUserId) {
            $fallbackUserId = User::factory()->createOne()->id;
        }

        $fallbackRoutineId = DB::table('routines')->inRandomOrder()->value('id');

        if (! $fallbackRoutineId) {
            $fallbackRoutineId = DB::table('routines')->insertGetId([
                'user_id' => $fallbackUserId,
                'title' => fake()->sentence(3),
                'body' => fake()->sentence(12),
                'mood_tag' => fake()->numberBetween(1, 5),
                'is_anonymous' => false,
                'upvote_count' => 0,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $reactableClass = fake()->randomElement([Routine::class, Comment::class]);
        $reactableId = $reactableClass === Routine::class
            ? (DB::table('routines')->inRandomOrder()->value('id') ?? $fallbackRoutineId)
            : (DB::table('comments')->inRandomOrder()->value('id') ?? DB::table('comments')->insertGetId([
                'user_id' => $fallbackUserId,
                'commentable_type' => Routine::class,
                'commentable_id' => $fallbackRoutineId,
                'parent_id' => null,
                'body' => fake()->sentence(10),
                'is_anonymous' => false,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]));

        return [
            'user_id' => User::factory(),
            'reactable_type' => $reactableClass,
            'reactable_id' => $reactableId,
            'reaction' => fake()->randomElement(['like', 'love', 'support', 'insightful', 'clap']),
        ];
    }
}
