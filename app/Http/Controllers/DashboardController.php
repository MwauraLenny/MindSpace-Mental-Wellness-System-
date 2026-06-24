<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\MoodLog;
use App\Models\Notification;
use App\Models\Report;
use App\Models\Routine;
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

        $moodCategories = MoodLog::categories();

        $moodLogs = MoodLog::query()
            ->where('user_id', $user->id)
            ->orderByDesc('logged_at')
            ->limit(30)
            ->get();

        $latestMood = $moodLogs->first();

        $moodHistory = $moodLogs
            ->sortByDesc('logged_at')
            ->take(8)
            ->values();

        $moodTrend = $moodLogs
            ->groupBy(fn ($log) => optional($log->logged_at)->format('Y-m-d'))
            ->map(fn ($logs) => round($logs->avg('mood_value'), 2))
            ->filter(fn ($score, $date) => $date !== null)
            ->sortKeys();

        $moodDistribution = collect(array_keys($moodCategories))
            ->mapWithKeys(fn ($key) => [$moodCategories[$key]['label'] => $moodLogs->where('mood_category_key', $key)->count()]);

        $recommendations = Routine::query()
            ->with('user')
            ->withCount(['likes', 'comments'])
            ->where('status', 'active')
            ->where('user_id', '!=', $user->id)
            ->when($latestMood, fn ($query) => $query->where('mood_tag', $latestMood->mood_value))
            ->orderByDesc('upvote_count')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        if ($recommendations->isEmpty()) {
            $recommendations = Routine::query()
                ->with('user')
                ->withCount(['likes', 'comments'])
                ->where('status', 'active')
                ->where('user_id', '!=', $user->id)
                ->orderByDesc('upvote_count')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        }

        $notifications = Notification::query()
            ->where('user_id', $user->id)
            ->orderByRaw('read_at IS NULL DESC')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $communityActivity = Routine::query()
            ->with('user')
            ->withCount(['likes', 'comments'])
            ->where('status', 'active')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        return view('dashboard', [
            'moodCategories' => $moodCategories,
            'latestMood' => $latestMood,
            'moodHistory' => $moodHistory,
            'moodTrendDates' => $moodTrend->keys()->values(),
            'moodTrendScores' => $moodTrend->values(),
            'moodDistributionLabels' => $moodDistribution->keys()->values(),
            'moodDistributionValues' => $moodDistribution->values(),
            'recommendations' => $recommendations,
            'notifications' => $notifications,
            'communityActivity' => $communityActivity,
        ]);
    }

    public function admin(): View
    {
        $totalUsers = DB::table('users')->count();
        $totalRoutines = DB::table('routines')->count();
        $totalReports = DB::table('reports')->count();

        $communityActivity = [
            'newUsers7d' => User::query()
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

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalRoutines' => $totalRoutines,
            'totalReports' => $totalReports,
            'communityActivity' => $communityActivity,
            'recentCommunityItems' => $recentCommunityItems,
            'moderationQueue' => $moderationQueue,
        ]);
    }
}
