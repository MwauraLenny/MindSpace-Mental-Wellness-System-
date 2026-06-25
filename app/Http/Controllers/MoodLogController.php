<?php

namespace App\Http\Controllers;

use App\Models\MoodLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MoodLogController extends Controller
{
    public function index()
    {
        $userName = Auth::user()?->name ?? 'there';
        $moodCategories = MoodLog::categoriesByScoreDesc();

        $logs = MoodLog::query()
                ->where('user_id', Auth::id())
                    ->orderBy('logged_at', 'desc')
                    ->get();

        $uniqueLoggedDates = $logs
            ->pluck('logged_at')
            ->filter()
            ->map(fn (Carbon $loggedAt) => $loggedAt->toDateString())
            ->unique()
            ->values();

        $streakDays = 0;

        if ($uniqueLoggedDates->isNotEmpty()) {
            $remainingDates = $uniqueLoggedDates->flip();
            $cursor = Carbon::parse($uniqueLoggedDates->first())->startOfDay();

            while ($remainingDates->has($cursor->toDateString())) {
                $streakDays++;
                $cursor->subDay();
            }
        }

        $streakFire = match (true) {
            $streakDays >= 30 => '🔥🔥🔥🔥',
            $streakDays >= 14 => '🔥🔥🔥',
            $streakDays >= 7 => '🔥🔥',
            $streakDays >= 3 => '🔥',
            default => '🕯️',
        };

        $streakTier = match (true) {
            $streakDays >= 30 => 'Legendary streak',
            $streakDays >= 14 => 'Strong streak',
            $streakDays >= 7 => 'Solid streak',
            $streakDays >= 3 => 'Building streak',
            default => 'New streak',
        };

        $entriesCount = $logs->count();
        $journalCount = $logs->filter(fn (MoodLog $log) => filled($log->journal_note))->count();
        $positiveCount = $logs->where('mood_value', '>=', 4)->count();

        $achievements = collect([
            [
                'title' => 'First Check-In',
                'description' => 'Logged your first mood entry.',
                'icon' => '🌱',
                'unlocked' => $entriesCount >= 1,
            ],
            [
                'title' => 'Consistent Week',
                'description' => 'Logged moods for 7 or more days in total.',
                'icon' => '📅',
                'unlocked' => $entriesCount >= 7,
            ],
            [
                'title' => 'On Fire',
                'description' => 'Reached a 7-day streak.',
                'icon' => '🔥',
                'unlocked' => $streakDays >= 7,
            ],
            [
                'title' => 'Flame Keeper',
                'description' => 'Reached a 14-day streak.',
                'icon' => '🔥🔥',
                'unlocked' => $streakDays >= 14,
            ],
            [
                'title' => 'Mood Legend',
                'description' => 'Reached a 30-day streak.',
                'icon' => '🔥🔥🔥',
                'unlocked' => $streakDays >= 30,
            ],
            [
                'title' => 'Reflective Writer',
                'description' => 'Added 5 journal notes to mood logs.',
                'icon' => '✍️',
                'unlocked' => $journalCount >= 5,
            ],
            [
                'title' => 'Bright Week',
                'description' => 'Logged 5 moods in the positive zone (4-5).',
                'icon' => '🌤️',
                'unlocked' => $positiveCount >= 5,
            ],
        ]);

        $latestMood = $logs->first();
        $previousMood = $logs->skip(1)->first();
        $moodImproved = $latestMood && $previousMood
            && $latestMood->mood_value > $previousMood->mood_value;
        $moodDropped = $latestMood && $previousMood
            && $latestMood->mood_value < $previousMood->mood_value;
        $moodStable = $latestMood && $previousMood
            && $latestMood->mood_value === $previousMood->mood_value;

        $moodStableRegion = null;

        if ($moodStable && $latestMood) {
            $moodStableRegion = match (true) {
                $latestMood->mood_value >= 4 => 'happy',
                $latestMood->mood_value === 3 => 'mid',
                default => 'bad',
            };
        }

        return view('mood.index', compact(
            'logs',
            'latestMood',
            'moodImproved',
            'moodDropped',
            'moodStable',
            'moodStableRegion',
            'moodCategories',
            'userName',
            'streakDays',
            'streakFire',
            'streakTier',
            'achievements'
        ));
    }

    public function dashboard(Request $request)
    {
        $userName = Auth::user()?->name ?? 'there';
        $period = $this->resolvePeriod($request);

        $logs = $this->filteredLogsQuery($period)
            ->orderBy('logged_at', 'asc')
            ->get();

        $categories = MoodLog::categories();
        $totalEntries = $logs->count();
        $averageScore = $totalEntries > 0 ? round($logs->avg('mood_value'), 2) : 0;

        $moodCounts = collect(array_keys($categories))
            ->mapWithKeys(fn ($key) => [$key => $logs->where('mood_category_key', $key)->count()]);

        $mostFrequentMoodKey = $moodCounts->sortDesc()->keys()->first();
        $mostFrequentMood = $mostFrequentMoodKey ? $categories[$mostFrequentMoodKey]['label'] : 'No entries yet';

        $positiveRate = $totalEntries > 0
            ? round(($logs->where('mood_value', '>=', 4)->count() / $totalEntries) * 100, 1)
            : 0;

        $dailyAverage = $logs
            ->groupBy(fn ($log) => optional($log->logged_at)->format('Y-m-d'))
            ->map(fn ($dayLogs) => round($dayLogs->avg('mood_value'), 2))
            ->filter(fn ($value, $key) => $key !== null)
            ->sortKeys();

        $dayOfWeekPattern = $logs
            ->groupBy(fn ($log) => optional($log->logged_at)->format('l'))
            ->map(fn ($group) => round($group->avg('mood_value'), 2));

        $timeOfDayPattern = $logs->groupBy(function ($log) {
            $hour = optional($log->logged_at)->hour;

            if ($hour === null) {
                return 'Unknown';
            }

            if ($hour >= 5 && $hour < 12) {
                return 'Morning';
            }

            if ($hour >= 12 && $hour < 17) {
                return 'Afternoon';
            }

            if ($hour >= 17 && $hour < 22) {
                return 'Evening';
            }

            return 'Night';
        })->map(fn ($group) => round($group->avg('mood_value'), 2));

        $recentWindow = $logs->sortByDesc('logged_at')->take(14)->sortBy('logged_at')->values();
        $currentWindowAverage = $recentWindow->take(-7)->avg('mood_value');
        $previousWindowAverage = $recentWindow->take(7)->avg('mood_value');

        $trendSummary = 'Log more moods to generate trend insights.';

        if ($recentWindow->count() >= 7) {
            if ($previousWindowAverage !== null && $currentWindowAverage !== null) {
                if ($currentWindowAverage > $previousWindowAverage) {
                    $trendSummary = 'Your recent emotional trend is improving compared to the prior week.';
                } elseif ($currentWindowAverage < $previousWindowAverage) {
                    $trendSummary = 'Your recent emotional trend has dipped. Consider routines that helped in better weeks.';
                } else {
                    $trendSummary = 'Your emotional trend is steady over the last two weeks.';
                }
            }
        }

        return view('mood.dashboard', [
            'logs' => $logs->sortByDesc('logged_at')->values(),
            'period' => $period,
            'periodOptions' => [
                'all' => 'All time',
                '7d' => 'Last 7 days',
                '30d' => 'Last 30 days',
            ],
            'totalEntries' => $totalEntries,
            'averageScore' => $averageScore,
            'positiveRate' => $positiveRate,
            'mostFrequentMood' => $mostFrequentMood,
            'trendSummary' => $trendSummary,
            'moodCounts' => $moodCounts,
            'dayOfWeekPattern' => $dayOfWeekPattern,
            'timeOfDayPattern' => $timeOfDayPattern,
            'dailyAverage' => $dailyAverage,
            'categories' => $categories,
            'chartDates' => $dailyAverage->keys()->values(),
            'chartScores' => $dailyAverage->values(),
            'chartMoodLabels' => $moodCounts->keys()->map(fn ($key) => $categories[$key]['label'])->values(),
            'chartMoodCounts' => $moodCounts->values(),
            'userName' => $userName,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $period = $this->resolvePeriod($request);
        $logs = $this->filteredLogsQuery($period)
            ->orderBy('logged_at', 'desc')
            ->get();

        $filename = 'mood-history-'.$period.'-'.Carbon::now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($logs): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['Date/Time', 'Mood Category', 'Mood Score', 'Journal Note']);

            foreach ($logs as $log) {
                fputcsv($handle, [
                    optional($log->logged_at)->format('Y-m-d H:i:s'),
                    $log->mood_label,
                    $log->mood_value,
                    $log->journal_note,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $period = $this->resolvePeriod($request);
        $logs = $this->filteredLogsQuery($period)
            ->orderBy('logged_at', 'desc')
            ->get();

        $totalEntries = $logs->count();
        $averageScore = $totalEntries > 0 ? round($logs->avg('mood_value'), 2) : 0;
        $positiveRate = $totalEntries > 0
            ? round(($logs->where('mood_value', '>=', 4)->count() / $totalEntries) * 100, 1)
            : 0;

        $pdf = Pdf::loadView('mood.export-pdf', [
            'logs' => $logs,
            'period' => $period,
            'generatedAt' => Carbon::now(),
            'totalEntries' => $totalEntries,
            'averageScore' => $averageScore,
            'positiveRate' => $positiveRate,
        ]);

        return $pdf->download('mood-report-'.$period.'-'.Carbon::now()->format('Ymd_His').'.pdf');
    }

    public function store(Request $request)
    {
        $request->validate([
            'mood_category' => 'required|string|in:'.implode(',', array_keys(MoodLog::categories())),
            'journal_note' => 'nullable|string|max:500',
        ]);

        $category = $request->string('mood_category')->toString();

        MoodLog::create([
            'user_id' => Auth::id(),
            'mood_category' => $category,
            'mood_value' => MoodLog::scoreFromCategory($category),
            'journal_note' => $request->journal_note,
        ]);

        return redirect()->route('mood.index')->with('success', 'Mood logged successfully!');
    }

    private function resolvePeriod(Request $request): string
    {
        $period = $request->string('period')->toString();

        if ($period === '') {
            return 'all';
        }

        return in_array($period, ['all', '7d', '30d'], true) ? $period : 'all';
    }

    private function filteredLogsQuery(string $period): Builder
    {
        $query = MoodLog::query()
            ->where('user_id', Auth::id());

        if ($period === '7d') {
            $query->where('logged_at', '>=', Carbon::now()->subDays(7)->startOfDay());
        }

        if ($period === '30d') {
            $query->where('logged_at', '>=', Carbon::now()->subDays(30)->startOfDay());
        }

        return $query;
    }
}
