<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Journal Entry Details
            </h2>

            <a
                href="{{ route('journals.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800"
            >
                Back To Entries
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6 space-y-5">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Entry title</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ $journal->title }}</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Date created</p>
                        <p class="text-sm text-gray-700 mt-1">{{ optional($journal->created_at)->format('M d, Y h:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Entry date</p>
                        <p class="text-sm text-gray-700 mt-1">{{ optional($journal->entry_date)->format('M d, Y') }}</p>
                    </div>
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Related mood</p>
                    @if($journal->moodLog)
                        <p class="text-sm text-indigo-700 mt-1">
                            {{ $journal->moodLog->mood_emoji }} {{ $journal->moodLog->mood_label }}
                            <span class="text-gray-500">(Score {{ $journal->moodLog->mood_value }})</span>
                        </p>
                    @else
                        <p class="text-sm text-gray-500 mt-1">No related mood attached.</p>
                    @endif
                </div>

                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500">Entry content</p>
                    <div class="mt-2 rounded-md border border-gray-200 bg-gray-50 p-4 whitespace-pre-wrap text-sm text-gray-700">{{ $journal->body }}</div>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <a
                        href="{{ route('journals.edit', $journal->id) }}"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                    >
                        Edit Entry
                    </a>

                    <form method="POST" action="{{ route('journals.destroy', $journal->id) }}" onsubmit="return confirm('Delete this journal entry?');">
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700"
                        >
                            Delete Entry
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
