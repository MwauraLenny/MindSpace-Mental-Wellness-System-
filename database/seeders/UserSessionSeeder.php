<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Database\Seeder;

class UserSessionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->pluck('id');

        foreach ($users as $userId) {
            UserSession::factory()
                ->count(2)
                ->create([
                    'user_id' => (int) $userId,
                ]);
        }
    }
}
