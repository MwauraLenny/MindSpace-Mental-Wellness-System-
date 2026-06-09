<?php

namespace Database\Seeders;

use App\Models\Routine;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReportSeeder extends Seeder
{
    public function run(): void
    {
        $reporter = User::query()->where('email', 'test@example.com')->first();
        $routine = Routine::query()->first();

        if (! $reporter || ! $routine) {
            return;
        }

        $now = now();

        DB::table('reports')->updateOrInsert(
            [
                'reporter_id' => $reporter->id,
                'reportable_type' => Routine::class,
                'reportable_id' => $routine->id,
                'reason' => 'inappropriate_content',
            ],
            [
                'details' => 'Example moderation report for testing the content safety workflow.',
                'status' => 'pending',
                'resolved_by' => null,
                'resolved_at' => null,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }
}
