<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Community Routines
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if(session('success'))
                <div class="bg-green-100 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if($moodFilter)
                <div class="bg-indigo-50 border border-indigo-200 text-indigo-700 px-4 py-3 rounded text-sm">
                    Showing routines matched to your current mood:
                    {{ [1=>'😢',2=>'😟',3=>'😐',4=>'🙂',5=>'😄'][$moodFilter] }} (level {{ $moodFilter }})
                </div>
            @endif

            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-700">What helped others</h3>
                <a href="{{ route('routines.create') }}"
                    class="bg-indigo-600 text-white px-4 py-2 rounded text-sm hover:bg-indigo-700">
                    + Share a routine
                </a>
            </div>

            @forelse($routines as $routine)
                <div class="bg-white shadow sm:rounded-lg p-5">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-sm font-medium text-gray-500">
                                {{ $routine->is_anonymous ? 'Anonymous user' : $routine->user->name }}
                                &middot; mood level {{ $routine->mood_tag }}
                                {{ [1=>'😢',2=>'😟',3=>'😐',4=>'🙂',5=>'😄'][$routine->mood_tag] }}
                            </p>
                            <p class="mt-2 text-gray-800">{{ $routine->body }}</p>
                        </div>
                        <span class="text-sm text-gray-400 ml-4">{{ $routine->upvote_count }} ▲</span>
                    </div>
                    <div class="mt-3 flex gap-2">
                        <form method="POST" action="{{ route('routines.upvote', $routine->id) }}">
                            @csrf
                            <button type="submit"
                                class="text-sm bg-indigo-100 text-indigo-700 px-3 py-1 rounded hover:bg-indigo-200">
                                ▲ Upvote
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white shadow sm:rounded-lg p-6 text-center text-gray-500">
                    No routines yet for this mood level.
                    <a href="{{ route('routines.create') }}" class="text-indigo-600 underline ml-1">
                        Be the first to share!
                    </a>
                </div>
            @endforelse

        </div>
    </div>
</x-app-layout>
