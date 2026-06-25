<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Welcome, {{ $userName }}
            </h2>

            <a
                href="{{ route('mood.dashboard') }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
            >
                Full Mood Analytics
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @php
                $previousMood = $moodHistory->skip(1)->first();
            @endphp

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Streak Status</h3>
                        <p class="text-sm text-gray-600 mt-1">Keep checking in daily to strengthen your momentum.</p>
                    </div>
                    <a
                        href="{{ route('mood.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700"
                    >
                        Log Mood
                    </a>
                </div>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <article class="rounded-md border border-orange-100 bg-orange-50/60 p-4">
                        <p class="text-sm text-orange-800 font-medium">Current Streak</p>
                        <p class="text-2xl font-bold text-orange-900 mt-1">{{ $currentStreak }} day{{ $currentStreak === 1 ? '' : 's' }} 🔥</p>
                        <p class="text-xs text-orange-700 mt-1">Tier: {{ $streakTierLabel }}</p>
                    </article>
                    <article class="rounded-md border border-indigo-100 bg-indigo-50/60 p-4">
                        <p class="text-sm text-indigo-800 font-medium">Longest Streak</p>
                        <p class="text-2xl font-bold text-indigo-900 mt-1">{{ $longestStreak }} day{{ $longestStreak === 1 ? '' : 's' }}</p>
                    </article>
                    <article class="rounded-md border border-emerald-100 bg-emerald-50/60 p-4">
                        <p class="text-sm text-emerald-800 font-medium">Mood Pages</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <a href="{{ route('mood.index') }}" class="inline-block bg-white border border-emerald-300 text-emerald-700 px-3 py-1 rounded text-xs hover:bg-emerald-50">Log Mood</a>
                            <a href="{{ route('mood.dashboard') }}" class="inline-block bg-white border border-emerald-300 text-emerald-700 px-3 py-1 rounded text-xs hover:bg-emerald-50">Analytics</a>
                        </div>
                    </article>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800">Welcome Back</h3>
                    <p class="text-sm text-gray-600 mt-1">{{ $userName }}, this page is your quick check-in hub.</p>

                    @if($latestMood)
                        <div class="mt-4 rounded-md border border-gray-200 p-4">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Latest Mood</p>
                            <p class="mt-1 text-lg font-semibold text-gray-800">{{ $latestMood->mood_emoji }} {{ $latestMood->mood_label }} <span class="text-sm text-gray-500">(Score {{ $latestMood->mood_value }})</span></p>
                            <p class="text-sm text-gray-600 mt-1">Logged {{ optional($latestMood->logged_at)->format('M d, Y h:i A') }}</p>
                        </div>
                    @else
                        <p class="mt-4 text-sm text-gray-500">No mood entries yet. Start with your first check-in.</p>
                    @endif

                    @if($previousMood)
                        <div class="mt-3 rounded-md border border-gray-100 bg-gray-50 p-4">
                            <p class="text-xs uppercase tracking-wide text-gray-500">Previous Mood</p>
                            <p class="mt-1 text-base font-semibold text-gray-800">{{ $previousMood->mood_emoji }} {{ $previousMood->mood_label }} <span class="text-sm text-gray-500">(Score {{ $previousMood->mood_value }})</span></p>
                            <p class="text-sm text-gray-600 mt-1">Logged {{ optional($previousMood->logged_at)->format('M d, Y h:i A') }}</p>
                        </div>
                    @endif
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800">Quick Actions</h3>
                    <p class="text-sm text-gray-600 mt-1">Use dedicated pages for deeper tasks.</p>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                        <a href="{{ route('mood.index') }}" class="rounded-md border border-emerald-200 bg-emerald-50/70 p-4 hover:bg-emerald-100/80 transition min-h-28 flex flex-col justify-between">
                            <p class="font-semibold text-emerald-800">Log Mood</p>
                            <p class="text-sm text-emerald-700 mt-1">Open the full mood logging page with personalized prompts.</p>
                        </a>
                        <a href="{{ route('mood.dashboard') }}" class="rounded-md border border-indigo-200 bg-indigo-50/70 p-4 hover:bg-indigo-100/80 transition min-h-28 flex flex-col justify-between">
                            <p class="font-semibold text-indigo-800">Mood Analytics</p>
                            <p class="text-sm text-indigo-700 mt-1">Open charts, trends, filters, and exports on the analytics page.</p>
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6" x-data="{ menuOpen: false, filter: 'unlocked' }">
                @php
                    $unlockedBadges = collect($achievementBadges)->where('unlocked', true)->values();
                    $lockedBadges = collect($achievementBadges)->where('unlocked', false)->values();
                @endphp

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Achievements</h3>
                        <p class="text-sm text-gray-600 mt-1">Track all 50 achievements and use locked goals as motivation.</p>
                    </div>

                    <div class="relative">
                        <button
                            type="button"
                            @click="menuOpen = !menuOpen"
                            class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                        >
                            Achievements
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div
                            x-show="menuOpen"
                            @click.outside="menuOpen = false"
                            class="absolute right-0 mt-2 w-56 rounded-md border border-gray-200 bg-white shadow-lg z-20"
                            style="display: none;"
                        >
                            <button
                                type="button"
                                @click="filter = 'unlocked'; menuOpen = false"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-emerald-50"
                            >
                                Unlocked ({{ $unlockedBadges->count() }})
                            </button>
                            <button
                                type="button"
                                @click="filter = 'locked'; menuOpen = false"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                            >
                                Locked ({{ $lockedBadges->count() }})
                            </button>
                        </div>
                    </div>
                </div>

                <p class="mt-3 text-xs text-gray-500" x-show="filter === 'unlocked'" style="display: none;">
                    Showing unlocked achievements.
                </p>
                <p class="mt-3 text-xs text-gray-500" x-show="filter === 'locked'" style="display: none;">
                    Showing locked achievements.
                </p>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3" x-show="filter === 'unlocked'" style="display: none;">
                    @forelse($unlockedBadges as $badge)
                        <article class="rounded-md border border-emerald-100 bg-emerald-50/60 p-4">
                            <p class="font-semibold text-emerald-800">{{ ($badge['emoji'] ?? '🏅').' '.$badge['title'] }}</p>
                            <p class="text-sm text-emerald-700 mt-1">{{ $badge['description'] }}</p>
                        </article>
                    @empty
                        <p class="text-sm text-gray-500">No unlocked achievements yet. Keep going.</p>
                    @endforelse
                </div>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3" x-show="filter === 'locked'" style="display: none;">
                    @forelse($lockedBadges as $badge)
                        <article class="rounded-md border border-gray-200 bg-gray-50 p-4 opacity-55">
                            <p class="font-semibold text-gray-600">{{ ($badge['emoji'] ?? '🏅').' '.$badge['title'] }}</p>
                            <p class="text-sm text-gray-500 mt-1">{{ $badge['description'] }}</p>
                        </article>
                    @empty
                        <p class="text-sm text-gray-500">All achievements unlocked. Excellent work.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
