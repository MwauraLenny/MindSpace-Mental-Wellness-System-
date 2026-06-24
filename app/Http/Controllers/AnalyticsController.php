<?php

namespace App\Http\Controllers;

use App\Models\MoodLog;
use App\Models\Routine;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function personal(): View
    {
        $userId = (int) Auth::id();
        $categories = MoodLog::categories();

        $moodLogs = MoodLog::query()
            ->where('user_id', $userId)
            ->orderByDesc('logged_at')
            ->get();

        $moodFrequency = collect(array_keys($categories))
            ->map(function (string $key) use ($moodLogs, $categories): array {
                $count = $moodLogs->where('mood_category_key', $key)->count();

                return [
                    'key' => $key,
                    'label' => $categories[$key]['label'],
                    'emoji' => $categories[$key]['emoji'],
                    'count' => $count,
                ];
            })
            ->sortByDesc('count')
            ->values();

        $totalMoodEntries = (int) $moodLogs->count();

        $moodDistribution = $moodFrequency->map(function (array $item) use ($totalMoodEntries): array {
            $percentage = $totalMoodEntries > 0
                ? round(($item['count'] / $totalMoodEntries) * 100, 1)
                : 0;

            return array_merge($item, ['percentage' => $percentage]);
        });

        $moodTrend = $moodLogs
            ->groupBy(fn ($log) => optional($log->logged_at)->format('Y-m-d'))
            ->map(fn ($logs) => round($logs->avg('mood_value'), 2))
            ->filter(fn ($value, $key) => $key !== null)
            ->sortKeys()
            ->take(-30);

        $weeklyWindowStart = Carbon::now()->subDays(7)->startOfDay();
        $monthlyWindowStart = Carbon::now()->subDays(30)->startOfDay();

        $weeklyMoodLogs = $moodLogs->filter(fn ($log) => optional($log->logged_at)?->greaterThanOrEqualTo($weeklyWindowStart));
        $monthlyMoodLogs = $moodLogs->filter(fn ($log) => optional($log->logged_at)?->greaterThanOrEqualTo($monthlyWindowStart));

        $weeklyMoodReport = [
            'entries' => $weeklyMoodLogs->count(),
            'average_score' => $weeklyMoodLogs->isNotEmpty() ? round($weeklyMoodLogs->avg('mood_value'), 2) : 0,
            'positive_rate' => $weeklyMoodLogs->isNotEmpty() ? round(($weeklyMoodLogs->where('mood_value', '>=', 4)->count() / $weeklyMoodLogs->count()) * 100, 1) : 0,
        ];

        $monthlyMoodReport = [
            'entries' => $monthlyMoodLogs->count(),
            'average_score' => $monthlyMoodLogs->isNotEmpty() ? round($monthlyMoodLogs->avg('mood_value'), 2) : 0,
            'positive_rate' => $monthlyMoodLogs->isNotEmpty() ? round(($monthlyMoodLogs->where('mood_value', '>=', 4)->count() / $monthlyMoodLogs->count()) * 100, 1) : 0,
        ];

        $dailyLogMap = $moodLogs
            ->filter(fn ($log) => optional($log->logged_at) !== null)
            ->map(fn ($log) => optional($log->logged_at)->toDateString())
            ->unique()
            ->flip();

        $currentStreak = 0;
        $cursor = Carbon::today();

        while ($dailyLogMap->has($cursor->toDateString())) {
            $currentStreak++;
            $cursor = $cursor->subDay();
        }

        $longestStreak = 0;
        $activeRun = 0;
        $allDates = $dailyLogMap->keys()->sort()->values();
        $previousDate = null;

        foreach ($allDates as $dateString) {
            $date = Carbon::parse($dateString);

            if ($previousDate && $date->diffInDays($previousDate) === 1) {
                $activeRun++;
            } else {
                $activeRun = 1;
            }

            $longestStreak = max($longestStreak, $activeRun);
            $previousDate = $date;
        }

        $weekAverages = $moodLogs
            ->filter(fn ($log) => optional($log->logged_at) !== null)
            ->groupBy(fn ($log) => optional($log->logged_at)->startOfWeek()->format('Y-m-d'))
            ->map(fn ($logs) => round($logs->avg('mood_value'), 2))
            ->sortKeys();

        $mostImprovedMoodReport = [
            'improvement' => 0,
            'from_week' => null,
            'to_week' => null,
        ];

        $weekKeys = $weekAverages->keys()->values();
        for ($i = 1; $i < $weekKeys->count(); $i++) {
            $previousKey = (string) $weekKeys[$i - 1];
            $currentKey = (string) $weekKeys[$i];
            $improvement = round(((float) $weekAverages[$currentKey]) - ((float) $weekAverages[$previousKey]), 2);

            if ($improvement > $mostImprovedMoodReport['improvement']) {
                $mostImprovedMoodReport = [
                    'improvement' => $improvement,
                    'from_week' => $previousKey,
                    'to_week' => $currentKey,
                ];
            }
        }

        $mostUsedRoutines = DB::table('routines')
            ->leftJoin('routine_likes as rl', function ($join) use ($userId): void {
                $join->on('routines.id', '=', 'rl.routine_id')
                    ->where('rl.user_id', '=', $userId);
            })
            ->leftJoin('saved_routines as sr', function ($join) use ($userId): void {
                $join->on('routines.id', '=', 'sr.routine_id')
                    ->where('sr.user_id', '=', $userId);
            })
            ->leftJoin('routine_reactions as rr', function ($join) use ($userId): void {
                $join->on('routines.id', '=', 'rr.routine_id')
                    ->where('rr.user_id', '=', $userId);
            })
            ->leftJoin('comments as c', function ($join) use ($userId): void {
                $join->on('routines.id', '=', 'c.commentable_id')
                    ->where('c.commentable_type', '=', Routine::class)
                    ->where('c.user_id', '=', $userId);
            })
            ->select(
                'routines.id',
                'routines.title',
                'routines.body',
                DB::raw('COUNT(DISTINCT rl.id) as likes_used'),
                DB::raw('COUNT(DISTINCT sr.id) as saves_used'),
                DB::raw('COUNT(DISTINCT rr.id) as reactions_used'),
                DB::raw('COUNT(DISTINCT c.id) as comments_used')
            )
            ->groupBy('routines.id', 'routines.title', 'routines.body')
            ->orderByDesc(DB::raw('(COUNT(DISTINCT rl.id) + COUNT(DISTINCT sr.id) + COUNT(DISTINCT rr.id) + COUNT(DISTINCT c.id))'))
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $usageCount = (int) $row->likes_used + (int) $row->saves_used + (int) $row->reactions_used + (int) $row->comments_used;

                return [
                    'id' => (int) $row->id,
                    'title' => $row->title ?: 'Community Wellness Routine',
                    'body' => $row->body,
                    'usage_count' => $usageCount,
                    'likes_used' => (int) $row->likes_used,
                    'saves_used' => (int) $row->saves_used,
                    'reactions_used' => (int) $row->reactions_used,
                    'comments_used' => (int) $row->comments_used,
                ];
            })
            ->filter(fn (array $row): bool => $row['usage_count'] > 0)
            ->take(5)
            ->values();

        return view('analytics.personal', [
            'totalMoodEntries' => $totalMoodEntries,
            'moodFrequency' => $moodFrequency,
            'moodDistribution' => $moodDistribution,
            'moodTrendDates' => $moodTrend->keys()->values(),
            'moodTrendScores' => $moodTrend->values(),
            'mostUsedRoutines' => $mostUsedRoutines,
            'moodDistributionLabels' => $moodDistribution->pluck('label')->values(),
            'moodDistributionCounts' => $moodDistribution->pluck('count')->values(),
            'weeklyMoodReport' => $weeklyMoodReport,
            'monthlyMoodReport' => $monthlyMoodReport,
            'moodStreaks' => [
                'current' => $currentStreak,
                'longest' => $longestStreak,
            ],
            'mostImprovedMoodReport' => $mostImprovedMoodReport,
        ]);
    }

    public function admin(): View
    {
        $windowStart = Carbon::now()->subDays(30)->startOfDay();

        $totalUsers = (int) DB::table('users')->count();

        $activeUserIds = collect()
            ->merge(DB::table('mood_logs')->where('logged_at', '>=', $windowStart)->pluck('user_id'))
            ->merge(DB::table('journals')->where('created_at', '>=', $windowStart)->pluck('user_id'))
            ->merge(DB::table('routines')->where('created_at', '>=', $windowStart)->pluck('user_id'))
            ->merge(DB::table('comments')->where('created_at', '>=', $windowStart)->pluck('user_id'))
            ->merge(DB::table('routine_likes')->where('created_at', '>=', $windowStart)->pluck('user_id'))
            ->merge(DB::table('saved_routines')->where('created_at', '>=', $windowStart)->pluck('user_id'))
            ->merge(DB::table('routine_reactions')->where('created_at', '>=', $windowStart)->pluck('user_id'))
            ->unique()
            ->values();

        $activeUsers = (int) $activeUserIds->count();

        $engagedUserIds = collect()
            ->merge(DB::table('routine_likes')->where('created_at', '>=', $windowStart)->pluck('user_id'))
            ->merge(DB::table('saved_routines')->where('created_at', '>=', $windowStart)->pluck('user_id'))
            ->merge(DB::table('routine_reactions')->where('created_at', '>=', $windowStart)->pluck('user_id'))
            ->merge(DB::table('comments')
                ->where('created_at', '>=', $windowStart)
                ->where('commentable_type', '=', Routine::class)
                ->pluck('user_id'))
            ->unique()
            ->values();

        $engagementRate = $totalUsers > 0
            ? round(($engagedUserIds->count() / $totalUsers) * 100, 1)
            : 0;

        $routinePopularity = DB::table('routines')
            ->leftJoin('routine_likes as rl', 'routines.id', '=', 'rl.routine_id')
            ->leftJoin('saved_routines as sr', 'routines.id', '=', 'sr.routine_id')
            ->leftJoin('routine_reactions as rr', 'routines.id', '=', 'rr.routine_id')
            ->leftJoin('comments as c', function ($join): void {
                $join->on('routines.id', '=', 'c.commentable_id')
                    ->where('c.commentable_type', '=', Routine::class)
                    ->where('c.status', '=', 'active');
            })
            ->select(
                'routines.id',
                'routines.title',
                DB::raw('COUNT(DISTINCT rl.id) as likes_count'),
                DB::raw('COUNT(DISTINCT sr.id) as saves_count'),
                DB::raw('COUNT(DISTINCT rr.id) as reactions_count'),
                DB::raw('COUNT(DISTINCT c.id) as comments_count')
            )
            ->groupBy('routines.id', 'routines.title')
            ->orderByDesc(DB::raw('(COUNT(DISTINCT rl.id) + COUNT(DISTINCT sr.id) + COUNT(DISTINCT rr.id) + COUNT(DISTINCT c.id))'))
            ->limit(8)
            ->get()
            ->map(function ($row) {
                return [
                    'id' => (int) $row->id,
                    'title' => $row->title ?: 'Community Wellness Routine',
                    'likes_count' => (int) $row->likes_count,
                    'saves_count' => (int) $row->saves_count,
                    'reactions_count' => (int) $row->reactions_count,
                    'comments_count' => (int) $row->comments_count,
                    'popularity_score' => (int) $row->likes_count + (int) $row->saves_count + (int) $row->reactions_count + (int) $row->comments_count,
                ];
            })
            ->values();

        $mostCommonEmotions = DB::table('mood_logs')
            ->select('mood_category', DB::raw('COUNT(*) as total'))
            ->groupBy('mood_category')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                $categories = MoodLog::categories();
                $label = 'Unknown';
                $emoji = '';

                if (! empty($row->mood_category) && isset($categories[$row->mood_category])) {
                    $label = $categories[$row->mood_category]['label'];
                    $emoji = $categories[$row->mood_category]['emoji'];
                }

                return [
                    'key' => $row->mood_category,
                    'label' => $label,
                    'emoji' => $emoji,
                    'total' => (int) $row->total,
                ];
            })
            ->values();

        return view('analytics.admin', [
            'activeUsers' => $activeUsers,
            'engagementRate' => $engagementRate,
            'routinePopularity' => $routinePopularity,
            'mostCommonEmotions' => $mostCommonEmotions,
            'activeUsersWindowLabel' => 'Last 30 days',
            'routinePopularityLabels' => $routinePopularity->pluck('title')->values(),
            'routinePopularityScores' => $routinePopularity->pluck('popularity_score')->values(),
            'emotionLabels' => $mostCommonEmotions->map(fn (array $item) => trim($item['emoji'].' '.$item['label']))->values(),
            'emotionTotals' => $mostCommonEmotions->pluck('total')->values(),
        ]);
    }
}
