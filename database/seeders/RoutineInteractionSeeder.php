<?php

namespace Database\Seeders;

use App\Models\Routine;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoutineInteractionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->pluck('id');
        $routines = Routine::query()->get(['id', 'user_id']);
        $now = now();

        foreach ($routines as $routine) {
            foreach ($users as $userId) {
                if ($userId === $routine->user_id) {
                    continue;
                }

                DB::table('routine_likes')->updateOrInsert(
                    [
                        'routine_id' => $routine->id,
                        'user_id' => $userId,
                    ],
                    [
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );

                DB::table('saved_routines')->updateOrInsert(
                    [
                        'routine_id' => $routine->id,
                        'user_id' => $userId,
                    ],
                    [
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );

                DB::table('comments')->updateOrInsert(
                    [
                        'user_id' => $userId,
                        'commentable_type' => Routine::class,
                        'commentable_id' => $routine->id,
                        'body' => 'This routine helped me a lot this week. Thank you for sharing.',
                    ],
                    [
                        'status' => 'active',
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }

        DB::table('routines')
            ->leftJoin('routine_likes', 'routines.id', '=', 'routine_likes.routine_id')
            ->select('routines.id', DB::raw('COUNT(routine_likes.id) as likes_count'))
            ->groupBy('routines.id')
            ->get()
            ->each(function ($row) use ($now) {
                DB::table('routines')
                    ->where('id', $row->id)
                    ->update([
                        'upvote_count' => (int) $row->likes_count,
                        'updated_at' => $now,
                    ]);
            });
    }
}
