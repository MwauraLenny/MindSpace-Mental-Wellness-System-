<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Journal Entries
            </h2>

            <a
                href="{{ route('journals.create') }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
            >
                New Entry
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('success'))
                <div class="bg-green-100 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @forelse($journals as $journal)
                <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">{{ $journal->title }}</h3>
                            <p class="text-xs text-gray-500 mt-1">
                                Date created: {{ optional($journal->created_at)->format('M d, Y h:i A') }}
                            </p>
                            <p class="text-sm text-gray-600 mt-3">{{ \Illuminate\Support\Str::limit($journal->body, 180) }}</p>

                            @if($journal->moodLog)
                                <p class="text-sm text-indigo-700 mt-3">
                                    Related mood: {{ $journal->moodLog->mood_emoji }} {{ $journal->moodLog->mood_label }}
                                </p>
                            @else
                                <p class="text-sm text-gray-500 mt-3">Related mood: none</p>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            <a
                                href="{{ route('journals.show', $journal->id) }}"
                                class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                            >
                                View
                            </a>

                            <a
                                href="{{ route('journals.edit', $journal->id) }}"
                                class="inline-flex items-center px-3 py-2 bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800"
                            >
                                Edit
                            </a>

                            <form method="POST" action="{{ route('journals.destroy', $journal->id) }}" onsubmit="return confirm('Delete this journal entry?');">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700"
                                >
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-sm rounded-lg p-8 text-center text-gray-500">
                    No journal entries yet. Create your first one.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
