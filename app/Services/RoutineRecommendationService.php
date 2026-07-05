<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\MoodLog;
use App\Models\Routine;
use App\Models\RoutineFeedback;
use App\Models\RoutineLike;
use App\Models\RoutineReaction;
use App\Models\SavedRoutine;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RoutineRecommendationService
{
    private const COPING_STRATEGIES_BY_MOOD = [
        'stressed' => [
            'Exercise routine: 15-minute brisk walk with deep breathing.',
            'Study routine: 25-minute focus sprint followed by a 5-minute reset.',
            'Meditation routine: 5 minutes of box breathing (4-4-4-4).',
        ],
        'anxious' => [
            'Grounding routine: identify 5 things you can see, 4 you can feel, 3 you can hear.',
            'Calm study routine: plan one small task and complete it in 15 minutes.',
            'Meditation routine: 7-minute body scan before starting work.',
        ],
        'sad' => [
            'Movement routine: sunlight walk and hydration check.',
            'Connection routine: send one supportive message to a trusted person.',
            'Journaling routine: write three thoughts and one gentle next step.',
        ],
        'angry' => [
            'Release routine: short intense movement set (pushups/squats/jumping jacks).',
            'Pause routine: 10 long exhales before replying to anything stressful.',
            'Reflection routine: note trigger, need, and one constructive action.',
        ],
        'tired' => [
            'Energy routine: water + stretch + 10-minute tidy.',
            'Study routine: one high-priority task only, then a full break.',
            'Recovery routine: no-screen wind-down in the final hour of the day.',
        ],
        'relaxed' => [
            'Maintenance routine: preserve your calm with a short evening reflection.',
            'Growth routine: tackle one meaningful task while momentum is high.',
            'Mindfulness routine: 5 minutes gratitude breathing.',
        ],
        'happy' => [
            'Momentum routine: channel positive mood into your top goal first.',
            'Community routine: share encouragement in the feed.',
            'Balance routine: schedule a short recovery block to sustain energy.',
        ],
        'excited' => [
            'Focus routine: write 3 priorities and commit to one first.',
            'Body routine: short exercise to regulate high energy.',
            'Calm routine: 3 minutes of slow breathing before deep work.',
        ],
        'default' => [
            'Exercise routine: 15-minute movement break.',
            'Study routine: one focused Pomodoro cycle.',
            'Meditation routine: 5-minute guided breathing.',
        ],
    ];

    public function buildForUser(int $userId, ?MoodLog $latestLog): array
    {
        $latestMoodValue = $latestLog?->mood_value;
        $latestMoodKey = $latestLog?->mood_category_key ?? 'default';
        $latestMoodLabel = $latestLog?->mood_label ?? 'Current';
        $latestMoodEmoji = $latestLog?->mood_emoji ?? '🙂';

        $similarUserIds = MoodLog::query()
            ->where('user_id', '!=', $userId)
            ->when(
                $latestLog,
                fn ($query) => $query->where(function ($moodQuery) use ($latestMoodValue, $latestMoodKey) {
                    $moodQuery
                        ->when($latestMoodValue, fn ($q) => $q->where('mood_value', $latestMoodValue))
                        ->orWhere('mood_category', $latestMoodKey);
                }),
                fn ($query) => $query->where('mood_value', '<=', 3)
            )
            ->orderByDesc('logged_at')
            ->limit(50)
            ->pluck('user_id')
            ->unique()
            ->values();

        $preferredCategoryIds = Routine::query()
            ->select('routines.routine_category_id')
            ->whereNotNull('routines.routine_category_id')
            ->where(function ($query) use ($userId) {
                $query->whereIn('routines.id', function ($sub) use ($userId) {
                    $sub->select('routine_id')->from('saved_routines')->where('user_id', $userId);
                })->orWhereIn('routines.id', function ($sub) use ($userId) {
                    $sub->select('routine_id')->from('routine_likes')->where('user_id', $userId);
                })->orWhereIn('routines.id', function ($sub) use ($userId) {
                    $sub->select('routine_id')->from('routine_reactions')->where('user_id', $userId);
                });
            })
            ->pluck('routines.routine_category_id')
            ->unique()
            ->values();

        $feedbackHistory = RoutineFeedback::query()
            ->with('routine:id,routine_category_id,mood_tag')
            ->where('user_id', $userId)
            ->whereNotNull('helped')
            ->get();

        $categoryOutcomeAffinity = [];
        $moodOutcomeAffinity = [];

        foreach ($feedbackHistory as $feedback) {
            if (! $feedback->routine) {
                continue;
            }

            $recencyWeight = max(0.5, 1 - now()->diffInDays($feedback->updated_at) / 180);
            $impact = $feedback->helped ? (1.5 * $recencyWeight) : (-1.0 * $recencyWeight);

            $categoryId = $feedback->routine->routine_category_id;
            if ($categoryId) {
                $categoryOutcomeAffinity[$categoryId] = ($categoryOutcomeAffinity[$categoryId] ?? 0) + $impact;
            }

            $moodTag = (int) ($feedback->routine->mood_tag ?? 0);
            if ($moodTag > 0) {
                $moodOutcomeAffinity[$moodTag] = ($moodOutcomeAffinity[$moodTag] ?? 0) + $impact;
            }
        }

        $candidateRoutines = Routine::query()
            ->with(['user', 'category'])
            ->withCount(['likes', 'saves', 'comments', 'reactions'])
            ->where('status', 'active')
            ->where('user_id', '!=', $userId)
            ->where(function ($query) use ($latestMoodValue, $similarUserIds, $preferredCategoryIds) {
                $query
                    ->when($latestMoodValue, fn ($q) => $q->orWhere('mood_tag', $latestMoodValue))
                    ->when($similarUserIds->isNotEmpty(), fn ($q) => $q->orWhereIn('user_id', $similarUserIds))
                    ->when($preferredCategoryIds->isNotEmpty(), fn ($q) => $q->orWhereIn('routine_category_id', $preferredCategoryIds));
            })
            ->orderByDesc('created_at')
            ->limit(40)
            ->get();

        if ($candidateRoutines->isEmpty()) {
            $candidateRoutines = Routine::query()
                ->with(['user', 'category'])
                ->withCount(['likes', 'saves', 'comments', 'reactions'])
                ->where('status', 'active')
                ->where('user_id', '!=', $userId)
                ->orderByDesc('upvote_count')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();
        }

        $recommendedRoutines = $candidateRoutines
            ->map(function (Routine $routine) use ($latestMoodValue, $similarUserIds, $preferredCategoryIds, $categoryOutcomeAffinity, $moodOutcomeAffinity) {
                $isMoodMatch = $latestMoodValue !== null && (int) $routine->mood_tag === (int) $latestMoodValue;
                $isSimilarUserRoutine = $similarUserIds->contains($routine->user_id);
                $isPreferredCategory = $preferredCategoryIds->contains($routine->routine_category_id);

                $engagementScore = (int) $routine->likes_count
                    + (int) $routine->saves_count
                    + (int) $routine->comments_count
                    + (int) $routine->reactions_count;

                $actorIds = collect()
                    ->merge(RoutineLike::query()->where('routine_id', $routine->id)->pluck('user_id'))
                    ->merge(SavedRoutine::query()->where('routine_id', $routine->id)->pluck('user_id'))
                    ->merge(RoutineReaction::query()->where('routine_id', $routine->id)->pluck('user_id'))
                    ->merge(Comment::query()->where('commentable_type', Routine::class)->where('commentable_id', $routine->id)->pluck('user_id'))
                    ->filter()
                    ->unique()
                    ->values();

                $uniqueActorCount = $actorIds->count();
                $trustedActorScore = $this->calculateTrustedActorScore($actorIds);

                $burstInteractions = (int) RoutineLike::query()->where('routine_id', $routine->id)->where('created_at', '>=', now()->subHour())->count()
                    + (int) SavedRoutine::query()->where('routine_id', $routine->id)->where('created_at', '>=', now()->subHour())->count()
                    + (int) RoutineReaction::query()->where('routine_id', $routine->id)->where('created_at', '>=', now()->subHour())->count()
                    + (int) Comment::query()->where('commentable_type', Routine::class)->where('commentable_id', $routine->id)->where('created_at', '>=', now()->subHour())->count();

                $burstPenalty = max(0, $burstInteractions - max(6, $uniqueActorCount * 2));

                $ageHours = max(0, now()->diffInHours($routine->created_at));
                $freshnessBonus = round(max(0, (72 - $ageHours) / 72) * 6, 2);

                $categoryAffinityBoost = round(($categoryOutcomeAffinity[$routine->routine_category_id] ?? 0) * 3, 2);
                $moodAffinityBoost = round(($moodOutcomeAffinity[(int) $routine->mood_tag] ?? 0) * 2, 2);

                $score = $engagementScore
                    + ($uniqueActorCount * 2)
                    + ($trustedActorScore * 2)
                    + $freshnessBonus
                    + $categoryAffinityBoost
                    + $moodAffinityBoost
                    + ($isMoodMatch ? 12 : 0)
                    + ($isSimilarUserRoutine ? 8 : 0)
                    + ($isPreferredCategory ? 5 : 0)
                    - ($burstPenalty * 3);

                $reason = 'Trending support routine';

                if ($isMoodMatch && $isSimilarUserRoutine) {
                    $reason = 'Mood match from users with similar mood history';
                } elseif ($isMoodMatch) {
                    $reason = 'Mood-based recommendation';
                } elseif ($isSimilarUserRoutine) {
                    $reason = 'From users with similar mood history';
                } elseif ($isPreferredCategory) {
                    $reason = 'Personalized from your engagement patterns';
                }

                if ($categoryAffinityBoost > 0 || $moodAffinityBoost > 0) {
                    $reason = 'Adapted from routines you rated as helpful';
                }

                $routine->setAttribute('recommendation_reason', $reason);
                $routine->setAttribute('recommendation_score', round($score, 2));
                $routine->setAttribute('recommendation_is_mood_match', $isMoodMatch);
                $routine->setAttribute('recommendation_is_similar_user', $isSimilarUserRoutine);
                $routine->setAttribute('recommendation_is_preferred_category', $isPreferredCategory);
                $routine->setAttribute('recommendation_engagement_score', $engagementScore);
                $routine->setAttribute('recommendation_unique_actor_count', $uniqueActorCount);
                $routine->setAttribute('recommendation_freshness_bonus', $freshnessBonus);
                $routine->setAttribute('recommendation_trusted_actor_score', round($trustedActorScore, 2));
                $routine->setAttribute('recommendation_burst_penalty', $burstPenalty);
                $routine->setAttribute('recommendation_category_affinity_boost', $categoryAffinityBoost);
                $routine->setAttribute('recommendation_mood_affinity_boost', $moodAffinityBoost);

                return $routine;
            })
            ->sortByDesc('recommendation_score')
            ->take(5)
            ->values();

        $copingStrategies = self::COPING_STRATEGIES_BY_MOOD[$latestMoodKey] ?? self::COPING_STRATEGIES_BY_MOOD['default'];

        return [
            'latestMoodLabel' => $latestMoodLabel,
            'latestMoodEmoji' => $latestMoodEmoji,
            'similarUserCount' => $similarUserIds->count(),
            'copingStrategies' => $copingStrategies,
            'routines' => $recommendedRoutines,
        ];
    }

    private function calculateTrustedActorScore(Collection $actorIds): float
    {
        if ($actorIds->isEmpty()) {
            return 0.0;
        }

        $actorIdArray = $actorIds->values()->all();

        $users = DB::table('users')
            ->whereIn('id', $actorIdArray)
            ->select('id', 'created_at')
            ->get();

        $moodLogCounts = DB::table('mood_logs')
            ->whereIn('user_id', $actorIdArray)
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $score = 0.0;

        foreach ($users as $user) {
            $createdAt = $user->created_at ? Carbon::parse($user->created_at) : now();
            $accountAgeDays = max(1, now()->diffInDays($createdAt));
            $logCount = (int) ($moodLogCounts[$user->id] ?? 0);

            $accountWeight = min(1.0, $accountAgeDays / 30);
            $activityWeight = min(1.0, $logCount / 20);

            $score += 1 + ($accountWeight * 0.75) + ($activityWeight * 0.75);
        }

        return $score;
    }
}
