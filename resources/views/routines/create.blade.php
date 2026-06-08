<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Share a Routine
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <p class="text-gray-600 text-sm mb-6">
                    Share what helped improve your mood. Your routine will be shown
                    to other users experiencing the same mood level.
                </p>

                <form method="POST" action="{{ route('routines.store') }}">
                    @csrf

                    {{-- Mood tag --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Which mood level did this help with?
                        </label>
                        <div class="flex gap-4">
                            @foreach([1=>'😢',2=>'😟',3=>'😐',4=>'🙂',5=>'😄'] as $value => $emoji)
                                <label class="flex flex-col items-center cursor-pointer">
                                    <input type="radio" name="mood_tag" value="{{ $value }}"
                                        {{ $latestLog && $latestLog->mood_value == $value ? 'checked' : '' }}
                                        required>
                                    <span class="text-3xl">{{ $emoji }}</span>
                                    <span class="text-xs text-gray-500">{{ $value }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('mood_tag')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Routine body --}}
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            What helped you?
                        </label>
                        <textarea name="body" rows="4"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm"
                            placeholder="e.g. I went for a 20 minute walk and listened to music..."
                            required>{{ old('body') }}</textarea>
                        @error('body')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Anonymous toggle --}}
                    <div class="mb-6 flex items-center gap-2">
                        <input type="checkbox" name="is_anonymous" id="is_anonymous" value="1"
                            {{ Auth::user()->anonymous_sharing ? 'checked' : '' }}>
                        <label for="is_anonymous" class="text-sm text-gray-600">
                            Post anonymously
                        </label>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit"
                            class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700">
                            Share with community
                        </button>
                        <a href="{{ route('routines.index') }}"
                            class="text-gray-500 px-4 py-2 rounded border hover:bg-gray-50">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
