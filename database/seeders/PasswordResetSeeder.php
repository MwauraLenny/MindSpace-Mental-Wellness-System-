<?php

namespace Database\Seeders;

use App\Models\PasswordReset;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PasswordResetSeeder extends Seeder
{
    public function run(): void
    {
        $emails = DB::table('users')
            ->inRandomOrder()
            ->limit(4)
            ->pluck('email');

        foreach ($emails as $email) {
            PasswordReset::updateOrCreate(
                ['email' => $email],
                [
                    'token' => Str::random(60),
                    'created_at' => now()->subHours(fake()->numberBetween(1, 48)),
                ]
            );
        }
    }
}
