<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AdminSeeder::class,
        ]);

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'role' => 'user',
                'anonymous_sharing' => false,
                'email_verified_at' => now(),
            ]
        );

        User::factory(8)->create();

        $this->call([
            PresentationUserSeeder::class,
        ]);

        $this->call([
            RoutineCategorySeeder::class,
            CommunityFeedSeeder::class,
            MoodJournalSeeder::class,
            MoodEntrySeeder::class,
            RoutineSeeder::class,
            RoutineInteractionSeeder::class,
            ReactionSeeder::class,
            RecommendationHistorySeeder::class,
            UserSessionSeeder::class,
            PasswordResetSeeder::class,
            NotificationSeeder::class,
            ReportSeeder::class,
        ]);
    }
}
