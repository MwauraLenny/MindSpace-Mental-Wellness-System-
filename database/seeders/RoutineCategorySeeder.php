<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoutineCategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $categories = [
            [
                'name' => 'Sleep Hygiene',
                'slug' => 'sleep-hygiene',
                'description' => 'Routines that improve sleep quality and consistency.',
            ],
            [
                'name' => 'Movement',
                'slug' => 'movement',
                'description' => 'Physical activities that support emotional regulation.',
            ],
            [
                'name' => 'Mindfulness',
                'slug' => 'mindfulness',
                'description' => 'Breathing, meditation, and grounding practices.',
            ],
            [
                'name' => 'Social Connection',
                'slug' => 'social-connection',
                'description' => 'Healthy connection routines with trusted people.',
            ],
        ];

        foreach ($categories as $category) {
            DB::table('routine_categories')->updateOrInsert(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
