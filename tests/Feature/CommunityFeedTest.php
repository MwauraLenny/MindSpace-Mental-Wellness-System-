<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\MoodLog;
use App\Models\Routine;
use App\Models\RoutineLike;
use App\Models\RoutineReaction;
use App\Models\SavedRoutine;
use App\Models\RoutineCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_community_feed_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/community');

        $response->assertOk();
        $response->assertSee('Community Feed');
    }

    public function test_feed_shows_all_shared_routines_by_default(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();

        MoodLog::create([
            'user_id' => $user->id,
            'mood_category' => 'stressed',
            'mood_value' => 1,
        ]);

        Routine::create([
            'user_id' => $owner->id,
            'title' => 'Low Mood Routine',
            'body' => 'Breathing and journaling.',
            'mood_tag' => 1,
            'status' => 'active',
        ]);

        Routine::create([
            'user_id' => $owner->id,
            'title' => 'High Mood Routine',
            'body' => 'Social walk and playlist.',
            'mood_tag' => 5,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/community?view=community&mood_scope=all');

        $response->assertOk();
        $response->assertSee('Low Mood Routine');
        $response->assertSee('High Mood Routine');
    }

    public function test_feed_can_filter_to_match_latest_mood(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();

        MoodLog::create([
            'user_id' => $user->id,
            'mood_category' => 'stressed',
            'mood_value' => 1,
        ]);

        Routine::create([
            'user_id' => $owner->id,
            'title' => 'Match Mood Routine',
            'body' => 'Grounding walk.',
            'mood_tag' => 1,
            'status' => 'active',
        ]);

        Routine::create([
            'user_id' => $owner->id,
            'title' => 'Different Mood Routine',
            'body' => 'Celebration activity.',
            'mood_tag' => 5,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/community?view=community&mood_scope=match');

        $response->assertOk();
        $response->assertSee('Match Mood Routine');
        $response->assertDontSee('Different Mood Routine');
    }

    public function test_feed_shows_anonymous_posts_and_engagement_counts(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();

        $routine = Routine::create([
            'user_id' => $owner->id,
            'title' => 'Anonymous Routine',
            'body' => 'Anonymous post body.',
            'mood_tag' => 3,
            'is_anonymous' => true,
            'status' => 'active',
        ]);

        RoutineLike::create([
            'routine_id' => $routine->id,
            'user_id' => $viewer->id,
        ]);

        SavedRoutine::create([
            'routine_id' => $routine->id,
            'user_id' => $viewer->id,
        ]);

        RoutineReaction::create([
            'routine_id' => $routine->id,
            'user_id' => $viewer->id,
            'reaction' => 'heart',
        ]);

        Comment::create([
            'user_id' => $viewer->id,
            'commentable_type' => Routine::class,
            'commentable_id' => $routine->id,
            'body' => 'Helpful post',
            'status' => 'active',
        ]);

        $response = $this->actingAs($viewer)->get('/community');

        $response->assertOk();
        $response->assertSee('Anonymous user');
        $response->assertSee('total engagement');
    }

    public function test_feed_displays_comments_and_replies(): void
    {
        $owner = User::factory()->create();
        $commenter = User::factory()->create();
        $replier = User::factory()->create();

        $routine = Routine::create([
            'user_id' => $owner->id,
            'title' => 'Thread Routine',
            'body' => 'Routine body for thread testing.',
            'mood_tag' => 4,
            'status' => 'active',
        ]);

        $comment = Comment::create([
            'user_id' => $commenter->id,
            'commentable_type' => Routine::class,
            'commentable_id' => $routine->id,
            'body' => 'Top level comment text',
            'status' => 'active',
        ]);

        Comment::create([
            'user_id' => $replier->id,
            'commentable_type' => Routine::class,
            'commentable_id' => $routine->id,
            'parent_id' => $comment->id,
            'body' => 'Reply comment text',
            'status' => 'active',
        ]);

        $response = $this->actingAs($owner)->get('/community');

        $response->assertOk();
        $response->assertSee('Top level comment text');
        $response->assertSee('Reply comment text');
    }

    public function test_guest_cannot_access_community_feed_page(): void
    {
        $this->get('/community')->assertRedirect('/login');
    }

    public function test_feed_displays_mood_based_and_similar_user_recommendations(): void
    {
        $user = User::factory()->create();
        $similarUser = User::factory()->create();
        $differentUser = User::factory()->create();

        MoodLog::create([
            'user_id' => $user->id,
            'mood_category' => 'stressed',
            'mood_value' => 1,
            'logged_at' => now(),
        ]);

        MoodLog::create([
            'user_id' => $similarUser->id,
            'mood_category' => 'stressed',
            'mood_value' => 1,
            'logged_at' => now()->subMinutes(10),
        ]);

        Routine::create([
            'user_id' => $similarUser->id,
            'title' => 'Similar User Exercise Routine',
            'body' => 'Walk quickly for 15 minutes and breathe deeply.',
            'mood_tag' => 1,
            'status' => 'active',
        ]);

        Routine::create([
            'user_id' => $differentUser->id,
            'title' => 'Different Mood Routine',
            'body' => 'Celebrate with a party playlist.',
            'mood_tag' => 5,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/community');

        $response->assertOk();
        $response->assertSee('Personalized recommendations');
        $response->assertSee('Stressed');
        $response->assertSee('Similar User Exercise Routine');
        $response->assertSee('Mood match from users with similar mood history');
        $response->assertSee('Exercise routine: 15-minute brisk walk with deep breathing.');
        $response->assertSee('Study routine: 25-minute focus sprint followed by a 5-minute reset.');
        $response->assertSee('Meditation routine: 5 minutes of box breathing (4-4-4-4).');
        $response->assertSee('Why this was recommended');
        $response->assertSee('Start now');
    }

    public function test_feed_displays_personalized_category_recommendation(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();

        $category = RoutineCategory::create([
            'name' => 'Meditation Routines',
            'slug' => 'meditation-routines',
            'description' => 'Mindfulness and breathing',
            'is_active' => true,
        ]);

        $likedRoutine = Routine::create([
            'user_id' => $owner->id,
            'title' => 'Evening Calm Meditation',
            'body' => 'Breathe in for 4, out for 6.',
            'mood_tag' => 3,
            'routine_category_id' => $category->id,
            'status' => 'active',
        ]);

        RoutineLike::create([
            'routine_id' => $likedRoutine->id,
            'user_id' => $user->id,
        ]);

        $personalizedRoutine = Routine::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Meditation Focus Reset',
            'body' => '10-minute mindful reset before study.',
            'mood_tag' => 4,
            'routine_category_id' => $category->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/community');

        $response->assertOk();
        $response->assertSee($personalizedRoutine->title);
        $response->assertSee('Personalized from your engagement patterns');
    }
}
