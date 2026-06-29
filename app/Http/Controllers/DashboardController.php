<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Journal;
use App\Models\MoodLog;
use App\Models\Report;
use App\Models\Routine;
use App\Models\SavedRoutine;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        if ($user?->role === 'admin') {
            return $this->admin();
        }

        $totalMoodLogs = MoodLog::query()
            ->where('user_id', $user->id)
            ->count();

        $distinctMoodDates = MoodLog::query()
            ->where('user_id', $user->id)
            ->whereNotNull('logged_at')
            ->selectRaw('DATE(logged_at) as logged_date')
            ->groupBy('logged_date')
            ->orderByDesc('logged_date')
            ->pluck('logged_date')
            ->map(fn ($date) => Carbon::parse($date)->startOfDay())
            ->values();

        $currentStreak = 0;

        if ($distinctMoodDates->isNotEmpty()) {
            $latestLoggedDate = $distinctMoodDates->first();
            $daysSinceLatestLog = $latestLoggedDate->diffInDays(Carbon::today());

            if ($daysSinceLatestLog <= 1) {
                $expectedDate = $latestLoggedDate->copy();

                foreach ($distinctMoodDates as $loggedDate) {
                    if (! $loggedDate->equalTo($expectedDate)) {
                        break;
                    }

                    $currentStreak++;
                    $expectedDate = $expectedDate->copy()->subDay();
                }
            }
        }

        $longestStreak = 0;
        $rollingStreak = 0;
        $previousDate = null;

        foreach ($distinctMoodDates->reverse()->values() as $loggedDate) {
            if ($previousDate === null) {
                $rollingStreak = 1;
            } elseif ($loggedDate->equalTo($previousDate->copy()->addDay())) {
                $rollingStreak++;
            } else {
                $rollingStreak = 1;
            }

            $longestStreak = max($longestStreak, $rollingStreak);
            $previousDate = $loggedDate;
        }

        $streakMeta = $this->streakMeta($currentStreak);

        $routinesCreatedCount = Routine::query()->where('user_id', $user->id)->count();
        $savedRoutinesCount = SavedRoutine::query()->where('user_id', $user->id)->count();
        $commentsCount = Comment::query()->where('user_id', $user->id)->count();
        $journalCount = Journal::query()->where('user_id', $user->id)->count();

        $moodHistory = MoodLog::query()
            ->where('user_id', $user->id)
            ->orderByDesc('logged_at')
            ->limit(2)
            ->get();

        $latestMood = $moodHistory->first();

        $achievementBadges = $this->buildAchievementCatalog([
            'totalMoodLogs' => $totalMoodLogs,
            'currentStreak' => $currentStreak,
            'longestStreak' => $longestStreak,
            'routinesCreatedCount' => $routinesCreatedCount,
            'savedRoutinesCount' => $savedRoutinesCount,
            'commentsCount' => $commentsCount,
            'journalCount' => $journalCount,
        ]);

        return view('dashboard', [
            'userName' => $user->name,
            'latestMood' => $latestMood,
            'moodHistory' => $moodHistory,
            'achievementBadges' => $achievementBadges,
            'currentStreak' => $currentStreak,
            'longestStreak' => $longestStreak,
            'streakEmoji' => $streakMeta['emoji'],
            'streakTierLabel' => $streakMeta['label'],
        ]);
    }

    private function streakMeta(int $streak): array
    {
        return match (true) {
            $streak >= 30 => ['emoji' => '💜🔥💜🔥💜', 'label' => 'Purple Inferno'],
            $streak >= 14 => ['emoji' => '💙🔥💜🔥', 'label' => 'Blue to Purple Flame'],
            $streak >= 7 => ['emoji' => '💙🔥💙', 'label' => 'Blue Flame'],
            $streak >= 3 => ['emoji' => '🔥🔥', 'label' => 'Heating Up'],
            $streak >= 1 => ['emoji' => '🔥', 'label' => 'Started'],
            default => ['emoji' => '🧊', 'label' => 'Restarting'],
        };
    }

    private function buildAchievementCatalog(array $stats): array
    {
        $communityActions = $stats['routinesCreatedCount'] + $stats['savedRoutinesCount'] + $stats['commentsCount'];
        $reflectionActions = $stats['totalMoodLogs'] + $stats['journalCount'];

        return [
            // 1-10 Mood logs
            $this->achievement('🌱', 'First Check-In', 'Log 1 mood entry.', $stats['totalMoodLogs'] >= 1),
            $this->achievement('🟢', 'Mood Starter', 'Log 3 mood entries.', $stats['totalMoodLogs'] >= 3),
            $this->achievement('📘', 'Mood Learner', 'Log 5 mood entries.', $stats['totalMoodLogs'] >= 5),
            $this->achievement('✅', 'Consistency Starter', 'Log 7 mood entries.', $stats['totalMoodLogs'] >= 7),
            $this->achievement('📗', 'Mood Builder', 'Log 10 mood entries.', $stats['totalMoodLogs'] >= 10),
            $this->achievement('📙', 'Mood Tracker', 'Log 14 mood entries.', $stats['totalMoodLogs'] >= 14),
            $this->achievement('📕', 'Mood Keeper', 'Log 21 mood entries.', $stats['totalMoodLogs'] >= 21),
            $this->achievement('📚', 'Mood Historian', 'Log 30 mood entries.', $stats['totalMoodLogs'] >= 30),
            $this->achievement('🧠', 'Mood Analyst', 'Log 45 mood entries.', $stats['totalMoodLogs'] >= 45),
            $this->achievement('🏆', 'Mood Master', 'Log 60 mood entries.', $stats['totalMoodLogs'] >= 60),

            // 11-20 Current streak
            $this->achievement('🔥', 'Streak Started', 'Reach a 1-day current streak.', $stats['currentStreak'] >= 1),
            $this->achievement('🔥', 'Spark Streak', 'Reach a 3-day current streak.', $stats['currentStreak'] >= 3),
            $this->achievement('🔥', 'Warm Streak', 'Reach a 5-day current streak.', $stats['currentStreak'] >= 5),
            $this->achievement('🔥', 'Weekly Flame', 'Reach a 7-day current streak.', $stats['currentStreak'] >= 7),
            $this->achievement('🔥', 'Momentum Flame', 'Reach a 10-day current streak.', $stats['currentStreak'] >= 10),
            $this->achievement('🔥', 'Fortnight Fire', 'Reach a 14-day current streak.', $stats['currentStreak'] >= 14),
            $this->achievement('🔥', 'Three-Week Blaze', 'Reach a 21-day current streak.', $stats['currentStreak'] >= 21),
            $this->achievement('🔥', 'Monthly Inferno', 'Reach a 30-day current streak.', $stats['currentStreak'] >= 30),
            $this->achievement('🔥', 'Relentless Flame', 'Reach a 45-day current streak.', $stats['currentStreak'] >= 45),
            $this->achievement('🔥', 'Streak Legend', 'Reach a 60-day current streak.', $stats['currentStreak'] >= 60),

            // 21-25 Longest streak
            $this->achievement('⏱️', 'Streak Memory', 'Set a 3-day best streak.', $stats['longestStreak'] >= 3),
            $this->achievement('⏱️', 'Streak Veteran', 'Set a 7-day best streak.', $stats['longestStreak'] >= 7),
            $this->achievement('⏱️', 'Streak Pro', 'Set a 14-day best streak.', $stats['longestStreak'] >= 14),
            $this->achievement('⏱️', 'Streak Elite', 'Set a 30-day best streak.', $stats['longestStreak'] >= 30),
            $this->achievement('⏱️', 'Streak Champion', 'Set a 60-day best streak.', $stats['longestStreak'] >= 60),

            // 26-30 Routines created
            $this->achievement('🛠️', 'Routine Builder', 'Create 1 routine.', $stats['routinesCreatedCount'] >= 1),
            $this->achievement('🧭', 'Routine Explorer', 'Create 2 routines.', $stats['routinesCreatedCount'] >= 2),
            $this->achievement('🧱', 'Routine Architect', 'Create 3 routines.', $stats['routinesCreatedCount'] >= 3),
            $this->achievement('🏗️', 'Routine Engineer', 'Create 5 routines.', $stats['routinesCreatedCount'] >= 5),
            $this->achievement('🌟', 'Routine Mentor', 'Create 8 routines.', $stats['routinesCreatedCount'] >= 8),

            // 31-35 Saved routines
            $this->achievement('📌', 'First Save', 'Save 1 routine.', $stats['savedRoutinesCount'] >= 1),
            $this->achievement('📌', 'Collector', 'Save 3 routines.', $stats['savedRoutinesCount'] >= 3),
            $this->achievement('📌', 'Curator', 'Save 5 routines.', $stats['savedRoutinesCount'] >= 5),
            $this->achievement('📌', 'Library Keeper', 'Save 10 routines.', $stats['savedRoutinesCount'] >= 10),
            $this->achievement('📌', 'Routine Archivist', 'Save 20 routines.', $stats['savedRoutinesCount'] >= 20),

            // 36-40 Comments
            $this->achievement('💬', 'First Comment', 'Post 1 community comment.', $stats['commentsCount'] >= 1),
            $this->achievement('💬', 'Conversation Starter', 'Post 3 community comments.', $stats['commentsCount'] >= 3),
            $this->achievement('💬', 'Supportive Voice', 'Post 5 community comments.', $stats['commentsCount'] >= 5),
            $this->achievement('🤝', 'Community Encourager', 'Post 10 community comments.', $stats['commentsCount'] >= 10),
            $this->achievement('📣', 'Community Anchor', 'Post 20 community comments.', $stats['commentsCount'] >= 20),

            // 41-45 Journals
            $this->achievement('📝', 'First Reflection', 'Write 1 journal entry.', $stats['journalCount'] >= 1),
            $this->achievement('📝', 'Reflection Starter', 'Write 3 journal entries.', $stats['journalCount'] >= 3),
            $this->achievement('📝', 'Reflection Habit', 'Write 5 journal entries.', $stats['journalCount'] >= 5),
            $this->achievement('📓', 'Journal Keeper', 'Write 10 journal entries.', $stats['journalCount'] >= 10),
            $this->achievement('📔', 'Journal Sage', 'Write 20 journal entries.', $stats['journalCount'] >= 20),

            // 46-48 Community actions (routines + saves + comments)
            $this->achievement('🌍', 'Community Starter', 'Complete 10 community actions.', $communityActions >= 10),
            $this->achievement('🌍', 'Community Builder', 'Complete 25 community actions.', $communityActions >= 25),
            $this->achievement('🌍', 'Community Pillar', 'Complete 50 community actions.', $communityActions >= 50),

            // 49-50 Reflection totals (moods + journals)
            $this->achievement('🧘', 'Reflection Flow', 'Complete 20 total reflection actions.', $reflectionActions >= 20),
            $this->achievement('🧘', 'Reflection Master', 'Complete 50 total reflection actions.', $reflectionActions >= 50),
        ];
    }

    private function achievement(string $emoji, string $title, string $description, bool $unlocked): array
    {
        return [
            'emoji' => $emoji,
            'title' => $title,
            'description' => $description,
            'unlocked' => $unlocked,
        ];
    }

    public function admin(): View
    {
        $totalUsers = DB::table('users')->where('role', '!=', 'admin')->count();
        $totalRoutines = DB::table('routines')->count();
        $totalReports = DB::table('reports')->count();

        $communityActivity = [
            'newUsers7d' => User::query()
                ->where('role', '!=', 'admin')
                ->where('created_at', '>=', Carbon::now()->subDays(7)->startOfDay())
                ->count(),
            'newRoutines7d' => Routine::query()
                ->where('created_at', '>=', Carbon::now()->subDays(7)->startOfDay())
                ->count(),
            'newComments7d' => Comment::query()
                ->where('created_at', '>=', Carbon::now()->subDays(7)->startOfDay())
                ->count(),
            'newReports7d' => Report::query()
                ->where('created_at', '>=', Carbon::now()->subDays(7)->startOfDay())
                ->count(),
        ];

        $recentCommunityItems = Routine::query()
            ->with('user')
            ->withCount(['likes', 'comments'])
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $moderationQueue = Report::query()
            ->with(['reporter', 'reportable'])
            ->where('status', 'pending')
            ->orderBy('created_at')
            ->limit(10)
            ->get();

        $recentActivityPreview = collect();

        MoodLog::query()
            ->with('user')
            ->orderByDesc('logged_at')
            ->limit(6)
            ->get()
            ->each(function (MoodLog $log) use ($recentActivityPreview): void {
                $recentActivityPreview->push([
                    'type' => 'Mood log',
                    'summary' => ($log->user?->name ?? 'User').' logged '.$log->mood_label,
                    'at' => $log->logged_at,
                ]);
            });

        Journal::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get()
            ->each(function (Journal $journal) use ($recentActivityPreview): void {
                $recentActivityPreview->push([
                    'type' => 'Journal',
                    'summary' => ($journal->user?->name ?? 'User').' posted a journal entry',
                    'at' => $journal->created_at,
                ]);
            });

        Comment::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get()
            ->each(function (Comment $comment) use ($recentActivityPreview): void {
                $recentActivityPreview->push([
                    'type' => 'Comment',
                    'summary' => ($comment->user?->name ?? 'User').' posted a community comment',
                    'at' => $comment->created_at,
                ]);
            });

        $recentActivityPreview = $recentActivityPreview
            ->filter(fn (array $event) => ! empty($event['at']))
            ->sortByDesc('at')
            ->take(10)
            ->values();

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalRoutines' => $totalRoutines,
            'totalReports' => $totalReports,
            'communityActivity' => $communityActivity,
            'recentCommunityItems' => $recentCommunityItems,
            'moderationQueue' => $moderationQueue,
            'recentActivityPreview' => $recentActivityPreview,
        ]);
    }
}
