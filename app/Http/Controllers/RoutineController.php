<?php

namespace App\Http\Controllers;

use App\Models\Routine;
use App\Models\Comment;
use App\Models\MoodLog;
use App\Models\Reaction;
use App\Models\RoutineCategory;
use App\Models\RoutineDownvote;
use App\Models\RoutineFeedback;
use App\Models\RoutineLike;
use App\Models\RoutineReaction;
use App\Models\SavedRoutine;
use App\Services\NotificationService;
use App\Services\RoutineRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

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

    private const COMMENT_REACTION_META = [
        'helpful' => ['emoji' => '❤️', 'label' => 'Helpful'],
        'inspiring' => ['emoji' => '✨', 'label' => 'Inspiring'],
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
                        'reactions',
                        'user',
                        'replies' => fn ($replyQuery) => $replyQuery->with(['user', 'reactions'])->latest(),
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

        $downvotedRoutineIds = RoutineDownvote::query()->where('user_id', Auth::id())
            ->pluck('routine_id')
            ->flip();

        $savedRoutineIds = SavedRoutine::query()->where('user_id', Auth::id())
            ->pluck('routine_id')
            ->flip();

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

        $commentIds = $routines
            ->flatMap(function (Routine $routine) {
                return $routine->comments->flatMap(function (Comment $comment) {
                    return collect([$comment->id])->merge($comment->replies->pluck('id'));
                });
            })
            ->unique()
            ->values();

        $myCommentReactions = Reaction::query()
            ->where('user_id', Auth::id())
            ->where('reactable_type', Comment::class)
            ->when($commentIds->isNotEmpty(), fn ($query) => $query->whereIn('reactable_id', $commentIds))
            ->get()
            ->keyBy('reactable_id');

        $commentReactionMeta = self::COMMENT_REACTION_META;

        /** @var RoutineRecommendationService $recommendationService */
        $recommendationService = app(RoutineRecommendationService::class);
        $recommendations = $recommendationService->buildForUser((int) Auth::id(), $latestLog);

        $feedbackRoutineIds = $routines->pluck('id')
            ->merge(collect($recommendations['routines'])->pluck('id'))
            ->unique()
            ->values();

        $routineFeedbackByRoutineId = RoutineFeedback::query()
            ->where('user_id', Auth::id())
            ->when($feedbackRoutineIds->isNotEmpty(), fn ($q) => $q->whereIn('routine_id', $feedbackRoutineIds))
            ->get()
            ->keyBy('routine_id');

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
            'downvotedRoutineIds',
            'savedRoutineIds',
            'myReactions',
            'reactionMeta',
            'reactionCountsByRoutine',
            'engagementCountsByRoutine',
            'commentReactionMeta',
            'myCommentReactions',
            'routineFeedbackByRoutineId',
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
    public function upvote(Request $request, int $id)
    {
        $routine = Routine::findOrFail($id);

        if ($response = $this->throttleVote($request, $routine->id)) {
            return $response;
        }

        if ((int) $routine->user_id === (int) Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You cannot like your own routine.',
                ], 422);
            }

            return back()->with('error', 'You cannot like your own routine.');
        }

        $existing = RoutineLike::query()->where('routine_id', $routine->id)
            ->where('user_id', Auth::id())
            ->first();

        $existingDownvote = RoutineDownvote::query()->where('routine_id', $routine->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            RoutineLike::query()->whereKey($existing->id)->delete();
            $routine->upvote_count = max(0, $routine->upvote_count - 1);
            $routine->save();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Like removed.',
                    'liked' => false,
                    'downvoted' => $existingDownvote !== null,
                    'likes_count' => (int) $routine->upvote_count,
                    'downvotes_count' => (int) $routine->downvote_count,
                    'routine_id' => (int) $routine->id,
                ]);
            }

            return back()->with('success', 'Like removed.');
        }

        RoutineLike::create([
            'routine_id' => $routine->id,
            'user_id' => Auth::id(),
        ]);

        if ($existingDownvote) {
            RoutineDownvote::query()->whereKey($existingDownvote->id)->delete();
            $routine->downvote_count = max(0, $routine->downvote_count - 1);
            $routine->save();
        }

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

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Routine liked.',
                'liked' => true,
                'downvoted' => false,
                'likes_count' => (int) $routine->upvote_count,
                'downvotes_count' => (int) $routine->downvote_count,
                'routine_id' => (int) $routine->id,
            ]);
        }

        return back()->with('success', 'Routine liked.');
    }

    public function downvote(Request $request, int $id)
    {
        $routine = Routine::findOrFail($id);

        if ($response = $this->throttleVote($request, $routine->id)) {
            return $response;
        }

        if ((int) $routine->user_id === (int) Auth::id()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You cannot downvote your own routine.',
                ], 422);
            }

            return back()->with('error', 'You cannot downvote your own routine.');
        }

        $existing = RoutineDownvote::query()->where('routine_id', $routine->id)
            ->where('user_id', Auth::id())
            ->first();

        $existingLike = RoutineLike::query()->where('routine_id', $routine->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            RoutineDownvote::query()->whereKey($existing->id)->delete();
            $routine->downvote_count = max(0, $routine->downvote_count - 1);
            $routine->save();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Downvote removed.',
                    'downvoted' => false,
                    'liked' => $existingLike !== null,
                    'downvotes_count' => (int) $routine->downvote_count,
                    'likes_count' => (int) $routine->upvote_count,
                    'routine_id' => (int) $routine->id,
                ]);
            }

            return back()->with('success', 'Downvote removed.');
        }

        RoutineDownvote::create([
            'routine_id' => $routine->id,
            'user_id' => Auth::id(),
        ]);

        if ($existingLike) {
            RoutineLike::query()->whereKey($existingLike->id)->delete();
            $routine->upvote_count = max(0, $routine->upvote_count - 1);
            $routine->save();
        }

        $routine->increment('downvote_count');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Routine downvoted.',
                'downvoted' => true,
                'liked' => false,
                'downvotes_count' => (int) $routine->downvote_count,
                'likes_count' => (int) $routine->upvote_count,
                'routine_id' => (int) $routine->id,
            ]);
        }

        return back()->with('success', 'Routine downvoted.');
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

    public function feedback(Request $request, int $id)
    {
        $routine = Routine::query()
            ->where('id', $id)
            ->where('status', 'active')
            ->firstOrFail();

        $validated = $request->validate([
            'helped' => 'required|boolean',
        ]);

        $latestMood = MoodLog::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('logged_at')
            ->first();

        $feedback = RoutineFeedback::query()->firstOrNew([
            'user_id' => Auth::id(),
            'routine_id' => $routine->id,
        ]);

        if (! $feedback->exists) {
            $feedback->before_mood_value = $latestMood?->mood_value;
        } else {
            $feedback->after_mood_value = $latestMood?->mood_value;
        }

        $feedback->helped = (bool) $validated['helped'];

        if ($feedback->before_mood_value !== null && $feedback->after_mood_value !== null) {
            $feedback->mood_delta = (int) $feedback->after_mood_value - (int) $feedback->before_mood_value;
        }

        $feedback->save();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Feedback saved.',
                'routine_id' => (int) $routine->id,
                'helped' => (bool) $feedback->helped,
            ]);
        }

        return back()->with('success', 'Thanks for the feedback.');
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

    public function reactToComment(Request $request, int $id, int $commentId)
    {
        $routine = Routine::findOrFail($id);

        $comment = Comment::query()->where('id', $commentId)
            ->where('commentable_type', Routine::class)
            ->where('commentable_id', $routine->id)
            ->firstOrFail();

        $validated = $request->validate([
            'reaction' => 'required|string|in:'.implode(',', array_keys(self::COMMENT_REACTION_META)),
        ]);

        $existing = Reaction::query()
            ->where('user_id', Auth::id())
            ->where('reactable_type', Comment::class)
            ->where('reactable_id', $comment->id)
            ->first();

        if ($existing && $existing->reaction === $validated['reaction']) {
            Reaction::query()->whereKey($existing->id)->delete();

            if ($request->expectsJson()) {
                $counts = Reaction::query()
                    ->where('reactable_type', Comment::class)
                    ->where('reactable_id', $comment->id)
                    ->selectRaw('reaction, COUNT(*) as total')
                    ->groupBy('reaction')
                    ->pluck('total', 'reaction');

                return response()->json([
                    'message' => 'Reaction removed.',
                    'comment_id' => (int) $comment->id,
                    'active_reaction' => null,
                    'counts' => [
                        'helpful' => (int) ($counts['helpful'] ?? 0),
                        'inspiring' => (int) ($counts['inspiring'] ?? 0),
                    ],
                ]);
            }

            return back()->with('success', 'Reaction removed.');
        }

        Reaction::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'reactable_type' => Comment::class,
                'reactable_id' => $comment->id,
            ],
            [
                'reaction' => $validated['reaction'],
            ]
        );

        if ($request->expectsJson()) {
            $counts = Reaction::query()
                ->where('reactable_type', Comment::class)
                ->where('reactable_id', $comment->id)
                ->selectRaw('reaction, COUNT(*) as total')
                ->groupBy('reaction')
                ->pluck('total', 'reaction');

            return response()->json([
                'message' => 'Reaction updated.',
                'comment_id' => (int) $comment->id,
                'active_reaction' => $validated['reaction'],
                'counts' => [
                    'helpful' => (int) ($counts['helpful'] ?? 0),
                    'inspiring' => (int) ($counts['inspiring'] ?? 0),
                ],
            ]);
        }

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

    private function throttleVote(Request $request, int $routineId)
    {
        $userId = (int) Auth::id();
        $globalKey = 'routine-vote-global:'.$userId;
        $routineKey = 'routine-vote-routine:'.$userId.':'.$routineId;

        if (RateLimiter::tooManyAttempts($globalKey, 60) || RateLimiter::tooManyAttempts($routineKey, 12)) {
            $message = 'Too many vote actions in a short period. Please wait and try again.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 429);
            }

            return back()->with('error', $message);
        }

        RateLimiter::hit($globalKey, 3600);
        RateLimiter::hit($routineKey, 3600);

        return null;
    }

    private function resolveAnonymousSelection(Request $request): bool
    {
        return $request->boolean('is_anonymous');
    }
}
