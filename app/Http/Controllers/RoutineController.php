<?php

namespace App\Http\Controllers;

use App\Models\Routine;
use App\Models\MoodLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoutineController extends Controller
{
    // Show community feed filtered by user's latest mood
    public function index()
    {
        $latestLog = MoodLog::where('user_id', Auth::id())
                        ->orderBy('logged_at', 'desc')
                        ->first();

        $moodFilter = $latestLog ? $latestLog->mood_value : null;

        $routines = Routine::where('status', 'active')
                        ->when($moodFilter, fn($q) => $q->where('mood_tag', $moodFilter))
                        ->orderBy('upvote_count', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('routines.index', compact('routines', 'moodFilter'));
    }

    // Show create form
    public function create()
    {
        $latestLog = MoodLog::where('user_id', Auth::id())
                        ->orderBy('logged_at', 'desc')
                        ->first();

        return view('routines.create', compact('latestLog'));
    }

    // Store new routine
    public function store(Request $request)
    {
        $request->validate([
            'body' => 'required|string|max:1000',
            'mood_tag' => 'required|integer|min:1|max:5',
            'is_anonymous' => 'nullable|boolean',
        ]);

        Routine::create([
            'user_id' => Auth::id(),
            'body' => $request->body,
            'mood_tag' => $request->mood_tag,
            'is_anonymous' => $request->has('is_anonymous'),
        ]);

        return redirect()->route('routines.index')
                ->with('success', 'Your routine has been shared with the community!');
    }

    // Upvote a routine
    public function upvote($id)
    {
        $routine = Routine::findOrFail($id);
        $routine->increment('upvote_count');

        return back()->with('success', 'Upvoted!');
    }
}
