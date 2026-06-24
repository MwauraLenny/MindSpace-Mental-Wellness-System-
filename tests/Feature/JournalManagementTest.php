<?php

namespace Tests\Feature;

use App\Models\Journal;
use App\Models\MoodLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_journal_entries_page(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/journals');

        $response->assertOk();
        $response->assertSee('Journal Entries');
    }

    public function test_user_can_view_single_journal_entry_details(): void
    {
        $user = User::factory()->create();
        $journal = Journal::create([
            'user_id' => $user->id,
            'title' => 'Evening Reflection',
            'body' => 'Today I felt grounded after journaling.',
            'entry_date' => now()->toDateString(),
            'visibility' => 'private',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/journals/'.$journal->id);

        $response->assertOk();
        $response->assertSee('Journal Entry Details');
        $response->assertSee('Evening Reflection');
        $response->assertSee('Today I felt grounded after journaling.');
    }

    public function test_user_can_create_journal_entry_with_related_mood(): void
    {
        $user = User::factory()->create();
        $moodLog = MoodLog::create([
            'user_id' => $user->id,
            'mood_category' => 'happy',
            'mood_value' => 5,
            'journal_note' => 'Feeling bright today.',
        ]);

        $response = $this
            ->actingAs($user)
            ->post('/journals', [
                'title' => 'Morning Reflection',
                'content' => 'I felt optimistic and calm after a good walk.',
                'related_mood_log_id' => $moodLog->id,
            ]);

        $response->assertRedirect(route('journals.index', absolute: false));

        $this->assertDatabaseHas('journals', [
            'user_id' => $user->id,
            'title' => 'Morning Reflection',
            'body' => 'I felt optimistic and calm after a good walk.',
            'mood_log_id' => $moodLog->id,
        ]);
    }

    public function test_user_can_edit_journal_entry(): void
    {
        $user = User::factory()->create();
        $journal = Journal::create([
            'user_id' => $user->id,
            'title' => 'Old Title',
            'body' => 'Old content',
            'entry_date' => now()->toDateString(),
            'visibility' => 'private',
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/journals/'.$journal->id, [
                'title' => 'Updated Title',
                'content' => 'Updated content with emotional detail.',
                'related_mood_log_id' => null,
            ]);

        $response->assertRedirect(route('journals.index', absolute: false));

        $this->assertDatabaseHas('journals', [
            'id' => $journal->id,
            'title' => 'Updated Title',
            'body' => 'Updated content with emotional detail.',
        ]);
    }

    public function test_user_can_delete_journal_entry(): void
    {
        $user = User::factory()->create();
        $journal = Journal::create([
            'user_id' => $user->id,
            'title' => 'Delete Me',
            'body' => 'To be deleted',
            'entry_date' => now()->toDateString(),
            'visibility' => 'private',
        ]);

        $response = $this
            ->actingAs($user)
            ->delete('/journals/'.$journal->id);

        $response->assertRedirect(route('journals.index', absolute: false));
        $this->assertDatabaseMissing('journals', ['id' => $journal->id]);
    }

    public function test_user_cannot_edit_or_delete_another_users_journal(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $journal = Journal::create([
            'user_id' => $owner->id,
            'title' => 'Private Entry',
            'body' => 'Private body',
            'entry_date' => now()->toDateString(),
            'visibility' => 'private',
        ]);

        $this->actingAs($intruder)
            ->get('/journals/'.$journal->id)
            ->assertNotFound();

        $this->actingAs($intruder)
            ->get('/journals/'.$journal->id.'/edit')
            ->assertNotFound();

        $this->actingAs($intruder)
            ->patch('/journals/'.$journal->id, [
                'title' => 'Hack',
                'content' => 'Hack content',
                'related_mood_log_id' => null,
            ])->assertNotFound();

        $this->actingAs($intruder)
            ->delete('/journals/'.$journal->id)
            ->assertNotFound();
    }

    public function test_guest_cannot_access_journal_routes(): void
    {
        $this->get('/journals')->assertRedirect('/login');
        $this->get('/journals/create')->assertRedirect('/login');
        $this->post('/journals', [])->assertRedirect('/login');
    }
}
