<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Journal Entry
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('journals.update', $journal->id) }}" class="space-y-5">
                    @csrf
                    @method('PATCH')

                    <div>
                        <x-input-label for="title" :value="__('Entry title')" />
                        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $journal->title)" required />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="content" :value="__('Entry content')" />
                        <textarea
                            id="content"
                            name="content"
                            rows="7"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >{{ old('content', $journal->body) }}</textarea>
                        <x-input-error :messages="$errors->get('content')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="related_mood_log_id" :value="__('Related mood (optional)')" />
                        <select id="related_mood_log_id" name="related_mood_log_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">No related mood</option>
                            @foreach($moodLogs as $moodLog)
                                <option value="{{ $moodLog->id }}" @selected((string) old('related_mood_log_id', $journal->mood_log_id) === (string) $moodLog->id)>
                                    {{ $moodLog->mood_emoji }} {{ $moodLog->mood_label }} - {{ optional($moodLog->logged_at)->format('M d, Y h:i A') }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('related_mood_log_id')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('journals.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest text-gray-700 hover:bg-gray-300">
                            Cancel
                        </a>
                        <x-primary-button>
                            Update Entry
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
