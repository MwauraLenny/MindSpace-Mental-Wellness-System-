<?php

namespace App\Http\Controllers;

use App\Models\Routine;
use App\Models\Comment;
use App\Models\MoodLog;
use App\Models\RoutineCategory;
use App\Models\RoutineLike;
use App\Models\RoutineReaction;
use App\Models\SavedRoutine;
use App\Models\UserFollow;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoutineController extends Controller
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

    private const DEFAULT_CATEGORY_DEFINITIONS = [
        [
            'name' => 'Exercise Routines',
            'slug' => 'exercise-routines',
            'description' => 'Physical activity habits for mood regulation and energy.',
        ],
        [
            'name' => 'Study Habits',
            'slug' => 'study-habits',
            'description' => 'Focus and planning routines that reduce study stress.',
        ],
        [
            'name' => 'Meditation Routines',
            'slug' => 'meditation-routines',
            'description' => 'Breathing and mindfulness practices for calm.',
        ],
        [
            'name' => 'Social Activities',
            'slug' => 'social-activities',
            'description' => 'Healthy connection routines with trusted people.',
        ],
        [
            'name' => 'Music Recommendations',
            'slug' => 'music-recommendations',
            'description' => 'Playlists, artists, and listening routines that help emotions.',
        ],
        [
            'name' => 'Hobbies',
            'slug' => 'hobbies',
            'description' => 'Creative or practical hobby routines for wellbeing.',
        ],
        [
            'name' => 'Relaxation Techniques',
            'slug' => 'relaxation-techniques',
            'description' => 'Recovery and unwind techniques for stress relief.',
        ],
    ];

    // Show community feed filtered by user's latest mood
    public function index(Request $request)
    {
        return $this->feed($request);
    }

    public function saved(Request $request)
    {
        $request->merge(['view' => 'saved']);

        return $this->feed($request);
    }

    public function recommendations(Request $request)
    {
        $request->merge(['view' => 'recommendations']);

        return $this->feed($request);
    }

    public function feed(Request $request)
    {
        $latestLog = MoodLog::query()->where('user_id', Auth::id())
                        ->orderBy('logged_at', 'desc')
                        ->first();

        $moodFilter = $latestLog ? $latestLog->mood_value : null;
        $view = $request->string('view')->toString();
        $view = in_array($view, ['community', 'saved', 'mine', 'recommendations'], true) ? $view : 'community';
        $moodScope = $request->string('mood_scope')->toString();
        $moodScope = in_array($moodScope, ['all', 'match'], true) ? $moodScope : 'all';
        $search = trim($request->string('q')->toString());
        $explicitMoodTag = $request->integer('mood_tag');
        $sort = $request->string('sort')->toString();
        $sort = in_array($sort, ['latest', 'trending'], true) ? $sort : 'trending';

        $this->ensureDefaultCategories();

        $categories = RoutineCategory::query()->where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedCategory = $request->integer('category_id');

        $routinesQuery = Routine::query()->with([
                'user',
                'category',
                'reactions',
                'comments' => fn ($query) => $query
                    ->whereNull('parent_id')
                    ->with([
                        'user',
                        'replies' => fn ($replyQuery) => $replyQuery->with('user')->latest(),
                    ])
                    ->latest(),
            ])
            ->where('status', 'active')
            ->when(in_array($view, ['community', 'recommendations'], true) && $moodScope === 'match' && $moodFilter, fn ($q) => $q->where('mood_tag', $moodFilter))
            ->when($explicitMoodTag >= 1 && $explicitMoodTag <= 5, fn ($q) => $q->where('mood_tag', $explicitMoodTag))
            ->when($view === 'mine', fn ($q) => $q->where('user_id', Auth::id()))
            ->when($view === 'saved', fn ($q) => $q->whereHas('saves', fn ($sub) => $sub->where('user_id', Auth::id())))
            ->when($selectedCategory > 0, fn ($q) => $q->where('routine_category_id', $selectedCategory))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($searchQuery) use ($search): void {
                    $searchQuery
                        ->where('title', 'like', '%'.$search.'%')
                        ->orWhere('body', 'like', '%'.$search.'%')
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->withCount(['likes', 'saves', 'comments', 'reactions']);

        if ($sort === 'latest') {
            $routinesQuery
                ->orderByDesc('created_at');
        } else {
            $routinesQuery
                ->orderByDesc('upvote_count')
                ->orderByDesc('reactions_count')
                ->orderByDesc('comments_count')
                ->orderByDesc('created_at');
        }

        $routines = $routinesQuery->get();

        $likedRoutineIds = RoutineLike::query()->where('user_id', Auth::id())
            ->pluck('routine_id')
            ->flip();

        $savedRoutineIds = SavedRoutine::query()->where('user_id', Auth::id())
            ->pluck('routine_id')
            ->flip();

        $followedUserIds = UserFollow::query()->where('follower_id', Auth::id())
            ->pluck('followee_id')
            ->flip();

        $followerCountByRoutine = $routines->mapWithKeys(function (Routine $routine) {
            $count = UserFollow::query()
                ->where('followee_id', $routine->user_id)
                ->count();

            return [$routine->id => $count];
        });

        $myReactions = RoutineReaction::query()->where('user_id', Auth::id())
            ->get()
            ->keyBy('routine_id');

        $reactionMeta = RoutineReaction::REACTION_META;

        $reactionCountsByRoutine = $routines->mapWithKeys(function ($routine) {
            $counts = collect(RoutineReaction::ALLOWED_REACTIONS)
                ->mapWithKeys(fn ($reaction) => [$reaction => $routine->reactions->where('reaction', $reaction)->count()]);

            return [$routine->id => $counts];
        });

        $engagementCountsByRoutine = $routines->mapWithKeys(function ($routine) use ($reactionCountsByRoutine) {
            $total = (int) $routine->likes_count
                + (int) $routine->saves_count
                + (int) $routine->comments_count
                + (int) $reactionCountsByRoutine[$routine->id]->sum();

            return [$routine->id => $total];
        });

        $recommendations = $this->buildRecommendations(Auth::id(), $latestLog);

        $trendingRoutines = Routine::query()
            ->with('user')
            ->withCount(['likes', 'comments', 'reactions'])
            ->where('status', 'active')
            ->when(in_array($view, ['community', 'recommendations'], true) && $moodScope === 'match' && $moodFilter, fn ($q) => $q->where('mood_tag', $moodFilter))
            ->orderByDesc('upvote_count')
            ->orderByDesc('reactions_count')
            ->orderByDesc('comments_count')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('routines.index', compact(
            'routines',
            'moodFilter',
            'view',
            'moodScope',
            'search',
            'explicitMoodTag',
            'sort',
            'categories',
            'selectedCategory',
            'likedRoutineIds',
            'savedRoutineIds',
            'followedUserIds',
            'followerCountByRoutine',
            'myReactions',
            'reactionMeta',
            'reactionCountsByRoutine',
            'engagementCountsByRoutine',
            'recommendations',
            'trendingRoutines'
        ));
    }

    // Show create form
    public function create()
    {
        $this->ensureDefaultCategories();

        $latestLog = MoodLog::query()->where('user_id', Auth::id())
                        ->orderBy('logged_at', 'desc')
                        ->first();

        $categories = RoutineCategory::query()->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('routines.create', compact('latestLog', 'categories'));
    }

    // Store new routine
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:1000',
            'mood_tag' => 'required|integer|min:1|max:5',
            'routine_category_id' => 'required|integer|exists:routine_categories,id',
            'is_anonymous' => 'nullable|boolean',
        ]);

        $routine = Routine::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'body' => $request->body,
            'mood_tag' => $request->mood_tag,
            'routine_category_id' => $request->routine_category_id,
            'is_anonymous' => $this->resolveAnonymousSelection($request),
        ]);

        /** @var NotificationService $notificationService */
        $notificationService = app(NotificationService::class);

        $notificationService->createRoutineRecommendationNotifications($routine);
        $notificationService->notifyAdmins(
            'admin_notification',
            'New routine shared',
            'A new community routine has been shared and is available in the feed.',
            [
                'routine_id' => $routine->id,
                'creator_id' => Auth::id(),
            ]
        );

        return redirect()->route('routines.index')
                ->with('success', 'Your routine has been shared with the community!');
    }

    // Upvote a routine
    public function upvote(int $id)
    {
        $routine = Routine::findOrFail($id);

        $existing = RoutineLike::query()->where('routine_id', $routine->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            RoutineLike::query()->whereKey($existing->id)->delete();
            $routine->upvote_count = max(0, $routine->upvote_count - 1);
            $routine->save();

            return back()->with('success', 'Like removed.');
        }

        RoutineLike::create([
            'routine_id' => $routine->id,
            'user_id' => Auth::id(),
        ]);

        $routine->increment('upvote_count');

        /** @var NotificationService $notificationService */
        $notificationService = app(NotificationService::class);
        $notificationService->notifyRoutineInteraction(
            $routine,
            Auth::user(),
            'community_interaction',
            'Someone liked your routine',
            'Your routine received a new like from the community.'
        );

        return back()->with('success', 'Routine liked.');
    }

    public function save(int $id)
    {
        $routine = Routine::findOrFail($id);

        $existing = SavedRoutine::query()->where('routine_id', $routine->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            SavedRoutine::query()->whereKey($existing->id)->delete();

            return back()->with('success', 'Routine removed from saved list.');
        }

        SavedRoutine::create([
            'routine_id' => $routine->id,
            'user_id' => Auth::id(),
        ]);

        /** @var NotificationService $notificationService */
        $notificationService = app(NotificationService::class);
        $notificationService->notifyRoutineInteraction(
            $routine,
            Auth::user(),
            'community_interaction',
            'Someone saved your routine',
            'Your routine was saved by another community member.'
        );

        return back()->with('success', 'Routine saved to your collection.');
    }

    public function followContributor(int $id)
    {
        $routine = Routine::findOrFail($id);
        $followeeId = (int) $routine->user_id;
        $followerId = (int) Auth::id();

        if ($followeeId === $followerId) {
            return back()->with('error', 'You cannot follow your own account.');
        }

        UserFollow::query()->firstOrCreate([
            'follower_id' => $followerId,
            'followee_id' => $followeeId,
        ]);

        return back()->with('success', 'Contributor followed.');
    }

    public function unfollowContributor(int $id)
    {
        $routine = Routine::findOrFail($id);

        UserFollow::query()
            ->where('follower_id', Auth::id())
            ->where('followee_id', $routine->user_id)
            ->delete();

        return back()->with('success', 'Contributor unfollowed.');
    }

    public function react(Request $request, int $id)
    {
        $routine = Routine::findOrFail($id);

        $validated = $request->validate([
            'reaction' => 'required|string|in:'.implode(',', RoutineReaction::ALLOWED_REACTIONS),
        ]);

        RoutineReaction::updateOrCreate(
            [
                'routine_id' => $routine->id,
                'user_id' => Auth::id(),
            ],
            [
                'reaction' => $validated['reaction'],
            ]
        );

        $reactionLabel = RoutineReaction::REACTION_META[$validated['reaction']]['label'] ?? 'reaction';

        /** @var NotificationService $notificationService */
        $notificationService = app(NotificationService::class);
        $notificationService->notifyRoutineInteraction(
            $routine,
            Auth::user(),
            'community_interaction',
            'Someone reacted to your routine',
            'Your routine received a new '.$reactionLabel.' reaction.'
        );

        return back()->with('success', 'Reaction updated.');
    }

    public function comment(Request $request, int $id)
    {
        $routine = Routine::findOrFail($id);

        $validated = $request->validate([
            'body' => 'required|string|max:500',
            'parent_id' => 'nullable|integer|exists:comments,id',
            'is_anonymous' => 'nullable|boolean',
        ]);

        $parentId = $validated['parent_id'] ?? null;

        if ($parentId !== null) {
            $parentComment = Comment::query()->where('id', $parentId)
                ->where('commentable_type', Routine::class)
                ->where('commentable_id', $routine->id)
                ->whereNull('parent_id')
                ->first();

            if (! $parentComment) {
                return back()
                    ->withInput()
                    ->withErrors(['parent_id' => 'Invalid parent comment for this routine.']);
            }
        }

        Comment::create([
            'user_id' => Auth::id(),
            'commentable_type' => Routine::class,
            'commentable_id' => $routine->id,
            'parent_id' => $parentId,
            'body' => $validated['body'],
            'is_anonymous' => $this->resolveAnonymousSelection($request),
            'status' => 'active',
        ]);

        /** @var NotificationService $notificationService */
        $notificationService = app(NotificationService::class);
        $notificationService->notifyRoutineInteraction(
            $routine,
            Auth::user(),
            'community_interaction',
            $parentId ? 'Someone replied to your routine thread' : 'Someone commented on your routine',
            $parentId
                ? 'Your routine discussion has a new reply.'
                : 'Your routine received a new community comment.'
        );

        return back()->with('success', $parentId ? 'Reply posted.' : 'Comment posted.');
    }

    public function destroyComment(int $id, int $commentId)
    {
        $routine = Routine::findOrFail($id);

        $comment = Comment::query()->where('id', $commentId)
            ->where('commentable_type', Routine::class)
            ->where('commentable_id', $routine->id)
            ->firstOrFail();

        if ($comment->user_id !== Auth::id()) {
            abort(403, 'You can only delete your own comments.');
        }

        Comment::query()->whereKey($comment->id)->delete();

        return back()->with('success', 'Comment deleted.');
    }

    private function ensureDefaultCategories(): void
    {
        foreach (self::DEFAULT_CATEGORY_DEFINITIONS as $definition) {
            RoutineCategory::updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'is_active' => true,
                ]
            );
        }
    }

    private function buildRecommendations(int $userId, ?MoodLog $latestLog): array
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
            ->map(function (Routine $routine) use ($latestMoodValue, $similarUserIds, $preferredCategoryIds) {
                $isMoodMatch = $latestMoodValue !== null && (int) $routine->mood_tag === (int) $latestMoodValue;
                $isSimilarUserRoutine = $similarUserIds->contains($routine->user_id);
                $isPreferredCategory = $preferredCategoryIds->contains($routine->routine_category_id);

                $engagementScore = (int) $routine->likes_count
                    + (int) $routine->saves_count
                    + (int) $routine->comments_count
                    + (int) $routine->reactions_count;

                $score = $engagementScore
                    + ($isMoodMatch ? 12 : 0)
                    + ($isSimilarUserRoutine ? 8 : 0)
                    + ($isPreferredCategory ? 5 : 0);

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

                $routine->setAttribute('recommendation_reason', $reason);
                $routine->setAttribute('recommendation_score', $score);
                $routine->setAttribute('recommendation_is_mood_match', $isMoodMatch);
                $routine->setAttribute('recommendation_is_similar_user', $isSimilarUserRoutine);
                $routine->setAttribute('recommendation_is_preferred_category', $isPreferredCategory);
                $routine->setAttribute('recommendation_engagement_score', $engagementScore);

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

    private function resolveAnonymousSelection(Request $request): bool
    {
        return $request->boolean('is_anonymous');
    }
}
