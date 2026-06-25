<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Journal;
use App\Models\MoodLog;
use App\Models\AuditLog;
use App\Models\Report;
use App\Models\Routine;
use App\Models\RoutineLike;
use App\Models\RoutineReaction;
use App\Models\SavedRoutine;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reportable_type' => 'required|string|in:routine,comment',
            'reportable_id' => 'required|integer|min:1',
            'reason' => 'required|string|in:'.implode(',', Report::REASON_OPTIONS),
            'details' => 'nullable|string|max:1000',
        ]);

        $reportableClass = $validated['reportable_type'] === 'routine' ? Routine::class : Comment::class;

        $reportable = $validated['reportable_type'] === 'routine'
            ? Routine::query()
                ->where('id', (int) $validated['reportable_id'])
                ->where('status', 'active')
                ->first()
            : Comment::query()
                ->where('id', (int) $validated['reportable_id'])
                ->where('status', 'active')
                ->first();

        if (! $reportable) {
            return back()->with('error', 'The selected content could not be reported because it is unavailable.');
        }

        $ownerId = (int) ($reportable->user_id ?? 0);

        if ($ownerId === (int) Auth::id()) {
            return back()->with('error', 'You cannot report your own content.');
        }

        $existingPendingReport = Report::query()
            ->where('reporter_id', Auth::id())
            ->where('reportable_type', $reportableClass)
            ->where('reportable_id', (int) $validated['reportable_id'])
            ->where('status', Report::STATUS_PENDING)
            ->first();

        if ($existingPendingReport) {
            return back()->with('success', 'This content has already been reported and is awaiting moderation.');
        }

        $report = Report::create([
            'reporter_id' => Auth::id(),
            'reportable_type' => $reportableClass,
            'reportable_id' => (int) $validated['reportable_id'],
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => Report::STATUS_PENDING,
        ]);

        /** @var NotificationService $notificationService */
        $notificationService = app(NotificationService::class);
        $notificationService->notifyAdmins(
            'admin_notification',
            'New content report submitted',
            'A new community content report is waiting for moderation review.',
            [
                'report_id' => $report->id,
                'reportable_type' => $reportableClass,
                'reportable_id' => (int) $validated['reportable_id'],
                'reason' => $validated['reason'],
            ]
        );

        if ($ownerId > 0) {
            $notificationService->createForUser(
                $ownerId,
                'admin_notification',
                'Content warning notice',
                'One of your posts was flagged by another community member and is awaiting moderator review.',
                [
                    'report_id' => $report->id,
                    'report_reason' => $validated['reason'],
                ]
            );
        }

        return back()->with('success', 'Thanks for reporting. Our moderation team will review this content.');
    }

    public function personal(): View
    {
        return view('reports.personal', $this->buildPersonalReportData(Auth::id()));
    }

    public function personalExportCsv(): StreamedResponse
    {
        $data = $this->buildPersonalReportData(Auth::id());
        $filename = 'user-report-'.Carbon::now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($data): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['Metric', 'Value']);
            fputcsv($handle, ['Total Mood Entries', $data['totalEntries']]);
            fputcsv($handle, ['Average Mood Score', $data['averageScore']]);
            fputcsv($handle, ['Positive Mood Rate', $data['positiveRate'].'%']);
            fputcsv($handle, ['Most Common Mood', $data['mostFrequentMood']]);
            fputcsv($handle, ['Trend Summary', $data['trendSummary']]);

            fputcsv($handle, []);
            fputcsv($handle, ['Mood Category', 'Count']);

            foreach ($data['moodCounts'] as $key => $count) {
                fputcsv($handle, [
                    $data['categories'][$key]['label'] ?? $key,
                    $count,
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Activity', 'Count']);

            foreach ($data['activitySummary'] as $metric => $count) {
                fputcsv($handle, [str_replace('_', ' ', $metric), $count]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function personalExportPdf(): Response
    {
        $data = $this->buildPersonalReportData(Auth::id());

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('reports.personal-export-pdf', array_merge($data, [
            'generatedAt' => Carbon::now(),
        ]));

        return $pdf->download('user-report-'.Carbon::now()->format('Ymd_His').'.pdf');
    }

    public function admin(): View
    {
        return view('reports.admin', $this->buildAdminReportData());
    }

    public function moderate(Request $request, Report $report): RedirectResponse
    {
        $validated = $request->validate([
            'action' => 'required|string|in:resolve,dismiss,remove,warn',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $wasContentRemoved = false;

        if ($validated['action'] === 'remove') {
            $wasContentRemoved = $this->removeReportedContent($report);
        }

        $report->status = $validated['action'] === 'dismiss'
            ? Report::STATUS_DISMISSED
            : Report::STATUS_RESOLVED;
        $report->resolved_by = Auth::id();
        $report->resolved_at = now();

        if (! empty($validated['admin_note'])) {
            $existingDetails = trim((string) $report->details);
            $adminNotePrefix = 'Admin note: '.$validated['admin_note'];
            $report->details = $existingDetails === ''
                ? $adminNotePrefix
                : $existingDetails."\n\n".$adminNotePrefix;
        }

        if ($validated['action'] === 'remove' && ! $wasContentRemoved) {
            $existingDetails = trim((string) $report->details);
            $removeNote = 'Admin note: Reported content was already unavailable when moderation was applied.';
            $report->details = $existingDetails === ''
                ? $removeNote
                : $existingDetails."\n\n".$removeNote;
        }

        $report->save();

        AuditLog::query()->create([
            'actor_id' => Auth::id(),
            'action' => 'admin.report.moderated',
            'target_type' => Report::class,
            'target_id' => (int) $report->id,
            'meta' => [
                'moderation_action' => $validated['action'],
                'result_status' => $report->status,
            ],
            'performed_at' => now(),
        ]);

        /** @var NotificationService $notificationService */
        $notificationService = app(NotificationService::class);
        $notificationService->createForUser(
            (int) $report->reporter_id,
            'community_interaction',
            'Report status updated',
            $validated['action'] === 'dismiss'
                ? 'A report you submitted was reviewed and dismissed.'
                : 'A report you submitted was reviewed and resolved by moderation.',
            [
                'report_id' => $report->id,
                'status' => $report->status,
                'action' => $validated['action'],
            ]
        );

        $targetOwnerId = (int) optional($report->reportable)->user_id;

        if ($validated['action'] === 'warn' && $targetOwnerId > 0) {
            $notificationService->createForUser(
                $targetOwnerId,
                'admin_notification',
                'Moderator warning on your content',
                'A moderator issued a warning on your flagged content. Please review community guidelines.',
                [
                    'report_id' => $report->id,
                    'admin_note' => $validated['admin_note'] ?? null,
                ]
            );
        }

        return back()->with('success', 'Moderation action saved successfully.');
    }

    public function adminExportCsv(): StreamedResponse
    {
        $data = $this->buildAdminReportData();
        $filename = 'admin-report-'.Carbon::now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($data): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['Metric', 'Value']);
            fputcsv($handle, ['Total Users', $data['totalUsers']]);
            fputcsv($handle, ['Active Users (30d)', $data['activeUsers']]);
            fputcsv($handle, ['Total Reports', $data['reportStats']['total']]);
            fputcsv($handle, ['Pending Reports', $data['reportStats']['pending']]);
            fputcsv($handle, ['Resolved Reports', $data['reportStats']['resolved']]);
            fputcsv($handle, ['Dismissed Reports', $data['reportStats']['dismissed']]);

            fputcsv($handle, []);
            fputcsv($handle, ['Most Common Moods', 'Count']);

            foreach ($data['mostCommonMoods'] as $mood) {
                fputcsv($handle, [$mood['label'], $mood['total']]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Most Liked Routines', 'Likes']);

            foreach ($data['mostLikedRoutines'] as $routine) {
                fputcsv($handle, [$routine->display_title, $routine->likes_count]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function adminExportPdf(): Response
    {
        $data = $this->buildAdminReportData();

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('reports.admin-export-pdf', array_merge($data, [
            'generatedAt' => Carbon::now(),
        ]));

        return $pdf->download('admin-report-'.Carbon::now()->format('Ymd_His').'.pdf');
    }

    private function buildPersonalReportData(int $userId): array
    {
        $moodLogs = MoodLog::query()
            ->where('user_id', $userId)
            ->orderByDesc('logged_at')
            ->get();

        $totalEntries = $moodLogs->count();
        $averageScore = $totalEntries > 0 ? round($moodLogs->avg('mood_value'), 2) : 0;
        $positiveRate = $totalEntries > 0
            ? round(($moodLogs->where('mood_value', '>=', 4)->count() / $totalEntries) * 100, 1)
            : 0;

        $categories = MoodLog::categories();
        $moodCounts = collect(array_keys($categories))
            ->mapWithKeys(fn ($key) => [$key => $moodLogs->where('mood_category_key', $key)->count()]);

        $mostFrequentMoodKey = $moodCounts->sortDesc()->keys()->first();
        $mostFrequentMood = $mostFrequentMoodKey
            ? $categories[$mostFrequentMoodKey]['label']
            : 'No entries yet';

        $recent14 = $moodLogs->sortBy('logged_at')->take(14)->values();
        $currentWindowAverage = $recent14->take(-7)->avg('mood_value');
        $previousWindowAverage = $recent14->take(7)->avg('mood_value');

        $trendSummary = 'Log more moods to generate trend insights.';

        if ($recent14->count() >= 7 && $currentWindowAverage !== null && $previousWindowAverage !== null) {
            if ($currentWindowAverage > $previousWindowAverage) {
                $trendSummary = 'Your emotional trend is improving compared to the prior week.';
            } elseif ($currentWindowAverage < $previousWindowAverage) {
                $trendSummary = 'Your emotional trend has declined compared to the prior week.';
            } else {
                $trendSummary = 'Your emotional trend is stable over the last two weeks.';
            }
        }

        $dailyMoodTrend = $moodLogs
            ->groupBy(fn ($log) => optional($log->logged_at)->format('Y-m-d'))
            ->map(fn ($logs) => round($logs->avg('mood_value'), 2))
            ->filter(fn ($value, $key) => $key !== null)
            ->sortKeys()
            ->take(-14);

        $activitySummary = [
            'mood_logs_total' => $totalEntries,
            'journals_total' => Journal::query()->where('user_id', $userId)->count(),
            'routines_shared_total' => Routine::query()->where('user_id', $userId)->count(),
            'comments_total' => Comment::query()->where('user_id', $userId)->count(),
            'likes_given_total' => RoutineLike::query()->where('user_id', $userId)->count(),
            'saves_total' => SavedRoutine::query()->where('user_id', $userId)->count(),
            'reactions_total' => RoutineReaction::query()->where('user_id', $userId)->count(),
            'mood_logs_7d' => MoodLog::query()
                ->where('user_id', $userId)
                ->where('logged_at', '>=', Carbon::now()->subDays(7)->startOfDay())
                ->count(),
            'journals_7d' => Journal::query()
                ->where('user_id', $userId)
                ->where('created_at', '>=', Carbon::now()->subDays(7)->startOfDay())
                ->count(),
            'routines_7d' => Routine::query()
                ->where('user_id', $userId)
                ->where('created_at', '>=', Carbon::now()->subDays(7)->startOfDay())
                ->count(),
        ];

        return [
            'totalEntries' => $totalEntries,
            'averageScore' => $averageScore,
            'positiveRate' => $positiveRate,
            'mostFrequentMood' => $mostFrequentMood,
            'trendSummary' => $trendSummary,
            'moodCounts' => $moodCounts,
            'categories' => $categories,
            'dailyMoodTrendDates' => $dailyMoodTrend->keys()->values(),
            'dailyMoodTrendScores' => $dailyMoodTrend->values(),
            'activitySummary' => $activitySummary,
        ];
    }

    private function buildAdminReportData(): array
    {
        $activeWindowStart = Carbon::now()->subDays(30)->startOfDay();

        $totalUsers = DB::table('users')->count();

        $activeUserIds = collect()
            ->merge(MoodLog::query()->where('logged_at', '>=', $activeWindowStart)->pluck('user_id'))
            ->merge(Journal::query()->where('created_at', '>=', $activeWindowStart)->pluck('user_id'))
            ->merge(Routine::query()->where('created_at', '>=', $activeWindowStart)->pluck('user_id'))
            ->merge(Comment::query()->where('created_at', '>=', $activeWindowStart)->pluck('user_id'))
            ->merge(RoutineLike::query()->where('created_at', '>=', $activeWindowStart)->pluck('user_id'))
            ->merge(SavedRoutine::query()->where('created_at', '>=', $activeWindowStart)->pluck('user_id'))
            ->merge(RoutineReaction::query()->where('created_at', '>=', $activeWindowStart)->pluck('user_id'))
            ->unique()
            ->values();

        $activeUsers = $activeUserIds->count();

        $mostCommonMoods = DB::table('mood_logs')
            ->select('mood_category', DB::raw('COUNT(*) as total'))
            ->groupBy('mood_category')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $label = 'Unknown';

                if (! empty($row->mood_category) && isset(MoodLog::categories()[$row->mood_category])) {
                    $label = MoodLog::categories()[$row->mood_category]['label'];
                }

                return [
                    'key' => $row->mood_category,
                    'label' => $label,
                    'total' => (int) $row->total,
                ];
            });

        $mostLikedRoutines = Routine::query()
            ->with('user')
            ->withCount('likes')
            ->orderByDesc('likes_count')
            ->orderByDesc('upvote_count')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $reportStats = [
            'total' => DB::table('reports')->count(),
            'pending' => Report::query()->where('status', 'pending')->count(),
            'resolved' => Report::query()->where('status', 'resolved')->count(),
            'dismissed' => Report::query()->where('status', 'dismissed')->count(),
            'by_type' => DB::table('reports')
                ->select('reportable_type', DB::raw('COUNT(*) as total'))
                ->groupBy('reportable_type')
                ->orderByDesc('total')
                ->get(),
        ];

        $pendingReports = Report::query()
            ->with(['reporter', 'reportable'])
            ->where('status', Report::STATUS_PENDING)
            ->orderBy('created_at')
            ->limit(20)
            ->get();

        $recentModerationActions = Report::query()
            ->with(['reporter', 'resolver', 'reportable'])
            ->whereIn('status', [Report::STATUS_RESOLVED, Report::STATUS_DISMISSED])
            ->orderByDesc('resolved_at')
            ->limit(12)
            ->get();

        $monitoringMetrics = [
            'reports_7d' => Report::query()
                ->where('created_at', '>=', Carbon::now()->subDays(7)->startOfDay())
                ->count(),
            'resolved_7d' => Report::query()
                ->where('status', Report::STATUS_RESOLVED)
                ->where('resolved_at', '>=', Carbon::now()->subDays(7)->startOfDay())
                ->count(),
            'dismissed_7d' => Report::query()
                ->where('status', Report::STATUS_DISMISSED)
                ->where('resolved_at', '>=', Carbon::now()->subDays(7)->startOfDay())
                ->count(),
            'removed_routines_total' => Routine::query()
                ->where('status', '!=', 'active')
                ->count(),
            'removed_comments_total' => Comment::query()
                ->where('status', '!=', 'active')
                ->count(),
        ];

        return [
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'mostCommonMoods' => $mostCommonMoods,
            'mostLikedRoutines' => $mostLikedRoutines,
            'reportStats' => $reportStats,
            'activeWindowLabel' => 'Last 30 days',
            'pendingReports' => $pendingReports,
            'recentModerationActions' => $recentModerationActions,
            'monitoringMetrics' => $monitoringMetrics,
            'reportReasonOptions' => Report::REASON_OPTIONS,
        ];
    }

    private function removeReportedContent(Report $report): bool
    {
        $target = $report->reportable;

        if (! $target) {
            return false;
        }

        if ($target instanceof Routine) {
            if ($target->status !== 'removed') {
                $target->status = 'removed';
                $target->save();
            }

            return true;
        }

        if ($target instanceof Comment) {
            if ($target->status !== 'removed') {
                $target->status = 'removed';
                $target->save();
            }

            return true;
        }

        return false;
    }
}
