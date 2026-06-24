<?php

namespace App\Http\Controllers;

use App\Models\Routine;
use App\Models\Comment;
use App\Models\MoodLog;
use App\Models\RoutineCategory;
use App\Models\RoutineLike;
use App\Models\RoutineReaction;
use App\Models\SavedRoutine;
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

    public function feed(Request $request)
    {
        $latestLog = MoodLog::where('user_id', Auth::id())
                        ->orderBy('logged_at', 'desc')
                        ->first();

        $moodFilter = $latestLog ? $latestLog->mood_value : null;
        $view = $request->string('view')->toString();
        $view = in_array($view, ['community', 'saved', 'mine'], true) ? $view : 'community';
        $moodScope = $request->string('mood_scope')->toString();
        $moodScope = in_array($moodScope, ['all', 'match'], true) ? $moodScope : 'all';

        $this->ensureDefaultCategories();

        $categories = RoutineCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedCategory = $request->integer('category_id');

        $routines = Routine::with([
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
            ->when($view === 'community' && $moodScope === 'match' && $moodFilter, fn ($q) => $q->where('mood_tag', $moodFilter))
            ->when($view === 'mine', fn ($q) => $q->where('user_id', Auth::id()))
            ->when($view === 'saved', fn ($q) => $q->whereHas('saves', fn ($sub) => $sub->where('user_id', Auth::id())))
            ->when($selectedCategory > 0, fn ($q) => $q->where('routine_category_id', $selectedCategory))
            ->withCount(['likes', 'saves', 'comments'])
            ->orderBy('upvote_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $likedRoutineIds = RoutineLike::where('user_id', Auth::id())
            ->pluck('routine_id')
            ->flip();

        $savedRoutineIds = SavedRoutine::where('user_id', Auth::id())
            ->pluck('routine_id')
            ->flip();

        $myReactions = RoutineReaction::where('user_id', Auth::id())
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

        return view('routines.index', compact(
            'routines',
            'moodFilter',
            'view',
            'moodScope',
            'categories',
            'selectedCategory',
            'likedRoutineIds',
            'savedRoutineIds',
            'myReactions',
            'reactionMeta',
            'reactionCountsByRoutine',
            'engagementCountsByRoutine',
            'recommendations'
        ));
    }

    // Show create form
    public function create()
    {
        $this->ensureDefaultCategories();

        $latestLog = MoodLog::where('user_id', Auth::id())
                        ->orderBy('logged_at', 'desc')
                        ->first();

        $categories = RoutineCategory::where('is_active', true)
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

        Routine::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'body' => $request->body,
            'mood_tag' => $request->mood_tag,
            'routine_category_id' => $request->routine_category_id,
            'is_anonymous' => $request->has('is_anonymous'),
        ]);

        return redirect()->route('routines.index')
                ->with('success', 'Your routine has been shared with the community!');
    }

    // Upvote a routine
    public function upvote($id)
    {
        $routine = Routine::findOrFail($id);

        $existing = RoutineLike::where('routine_id', $routine->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            $existing->delete();
            $routine->upvote_count = max(0, $routine->upvote_count - 1);
            $routine->save();

            return back()->with('success', 'Like removed.');
        }

        RoutineLike::create([
            'routine_id' => $routine->id,
            'user_id' => Auth::id(),
        ]);

        $routine->increment('upvote_count');

        return back()->with('success', 'Routine liked.');
    }

    public function save($id)
    {
        $routine = Routine::findOrFail($id);

        $existing = SavedRoutine::where('routine_id', $routine->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            $existing->delete();

            return back()->with('success', 'Routine removed from saved list.');
        }

        SavedRoutine::create([
            'routine_id' => $routine->id,
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', 'Routine saved to your collection.');
    }

    public function react(Request $request, $id)
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

        return back()->with('success', 'Reaction updated.');
    }

    public function comment(Request $request, $id)
    {
        $routine = Routine::findOrFail($id);

        $validated = $request->validate([
            'body' => 'required|string|max:500',
            'parent_id' => 'nullable|integer|exists:comments,id',
            'is_anonymous' => 'nullable|boolean',
        ]);

        $parentId = $validated['parent_id'] ?? null;

        if ($parentId !== null) {
            $parentComment = Comment::where('id', $parentId)
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
            'is_anonymous' => $request->boolean('is_anonymous'),
            'status' => 'active',
        ]);

        return back()->with('success', $parentId ? 'Reply posted.' : 'Comment posted.');
    }

    public function destroyComment($id, $commentId)
    {
        $routine = Routine::findOrFail($id);

        $comment = Comment::where('id', $commentId)
            ->where('commentable_type', Routine::class)
            ->where('commentable_id', $routine->id)
            ->firstOrFail();

        if ($comment->user_id !== Auth::id()) {
            abort(403, 'You can only delete your own comments.');
        }

        $comment->delete();

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
}
