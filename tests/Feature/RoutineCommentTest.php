<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Routine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutineCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_comment_to_routine(): void
    {
        /** @var User $owner */
        $owner = User::factory()->createOne();
        /** @var User $user */
        $user = User::factory()->createOne();

        $routine = Routine::create([
            'user_id' => $owner->id,
            'title' => 'Meditation Break',
            'body' => 'Take 10 deep breaths and stretch.',
            'mood_tag' => 3,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post('/routines/'.$routine->id.'/comments', [
            'body' => 'This really helped me reset after classes.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'user_id' => $user->id,
            'commentable_type' => Routine::class,
            'commentable_id' => $routine->id,
            'body' => 'This really helped me reset after classes.',
            'status' => 'active',
        ]);
    }

    public function test_user_can_delete_own_comment(): void
    {
        /** @var User $owner */
        $owner = User::factory()->createOne();
        /** @var User $user */
        $user = User::factory()->createOne();

        $routine = Routine::create([
            'user_id' => $owner->id,
            'title' => 'Focus Ritual',
            'body' => 'Write top 3 tasks and breathe for 2 minutes.',
            'mood_tag' => 4,
            'status' => 'active',
        ]);

        $comment = Comment::create([
            'user_id' => $user->id,
            'commentable_type' => Routine::class,
            'commentable_id' => $routine->id,
            'body' => 'I use this before study sessions.',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->delete('/routines/'.$routine->id.'/comments/'.$comment->id);

        $response->assertRedirect();
        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
    }

    public function test_user_can_reply_to_a_top_level_comment(): void
    {
        /** @var User $owner */
        $owner = User::factory()->createOne();
        /** @var User $author */
        $author = User::factory()->createOne();
        /** @var User $replier */
        $replier = User::factory()->createOne();

        $routine = Routine::create([
            'user_id' => $owner->id,
            'title' => 'Morning Reset',
            'body' => 'Short breathing and hydration routine.',
            'mood_tag' => 3,
            'status' => 'active',
        ]);

        $comment = Comment::create([
            'user_id' => $author->id,
            'commentable_type' => Routine::class,
            'commentable_id' => $routine->id,
            'body' => 'This helps me before lectures.',
            'status' => 'active',
        ]);

        $response = $this->actingAs($replier)->post('/routines/'.$routine->id.'/comments', [
            'parent_id' => $comment->id,
            'body' => 'Same here, especially on busy days.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'user_id' => $replier->id,
            'commentable_type' => Routine::class,
            'commentable_id' => $routine->id,
            'parent_id' => $comment->id,
            'body' => 'Same here, especially on busy days.',
        ]);
    }

    public function test_user_cannot_reply_using_comment_from_another_routine(): void
    {
        /** @var User $owner */
        $owner = User::factory()->createOne();
        /** @var User $user */
        $user = User::factory()->createOne();

        $routineA = Routine::create([
            'user_id' => $owner->id,
            'title' => 'Routine A',
            'body' => 'Routine A body',
            'mood_tag' => 3,
            'status' => 'active',
        ]);

        $routineB = Routine::create([
            'user_id' => $owner->id,
            'title' => 'Routine B',
            'body' => 'Routine B body',
            'mood_tag' => 4,
            'status' => 'active',
        ]);

        $commentOnB = Comment::create([
            'user_id' => $owner->id,
            'commentable_type' => Routine::class,
            'commentable_id' => $routineB->id,
            'body' => 'Comment on B',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->from('/routines')->post('/routines/'.$routineA->id.'/comments', [
            'parent_id' => $commentOnB->id,
            'body' => 'Invalid cross-thread reply',
        ]);

        $response->assertRedirect('/routines');
        $response->assertSessionHasErrors('parent_id');

        $this->assertDatabaseMissing('comments', [
            'commentable_id' => $routineA->id,
            'body' => 'Invalid cross-thread reply',
        ]);
    }

    public function test_user_cannot_delete_other_users_comment(): void
    {
        /** @var User $owner */
        $owner = User::factory()->createOne();
        /** @var User $author */
        $author = User::factory()->createOne();
        /** @var User $intruder */
        $intruder = User::factory()->createOne();

        $routine = Routine::create([
            'user_id' => $owner->id,
            'title' => 'Evening Wind Down',
            'body' => 'Warm tea and low-light reading for 20 minutes.',
            'mood_tag' => 2,
            'status' => 'active',
        ]);

        $comment = Comment::create([
            'user_id' => $author->id,
            'commentable_type' => Routine::class,
            'commentable_id' => $routine->id,
            'body' => 'This is my favorite night routine.',
            'status' => 'active',
        ]);

        $this->actingAs($intruder)
            ->delete('/routines/'.$routine->id.'/comments/'.$comment->id)
            ->assertForbidden();

        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    public function test_guest_cannot_comment_or_delete_comment(): void
    {
        /** @var User $owner */
        $owner = User::factory()->createOne();

        $routine = Routine::create([
            'user_id' => $owner->id,
            'title' => 'Study Sprint',
            'body' => '25-minute focus with 5-minute breaks.',
            'mood_tag' => 3,
            'status' => 'active',
        ]);

        $comment = Comment::create([
            'user_id' => $owner->id,
            'commentable_type' => Routine::class,
            'commentable_id' => $routine->id,
            'body' => 'Works for me every week.',
            'status' => 'active',
        ]);

        $this->post('/routines/'.$routine->id.'/comments', [
            'body' => 'Guest message',
        ])->assertRedirect('/login');

        $this->delete('/routines/'.$routine->id.'/comments/'.$comment->id)
            ->assertRedirect('/login');

        $this->post('/routines/'.$routine->id.'/comments/'.$comment->id.'/react', [
            'reaction' => 'helpful',
        ])->assertRedirect('/login');
    }

    public function test_user_can_toggle_comment_reaction_by_clicking_same_reaction_twice(): void
    {
        /** @var User $owner */
        $owner = User::factory()->createOne();
        /** @var User $reactor */
        $reactor = User::factory()->createOne();

        $routine = Routine::create([
            'user_id' => $owner->id,
            'title' => 'Routine for reaction toggle',
            'body' => 'Routine body.',
            'mood_tag' => 3,
            'status' => 'active',
        ]);

        $comment = Comment::create([
            'user_id' => $owner->id,
            'commentable_type' => Routine::class,
            'commentable_id' => $routine->id,
            'body' => 'Top-level comment',
            'status' => 'active',
        ]);

        $this->actingAs($reactor)
            ->post('/routines/'.$routine->id.'/comments/'.$comment->id.'/react', [
                'reaction' => 'helpful',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('reactions', [
            'user_id' => $reactor->id,
            'reactable_type' => Comment::class,
            'reactable_id' => $comment->id,
            'reaction' => 'helpful',
        ]);

        $this->actingAs($reactor)
            ->post('/routines/'.$routine->id.'/comments/'.$comment->id.'/react', [
                'reaction' => 'helpful',
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('reactions', [
            'user_id' => $reactor->id,
            'reactable_type' => Comment::class,
            'reactable_id' => $comment->id,
            'reaction' => 'helpful',
        ]);
    }

    public function test_user_can_toggle_reply_reaction_by_clicking_same_reaction_twice(): void
    {
        /** @var User $owner */
        $owner = User::factory()->createOne();
        /** @var User $reactor */
        $reactor = User::factory()->createOne();

        $routine = Routine::create([
            'user_id' => $owner->id,
            'title' => 'Routine for reply reaction toggle',
            'body' => 'Routine body.',
            'mood_tag' => 4,
            'status' => 'active',
        ]);

        $comment = Comment::create([
            'user_id' => $owner->id,
            'commentable_type' => Routine::class,
            'commentable_id' => $routine->id,
            'body' => 'Parent comment',
            'status' => 'active',
        ]);

        $reply = Comment::create([
            'user_id' => $owner->id,
            'commentable_type' => Routine::class,
            'commentable_id' => $routine->id,
            'parent_id' => $comment->id,
            'body' => 'Reply comment',
            'status' => 'active',
        ]);

        $this->actingAs($reactor)
            ->post('/routines/'.$routine->id.'/comments/'.$reply->id.'/react', [
                'reaction' => 'inspiring',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('reactions', [
            'user_id' => $reactor->id,
            'reactable_type' => Comment::class,
            'reactable_id' => $reply->id,
            'reaction' => 'inspiring',
        ]);

        $this->actingAs($reactor)
            ->post('/routines/'.$routine->id.'/comments/'.$reply->id.'/react', [
                'reaction' => 'inspiring',
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('reactions', [
            'user_id' => $reactor->id,
            'reactable_type' => Comment::class,
            'reactable_id' => $reply->id,
            'reaction' => 'inspiring',
        ]);
    }

    public function test_user_can_comment_anonymously(): void
    {
        /** @var User $owner */
        $owner = User::factory()->createOne();
        /** @var User $user */
        $user = User::factory()->createOne();

        $routine = Routine::create([
            'user_id' => $owner->id,
            'title' => 'Anonymous Comment Routine',
            'body' => 'Routine body.',
            'mood_tag' => 3,
            'status' => 'active',
        ]);

        $this->actingAs($user)->post('/routines/'.$routine->id.'/comments', [
            'body' => 'Posting this anonymously.',
            'is_anonymous' => true,
        ])->assertRedirect();

        $comment = Comment::query()->where('commentable_id', $routine->id)
            ->where('body', 'Posting this anonymously.')
            ->firstOrFail();

        $this->assertTrue((bool) $comment->is_anonymous);

        $feed = $this->actingAs($owner)->get('/community');
        $feed->assertSee('Anonymous community member');
        $feed->assertDontSee($user->name);
    }

    public function test_user_can_reply_anonymously(): void
    {
        /** @var User $owner */
        $owner = User::factory()->createOne();
        /** @var User $author */
        $author = User::factory()->createOne();
        /** @var User $replier */
        $replier = User::factory()->createOne();

        $routine = Routine::create([
            'user_id' => $owner->id,
            'title' => 'Anonymous Reply Routine',
            'body' => 'Routine body.',
            'mood_tag' => 2,
            'status' => 'active',
        ]);

        $parent = Comment::create([
            'user_id' => $author->id,
            'commentable_type' => Routine::class,
            'commentable_id' => $routine->id,
            'body' => 'Top comment',
            'status' => 'active',
        ]);

        $this->actingAs($replier)->post('/routines/'.$routine->id.'/comments', [
            'parent_id' => $parent->id,
            'body' => 'Anonymous reply text',
            'is_anonymous' => true,
        ])->assertRedirect();

        $reply = Comment::query()->where('parent_id', $parent->id)
            ->where('body', 'Anonymous reply text')
            ->firstOrFail();

        $this->assertTrue((bool) $reply->is_anonymous);

        $feed = $this->actingAs($owner)->get('/community');
        $feed->assertSee('Anonymous reply text');
        $feed->assertSee('Anonymous community member');
        $feed->assertDontSee($replier->name);
    }
}
