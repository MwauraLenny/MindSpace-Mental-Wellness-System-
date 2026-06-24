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

    public function test_student_can_view_reports_with_mood_trends_and_activity_summary(): void
    {
        $user = User::factory()->create();

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

        $response = $this->actingAs($user)->get('/reports/student');

        $response->assertOk();
        $response->assertSee('Student Reports');
        $response->assertSee('Mood Report Overview');
        $response->assertSee('Mood Trends');
        $response->assertSee('Emotional Statistics');
        $response->assertSee('Activity Summary');
    }

    public function test_admin_can_view_reports_with_user_and_content_statistics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $userA = User::factory()->create();
        $userB = User::factory()->create();

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
        $user = User::factory()->create(['role' => 'user']);

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
        $this->get('/reports/student')->assertRedirect('/login');
        $this->get('/admin/reports')->assertRedirect('/login');
        $this->get('/reports/student/export/csv')->assertRedirect('/login');
        $this->get('/reports/student/export/pdf')->assertRedirect('/login');
        $this->get('/admin/reports/export/csv')->assertRedirect('/login');
        $this->get('/admin/reports/export/pdf')->assertRedirect('/login');
    }

    public function test_student_can_export_reports_as_csv_and_pdf(): void
    {
        $user = User::factory()->create();

        MoodLog::create([
            'user_id' => $user->id,
            'mood_category' => 'happy',
            'mood_value' => 5,
            'logged_at' => now(),
        ]);

        $csv = $this->actingAs($user)->get('/reports/student/export/csv');
        $csv->assertOk();
        $csv->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Total Mood Entries', $csv->streamedContent());

        $pdf = $this->actingAs($user)->get('/reports/student/export/pdf');
        $pdf->assertOk();
        $pdf->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_can_export_reports_as_csv_and_pdf(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();

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
}
