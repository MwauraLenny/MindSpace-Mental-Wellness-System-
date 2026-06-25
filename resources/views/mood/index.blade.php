<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Log Your Mood
            </h2>

            <a
                href="{{ route('mood.dashboard') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
            >
                Mood Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Success message --}}
            @if(session('success'))
                <div class="bg-green-100 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Mood improved prompt --}}
            @if($moodImproved)
                <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-4 rounded">
                    <p class="font-semibold">Nice progress, {{ $userName }}. Your mood has improved! 🎉</p>
                    <p class="text-sm mt-1">Would you like to share what helped you so others can benefit too?</p>
                    <a href="/routines/create" class="mt-2 inline-block bg-yellow-500 text-white px-4 py-2 rounded text-sm">
                        Yes, share my routine
                    </a>
                </div>
            @endif

            {{-- Mood dropped prompt --}}
            @if($moodDropped)
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-4 rounded">
                    <p class="font-semibold">{{ $userName }}, it looks like your mood dropped a bit.</p>
                    <p class="text-sm mt-1">That is okay. You can add a note, check your mood patterns, or explore community routines that may help today.</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ route('mood.dashboard') }}" class="inline-block bg-rose-600 text-white px-4 py-2 rounded text-sm hover:bg-rose-700">
                            View Mood Insights
                        </a>
                        <a href="{{ route('community.feed') }}" class="inline-block bg-white border border-rose-300 text-rose-700 px-4 py-2 rounded text-sm hover:bg-rose-50">
                            Explore Support Routines
                        </a>
                    </div>
                </div>
            @endif

            {{-- Mood stable prompt --}}
            @if($moodStable)
                <div class="bg-sky-50 border border-sky-200 text-sky-800 px-4 py-4 rounded">
                    @if($moodStableRegion === 'happy')
                        <p class="font-semibold">Great consistency, {{ $userName }}. You stayed in a strong mood zone (4-5).</p>
                        <p class="text-sm mt-1">Keep repeating what is working and log one quick note about the habits that helped.</p>
                    @elseif($moodStableRegion === 'mid')
                        <p class="font-semibold">Steady middle zone, {{ $userName }}. Your mood stayed at level 3.</p>
                        <p class="text-sm mt-1">You are stable. A small shift like a short walk, breathing break, or early sleep can help move upward.</p>
                    @else
                        <p class="font-semibold">You stayed in a tough zone, {{ $userName }} (levels 1-2).</p>
                        <p class="text-sm mt-1">Thank you for checking in. Start with one gentle step and review supportive routines picked for lower-energy days.</p>
                    @endif
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ route('mood.dashboard') }}" class="inline-block bg-sky-600 text-white px-4 py-2 rounded text-sm hover:bg-sky-700">
                            Review Mood Patterns
                        </a>
                        <a href="{{ route('routines.recommendations') }}" class="inline-block bg-white border border-sky-300 text-sky-700 px-4 py-2 rounded text-sm hover:bg-sky-50">
                            Explore Helpful Routines
                        </a>
                    </div>
                </div>
            @endif

            {{-- Log mood form --}}
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">How are you feeling today?</h3>
                <form method="POST" action="{{ route('mood.store') }}">
                    @csrf

                    {{-- Emoji categories --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select your mood</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach($moodCategories as $key => $mood)
                                <label class="rounded-lg border border-gray-200 px-3 py-3 flex items-center gap-3 cursor-pointer hover:border-indigo-400 transition">
                                    <input
                                        type="radio"
                                        name="mood_category"
                                        value="{{ $key }}"
                                        class="text-indigo-600"
                                        @checked(old('mood_category') === $key)
                                        required
                                    >
                                    <span class="text-2xl">{{ $mood['emoji'] }}</span>
                                    <span class="text-sm font-medium text-gray-700">{{ $mood['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('mood_category')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Journal note --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Journal note <span class="text-gray-400">(optional)</span>
                        </label>
                        <textarea name="journal_note" rows="3"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                            placeholder="Write how you're feeling...">{{ old('journal_note') }}</textarea>
                    </div>

                    <button type="submit"
                        class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
                        Save mood entry
                    </button>
                </form>
            </div>

            {{-- Mood history --}}
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Your mood history</h3>
                @forelse($logs as $log)
                    <div class="flex items-center justify-between border-b py-2">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">{{ $log->mood_emoji }}</span>
                            <div>
                                <p class="text-sm font-semibold text-gray-700">{{ $log->mood_label }} <span class="text-xs text-gray-400">(Score {{ $log->mood_value }})</span></p>
                                <span class="text-sm text-gray-600">{{ $log->journal_note ?? 'No note' }}</span>
                            </div>
                        </div>
                        <span class="text-xs text-gray-400">{{ optional($log->logged_at)->format('M d, Y h:i A') }}</span>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No mood entries yet. Log your first mood above!</p>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
