<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->pluck('id');
        $now = now();

        foreach ($users as $userId) {
            DB::table('notifications')->updateOrInsert(
                [
                    'user_id' => $userId,
                    'type' => 'routine_engagement',
                    'title' => 'Someone liked your routine',
                ],
                [
                    'message' => 'Your shared routine received new engagement from the community.',
                    'data' => json_encode(['channel' => 'community_feed']),
                    'read_at' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
