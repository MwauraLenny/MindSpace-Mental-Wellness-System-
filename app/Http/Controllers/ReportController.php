<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Comment;
use App\Models\Journal;
use App\Models\MoodLog;
use App\Models\Report;
use App\Models\Routine;
use App\Models\RoutineLike;
use App\Models\RoutineReaction;
use App\Models\SavedRoutine;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function student(): View
    {
        return view('reports.student', $this->buildStudentReportData(Auth::id()));
    }

    public function studentExportCsv(): StreamedResponse
    {
        $data = $this->buildStudentReportData(Auth::id());
        $filename = 'student-report-'.Carbon::now()->format('Ymd_His').'.csv';

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

    public function studentExportPdf(): Response
    {
        $data = $this->buildStudentReportData(Auth::id());

        $pdf = Pdf::loadView('reports.student-export-pdf', array_merge($data, [
            'generatedAt' => Carbon::now(),
        ]));

        return $pdf->download('student-report-'.Carbon::now()->format('Ymd_His').'.pdf');
    }

    public function admin(): View
    {
        return view('reports.admin', $this->buildAdminReportData());
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

        $pdf = Pdf::loadView('reports.admin-export-pdf', array_merge($data, [
            'generatedAt' => Carbon::now(),
        ]));

        return $pdf->download('admin-report-'.Carbon::now()->format('Ymd_His').'.pdf');
    }

    private function buildStudentReportData(int $userId): array
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

        $totalUsers = User::query()->count();

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

        $mostCommonMoods = MoodLog::query()
            ->selectRaw('mood_category, count(*) as total')
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
            'total' => Report::query()->count(),
            'pending' => Report::query()->where('status', 'pending')->count(),
            'resolved' => Report::query()->where('status', 'resolved')->count(),
            'dismissed' => Report::query()->where('status', 'dismissed')->count(),
            'by_type' => Report::query()
                ->selectRaw('reportable_type, count(*) as total')
                ->groupBy('reportable_type')
                ->orderByDesc('total')
                ->get(),
        ];

        return [
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'mostCommonMoods' => $mostCommonMoods,
            'mostLikedRoutines' => $mostLikedRoutines,
            'reportStats' => $reportStats,
            'activeWindowLabel' => 'Last 30 days',
        ];
    }
}
