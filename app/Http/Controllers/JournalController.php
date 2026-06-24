<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\MoodLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class JournalController extends Controller
{
    public function index(): View
    {
        $journals = Journal::where('user_id', Auth::id())
            ->with('moodLog')
            ->latest()
            ->get();

        return view('journals.index', [
            'journals' => $journals,
        ]);
    }

    public function create(): View
    {
        $moodLogs = MoodLog::where('user_id', Auth::id())
            ->orderBy('logged_at', 'desc')
            ->limit(60)
            ->get();

        return view('journals.create', [
            'moodLogs' => $moodLogs,
        ]);
    }

    public function show(int $id): View
    {
        $journal = Journal::where('id', $id)
            ->where('user_id', Auth::id())
            ->with('moodLog')
            ->firstOrFail();

        return view('journals.show', [
            'journal' => $journal,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'related_mood_log_id' => 'nullable|integer|exists:mood_logs,id',
        ]);

        $relatedMoodLogId = $validated['related_mood_log_id'] ?? null;

        if ($relatedMoodLogId !== null) {
            $exists = MoodLog::where('id', $relatedMoodLogId)
                ->where('user_id', Auth::id())
                ->exists();

            if (! $exists) {
                return back()
                    ->withInput()
                    ->withErrors(['related_mood_log_id' => 'Selected mood is not valid for your account.']);
            }
        }

        Journal::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'body' => $validated['content'],
            'entry_date' => now()->toDateString(),
            'mood_log_id' => $relatedMoodLogId,
            'visibility' => 'private',
        ]);

        return redirect()->route('journals.index')->with('success', 'Journal entry created successfully.');
    }

    public function edit(int $id): View
    {
        $journal = Journal::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $moodLogs = MoodLog::where('user_id', Auth::id())
            ->orderBy('logged_at', 'desc')
            ->limit(60)
            ->get();

        return view('journals.edit', [
            'journal' => $journal,
            'moodLogs' => $moodLogs,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $journal = Journal::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'related_mood_log_id' => 'nullable|integer|exists:mood_logs,id',
        ]);

        $relatedMoodLogId = $validated['related_mood_log_id'] ?? null;

        if ($relatedMoodLogId !== null) {
            $exists = MoodLog::where('id', $relatedMoodLogId)
                ->where('user_id', Auth::id())
                ->exists();

            if (! $exists) {
                return back()
                    ->withInput()
                    ->withErrors(['related_mood_log_id' => 'Selected mood is not valid for your account.']);
            }
        }

        $journal->update([
            'title' => $validated['title'],
            'body' => $validated['content'],
            'mood_log_id' => $relatedMoodLogId,
        ]);

        return redirect()->route('journals.index')->with('success', 'Journal entry updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $journal = Journal::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $journal->delete();

        return redirect()->route('journals.index')->with('success', 'Journal entry deleted successfully.');
    }
}
