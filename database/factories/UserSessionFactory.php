<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<UserSession>
 */
class UserSessionFactory extends Factory
{
    protected $model = UserSession::class;

    public function definition(): array
    {
        $startedAt = fake()->dateTimeBetween('-10 days', '-1 hour');
        $lastActivityAt = fake()->dateTimeBetween($startedAt, 'now');

        return [
            'user_id' => User::factory(),
            'session_identifier' => Str::uuid()->toString(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'started_at' => $startedAt,
            'last_activity_at' => $lastActivityAt,
            'ended_at' => fake()->optional(0.4)->dateTimeBetween($lastActivityAt, 'now'),
            'meta' => [
                'device' => fake()->randomElement(['desktop', 'mobile', 'tablet']),
                'source' => fake()->randomElement(['web', 'app']),
            ],
        ];
    }
}
