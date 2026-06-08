<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Log Your Mood
        </h2>
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
                    <p class="font-semibold">Your mood has improved! 🎉</p>
                    <p class="text-sm mt-1">Would you like to share what helped you with the community?</p>
                    <a href="/routines/create" class="mt-2 inline-block bg-yellow-500 text-white px-4 py-2 rounded text-sm">
                        Yes, share my routine
                    </a>
                </div>
            @endif

            {{-- Log mood form --}}
            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">How are you feeling today?</h3>
                <form method="POST" action="{{ route('mood.store') }}">
                    @csrf

                    {{-- Emoji scale --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select your mood</label>
                        <div class="flex gap-4">
                            @foreach([1 => '😢', 2 => '😟', 3 => '😐', 4 => '🙂', 5 => '😄'] as $value => $emoji)
                                <label class="flex flex-col items-center cursor-pointer">
                                    <input type="radio" name="mood_value" value="{{ $value }}"
                                        class="mb-1" required>
                                    <span class="text-3xl">{{ $emoji }}</span>
                                    <span class="text-xs text-gray-500">{{ $value }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('mood_value')
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
                            <span class="text-2xl">
                                {{ [1=>'😢',2=>'😟',3=>'😐',4=>'🙂',5=>'😄'][$log->mood_value] }}
                            </span>
                            <span class="text-sm text-gray-600">{{ $log->journal_note ?? 'No note' }}</span>
                        </div>
                        <span class="text-xs text-gray-400">{{ $log->logged_at }}</span>
                    </div>
                @empty
                    <p class="text-gray-500 text-sm">No mood entries yet. Log your first mood above!</p>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
