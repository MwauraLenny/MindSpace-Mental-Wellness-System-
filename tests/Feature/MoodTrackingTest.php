<?php

namespace Tests\Feature;

use App\Models\MoodLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MoodTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_mood_logging_page(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/mood');

        $response->assertOk();
        $response->assertSee('Log Your Mood');
        $response->assertSee('Happy');
        $response->assertSee('Anxious');
    }

    public function test_user_can_submit_mood_with_emoji_category(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/mood', [
                'mood_category' => 'excited',
                'journal_note' => 'I had a really productive day.',
            ]);

        $response->assertRedirect(route('mood.index', absolute: false));

        $this->assertDatabaseHas('mood_logs', [
            'user_id' => $user->id,
            'mood_category' => 'excited',
            'mood_value' => 5,
            'journal_note' => 'I had a really productive day.',
        ]);
    }

    public function test_user_can_view_mood_history(): void
    {
        $user = User::factory()->create();

        MoodLog::create([
            'user_id' => $user->id,
            'mood_category' => 'stressed',
            'mood_value' => 1,
            'journal_note' => 'A lot of deadlines today.',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/mood');

        $response->assertOk();
        $response->assertSee('Stressed');
        $response->assertSee('A lot of deadlines today.');
    }

    public function test_user_can_view_mood_dashboard_with_analytics(): void
    {
        $user = User::factory()->create();

        MoodLog::create([
            'user_id' => $user->id,
            'mood_category' => 'happy',
            'mood_value' => 5,
            'journal_note' => 'Felt great after morning walk.',
        ]);

        MoodLog::create([
            'user_id' => $user->id,
            'mood_category' => 'anxious',
            'mood_value' => 1,
            'journal_note' => 'Worried about exams.',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/mood/dashboard');

        $response->assertOk();
        $response->assertSee('Mood Dashboard');
        $response->assertSee('Emotional Summary');
        $response->assertSee('Behavior Pattern Analysis');
    }

    public function test_user_can_filter_dashboard_by_last_7_days(): void
    {
        Carbon::setTestNow('2026-06-24 12:00:00');

        $user = User::factory()->create();

        MoodLog::create([
            'user_id' => $user->id,
            'mood_category' => 'happy',
            'mood_value' => 5,
            'journal_note' => 'Recent entry',
            'logged_at' => Carbon::now()->subDays(2),
        ]);

        MoodLog::create([
            'user_id' => $user->id,
            'mood_category' => 'sad',
            'mood_value' => 2,
            'journal_note' => 'Old entry',
            'logged_at' => Carbon::now()->subDays(20),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/mood/dashboard?period=7d');

        $response->assertOk();
        $response->assertSee('Recent entry');
        $response->assertDontSee('Old entry');

        Carbon::setTestNow();
    }

    public function test_user_can_export_mood_history_as_csv(): void
    {
        $user = User::factory()->create();

        MoodLog::create([
            'user_id' => $user->id,
            'mood_category' => 'relaxed',
            'mood_value' => 4,
            'journal_note' => 'CSV export row',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/mood/export/csv?period=all');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csvContent = $response->streamedContent();
        $this->assertStringContainsString('Mood Category', $csvContent);
        $this->assertStringContainsString('CSV export row', $csvContent);
    }

    public function test_user_can_export_mood_history_as_pdf(): void
    {
        $user = User::factory()->create();

        MoodLog::create([
            'user_id' => $user->id,
            'mood_category' => 'excited',
            'mood_value' => 5,
            'journal_note' => 'PDF export row',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/mood/export/pdf?period=all');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_guest_cannot_access_mood_routes(): void
    {
        $this->get('/mood')->assertRedirect('/login');
        $this->post('/mood', [
            'mood_category' => 'happy',
            'journal_note' => 'Test',
        ])->assertRedirect('/login');
        $this->get('/mood/dashboard')->assertRedirect('/login');
        $this->get('/mood/export/csv')->assertRedirect('/login');
        $this->get('/mood/export/pdf')->assertRedirect('/login');
    }
}
