<?php

namespace App\Http\Controllers;

use App\Models\MoodLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MoodLogController extends Controller
{
    public function index()
    {
        $logs = MoodLog::where('user_id', Auth::id())
                    ->orderBy('logged_at', 'desc')
                    ->get();

        $latestMood = $logs->first();
        $previousMood = $logs->skip(1)->first();
        $moodImproved = $latestMood && $previousMood
            && $latestMood->mood_value > $previousMood->mood_value;

        return view('mood.index', compact('logs', 'latestMood', 'moodImproved'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mood_value' => 'required|integer|min:1|max:5',
            'journal_note' => 'nullable|string|max:500',
        ]);

        MoodLog::create([
            'user_id' => Auth::id(),
            'mood_value' => $request->mood_value,
            'journal_note' => $request->journal_note,
        ]);

        return redirect()->route('mood.index')->with('success', 'Mood logged successfully!');
    }
}
