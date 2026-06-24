<?php

namespace Tests\Feature;

use App\Models\MoodLog;
use App\Models\Routine;
use App\Models\RoutineLike;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReportingSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_reports_with_mood_trends_and_activity_summary(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne();

        MoodLog::create([
            'user_id' => $user->id,
            'mood_category' => 'happy',
            'mood_value' => 5,
            'journal_note' => 'Great day',
            'logged_at' => now()->subDay(),
        ]);

        MoodLog::create([
            'user_id' => $user->id,
            'mood_category' => 'stressed',
            'mood_value' => 1,
            'journal_note' => 'Stressful day',
            'logged_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/reports/personal');

        $response->assertOk();
        $response->assertSee('Personal Reports');
        $response->assertSee('Mood Report Overview');
        $response->assertSee('Mood Trends');
        $response->assertSee('Emotional Statistics');
        $response->assertSee('Activity Summary');
    }

    public function test_admin_can_view_reports_with_user_and_content_statistics(): void
    {
        /** @var User $admin */
        $admin = User::factory()->createOne(['role' => 'admin']);
        /** @var User $userA */
        $userA = User::factory()->createOne();
        /** @var User $userB */
        $userB = User::factory()->createOne();

        MoodLog::create([
            'user_id' => $userA->id,
            'mood_category' => 'stressed',
            'mood_value' => 1,
            'logged_at' => now(),
        ]);

        MoodLog::create([
            'user_id' => $userB->id,
            'mood_category' => 'stressed',
            'mood_value' => 1,
            'logged_at' => now(),
        ]);

        $routine = Routine::create([
            'user_id' => $userA->id,
            'title' => 'Top Routine',
            'body' => 'Helpful routine body',
            'mood_tag' => 1,
            'status' => 'active',
        ]);

        RoutineLike::create([
            'routine_id' => $routine->id,
            'user_id' => $userB->id,
        ]);

        DB::table('reports')->insert([
            'reporter_id' => $userB->id,
            'reportable_type' => Routine::class,
            'reportable_id' => $routine->id,
            'reason' => 'spam',
            'details' => 'Reported for spam content',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/reports');

        $response->assertOk();
        $response->assertSee('Admin Reports');
        $response->assertSee('User Statistics');
        $response->assertSee('Total Users');
        $response->assertSee('Active Users');
        $response->assertSee('Most Common Moods');
        $response->assertSee('Most Liked Routines');
        $response->assertSee('Reported Content Statistics');
        $response->assertSee('Top Routine');
    }

    public function test_non_admin_cannot_access_admin_reports(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne(['role' => 'user']);

        $this->actingAs($user)
            ->get('/admin/reports')
            ->assertForbidden();

        $this->actingAs($user)
            ->get('/admin/reports/export/csv')
            ->assertForbidden();

        $this->actingAs($user)
            ->get('/admin/reports/export/pdf')
            ->assertForbidden();
    }

    public function test_guest_cannot_access_reports_pages(): void
    {
        $this->get('/reports/personal')->assertRedirect('/login');
        $this->get('/admin/reports')->assertRedirect('/login');
        $this->get('/reports/personal/export/csv')->assertRedirect('/login');
        $this->get('/reports/personal/export/pdf')->assertRedirect('/login');
        $this->get('/admin/reports/export/csv')->assertRedirect('/login');
        $this->get('/admin/reports/export/pdf')->assertRedirect('/login');
    }

    public function test_user_can_export_reports_as_csv_and_pdf(): void
    {
        /** @var User $user */
        $user = User::factory()->createOne();

        MoodLog::create([
            'user_id' => $user->id,
            'mood_category' => 'happy',
            'mood_value' => 5,
            'logged_at' => now(),
        ]);

        $csv = $this->actingAs($user)->get('/reports/personal/export/csv');
        $csv->assertOk();
        $csv->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Total Mood Entries', $csv->streamedContent());

        $pdf = $this->actingAs($user)->get('/reports/personal/export/pdf');
        $pdf->assertOk();
        $pdf->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_can_export_reports_as_csv_and_pdf(): void
    {
        /** @var User $admin */
        $admin = User::factory()->createOne(['role' => 'admin']);
        /** @var User $user */
        $user = User::factory()->createOne();

        MoodLog::create([
            'user_id' => $user->id,
            'mood_category' => 'stressed',
            'mood_value' => 1,
            'logged_at' => now(),
        ]);

        $csv = $this->actingAs($admin)->get('/admin/reports/export/csv');
        $csv->assertOk();
        $csv->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Total Users', $csv->streamedContent());

        $pdf = $this->actingAs($admin)->get('/admin/reports/export/pdf');
        $pdf->assertOk();
        $pdf->assertHeader('content-type', 'application/pdf');
    }

    public function test_user_can_report_inappropriate_routine_content(): void
    {
        /** @var User $reporter */
        $reporter = User::factory()->createOne();
        /** @var User $author */
        $author = User::factory()->createOne();
        /** @var User $admin */
        $admin = User::factory()->createOne(['role' => 'admin']);

        $routine = Routine::create([
            'user_id' => $author->id,
            'title' => 'Suspicious routine',
            'body' => 'Suspicious body',
            'mood_tag' => 2,
            'status' => 'active',
        ]);

        $response = $this->actingAs($reporter)->post('/reports', [
            'reportable_type' => 'routine',
            'reportable_id' => $routine->id,
            'reason' => 'spam',
            'details' => 'Looks like spam content.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $reporter->id,
            'reportable_type' => Routine::class,
            'reportable_id' => $routine->id,
            'reason' => 'spam',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'title' => 'New content report submitted',
        ]);
    }

    public function test_admin_can_remove_reported_content_and_resolve_report(): void
    {
        /** @var User $admin */
        $admin = User::factory()->createOne(['role' => 'admin']);
        /** @var User $reporter */
        $reporter = User::factory()->createOne();
        /** @var User $author */
        $author = User::factory()->createOne();

        $routine = Routine::create([
            'user_id' => $author->id,
            'title' => 'Reported routine',
            'body' => 'Problematic routine',
            'mood_tag' => 1,
            'status' => 'active',
        ]);

        $reportId = DB::table('reports')->insertGetId([
            'reporter_id' => $reporter->id,
            'reportable_type' => Routine::class,
            'reportable_id' => $routine->id,
            'reason' => 'harassment',
            'details' => 'Abusive wording.',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->patch('/admin/reports/'.$reportId.'/moderate', [
            'action' => 'remove',
            'admin_note' => 'Content violates community guidelines.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('reports', [
            'id' => $reportId,
            'status' => 'resolved',
            'resolved_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('routines', [
            'id' => $routine->id,
            'status' => 'removed',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $reporter->id,
            'title' => 'Report status updated',
        ]);
    }
}
