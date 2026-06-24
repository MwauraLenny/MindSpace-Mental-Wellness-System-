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
            'engagementCountsByRoutine'
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
}
