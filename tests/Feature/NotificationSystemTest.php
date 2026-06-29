<?php

namespace Tests\Feature;

use App\Models\MoodLog;
use App\Models\Notification;
use App\Models\Routine;
use App\Models\RoutineCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_receives_mood_and_wellness_reminders_when_no_recent_logs(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne();

        $this->actingAs($user)->get('/dashboard')->assertOk();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'mood_reminder',
            'title' => 'Mood reminder',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'wellness_checkin',
            'title' => 'Wellness check-in reminder',
        ]);
    }

    public function test_user_receives_recommendation_notification_when_new_matching_routine_is_shared(): void
    {
        /** @var User $targetUser */
        $targetUser = User::factory()->createOne();
        /** @var User $sharer */
        $sharer = User::factory()->createOne();

        MoodLog::create([
            'user_id' => $targetUser->id,
            'mood_category' => 'stressed',
            'mood_value' => 1,
            'logged_at' => now(),
        ]);

        $category = RoutineCategory::create([
            'name' => 'Exercise Routines',
            'slug' => 'exercise-routines',
            'description' => 'Exercise routines',
            'is_active' => true,
        ]);

        $this->actingAs($sharer)->post('/routines', [
            'title' => 'Stress Relief Walk',
            'body' => 'Walk and breathe for 15 minutes.',
            'mood_tag' => 1,
            'routine_category_id' => $category->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $targetUser->id,
            'type' => 'recommendation_notification',
            'title' => 'New routines available',
        ]);
    }

    public function test_user_receives_community_interaction_notification_when_routine_is_liked(): void
    {
        /** @var User $owner */
        $owner = User::factory()->createOne();
        /** @var User $liker */
        $liker = User::factory()->createOne();

        $routine = Routine::create([
            'user_id' => $owner->id,
            'title' => 'Evening Calm',
            'body' => 'Tea, breathing, and short journaling.',
            'mood_tag' => 2,
            'status' => 'active',
        ]);

        $this->actingAs($liker)->post('/routines/'.$routine->id.'/upvote')->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $owner->id,
            'type' => 'community_interaction',
            'title' => 'Someone liked your routine',
        ]);
    }

    public function test_admin_action_creates_admin_notification_for_target_user(): void
    {
        /** @var User $admin */
        $admin = User::factory()->createOne(['role' => 'admin']);
        /** @var User $user */
        $user = User::factory()->createOne(['role' => 'user']);

        $this->actingAs($admin)
            ->patch('/admin/users/'.$user->id.'/suspend', [
                'reason' => 'Policy violation',
                'duration' => '1w',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'type' => 'admin_notification',
            'title' => 'Account suspended',
        ]);
    }

    public function test_user_can_open_notifications_page_and_mark_notification_as_read(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne();

        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => 'community_interaction',
            'title' => 'Someone commented on your routine',
            'message' => 'Your routine received a new comment.',
        ]);

        $this->actingAs($user)->get('/notifications')
            ->assertOk()
            ->assertSee('Notifications')
            ->assertSee('Someone commented on your routine');

        $this->actingAs($user)
            ->post('/notifications/'.$notification->id.'/read')
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
