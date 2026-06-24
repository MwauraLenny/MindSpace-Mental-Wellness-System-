<?php

namespace Database\Factories;

use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @extends Factory<PasswordReset>
 */
class PasswordResetFactory extends Factory
{
    protected $model = PasswordReset::class;

    public function definition(): array
    {
        return [
            'email' => DB::table('users')->inRandomOrder()->value('email') ?? fake()->unique()->safeEmail(),
            'token' => Str::random(60),
            'created_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ];
    }
}
