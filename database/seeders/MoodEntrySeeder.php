<?php

namespace Database\Seeders;

use App\Models\MoodEntry;
use App\Models\User;
use Illuminate\Database\Seeder;

class MoodEntrySeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->pluck('id');

        foreach ($users as $userId) {
            MoodEntry::factory()
                ->count(4)
                ->create([
                    'user_id' => (int) $userId,
                ]);
        }
    }
}
