<?php

namespace Tests\Feature;

use App\Models\Routine;
use App\Models\RoutineCategory;
use App\Models\RoutineLike;
use App\Models\RoutineReaction;
use App\Models\SavedRoutine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutineCommunityTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_and_share_routine(): void
    {
        $user = User::factory()->create();
        $category = RoutineCategory::create([
            'name' => 'Exercise Routines',
            'slug' => 'exercise-routines',
            'description' => 'Exercise habits',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/routines', [
            'title' => 'Morning Stretch Flow',
            'body' => '10 minutes stretching + 10 minutes walking.',
            'mood_tag' => 4,
            'routine_category_id' => $category->id,
            'is_anonymous' => false,
        ]);

        $response->assertRedirect(route('routines.index', absolute: false));

        $this->assertDatabaseHas('routines', [
            'user_id' => $user->id,
            'title' => 'Morning Stretch Flow',
            'routine_category_id' => $category->id,
            'status' => 'active',
        ]);
    }

    public function test_user_can_like_and_unlike_routine(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $routine = Routine::create([
            'user_id' => $owner->id,
            'title' => 'Pomodoro Study Block',
            'body' => 'Study 25 mins, break 5 mins.',
            'mood_tag' => 3,
            'status' => 'active',
        ]);

        $this->actingAs($user)->post('/routines/'.$routine->id.'/upvote')->assertRedirect();
        $this->assertDatabaseHas('routine_likes', [
            'routine_id' => $routine->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)->post('/routines/'.$routine->id.'/upvote')->assertRedirect();
        $this->assertDatabaseMissing('routine_likes', [
            'routine_id' => $routine->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_save_and_unsave_routine(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $routine = Routine::create([
            'user_id' => $owner->id,
            'title' => 'Night Relaxation',
            'body' => 'Breathing plus calming music for 15 minutes.',
            'mood_tag' => 2,
            'status' => 'active',
        ]);

        $this->actingAs($user)->post('/routines/'.$routine->id.'/save')->assertRedirect();
        $this->assertDatabaseHas('saved_routines', [
            'routine_id' => $routine->id,
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)->post('/routines/'.$routine->id.'/save')->assertRedirect();
        $this->assertDatabaseMissing('saved_routines', [
            'routine_id' => $routine->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_can_react_to_routine(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $routine = Routine::create([
            'user_id' => $owner->id,
            'title' => 'Social Recharge',
            'body' => 'Call a close friend and take a short walk together.',
            'mood_tag' => 4,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post('/routines/'.$routine->id.'/react', [
            'reaction' => 'heart',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('routine_reactions', [
            'routine_id' => $routine->id,
            'user_id' => $user->id,
            'reaction' => 'heart',
        ]);
    }

    public function test_user_can_browse_saved_routines_view(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();
        $routine = Routine::create([
            'user_id' => $owner->id,
            'title' => 'Music Reset',
            'body' => 'Listen to a calming instrumental playlist.',
            'mood_tag' => 5,
            'status' => 'active',
        ]);

        SavedRoutine::create([
            'routine_id' => $routine->id,
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get('/routines?view=saved');

        $response->assertOk();
        $response->assertSee('Music Reset');
    }

    public function test_guest_cannot_access_routine_interaction_routes(): void
    {
        $this->post('/routines/1/upvote')->assertRedirect('/login');
        $this->post('/routines/1/save')->assertRedirect('/login');
        $this->post('/routines/1/react', ['reaction' => 'heart'])->assertRedirect('/login');
    }
}
