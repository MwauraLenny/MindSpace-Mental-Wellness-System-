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
                'name' => 'Exercise Routines',
                'slug' => 'exercise-routines',
                'description' => 'Physical activity habits for mood regulation and energy.',
            ],
            [
                'name' => 'Study Habits',
                'slug' => 'study-habits',
                'description' => 'Focus and planning routines that reduce study stress.',
            ],
            [
                'name' => 'Meditation Routines',
                'slug' => 'meditation-routines',
                'description' => 'Breathing and mindfulness practices for calm.',
            ],
            [
                'name' => 'Social Connection',
                'slug' => 'social-connection',
                'description' => 'Healthy connection routines with trusted people.',
            ],
            [
                'name' => 'Music Recommendations',
                'slug' => 'music-recommendations',
                'description' => 'Playlists, artists, and listening routines that help emotions.',
            ],
            [
                'name' => 'Hobbies',
                'slug' => 'hobbies',
                'description' => 'Creative or practical hobby routines for wellbeing.',
            ],
            [
                'name' => 'Relaxation Techniques',
                'slug' => 'relaxation-techniques',
                'description' => 'Recovery and unwind techniques for stress relief.',
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
