<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Wellness Dashboard
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
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800">Mood Tracker</h3>
                    <p class="text-sm text-gray-600 mt-1">Log your current mood quickly from the dashboard.</p>

                    <form method="POST" action="{{ route('mood.store') }}" class="mt-4 space-y-3">
                        @csrf

                        <div>
                            <label for="mood_category" class="block text-sm font-medium text-gray-700">Mood</label>
                            <select id="mood_category" name="mood_category" class="mt-1 block w-full rounded-md border-gray-300" required>
                                @foreach($moodCategories as $key => $meta)
                                    <option value="{{ $key }}">{{ $meta['emoji'] }} {{ $meta['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="journal_note" class="block text-sm font-medium text-gray-700">Journal Note (optional)</label>
                            <textarea id="journal_note" name="journal_note" rows="3" class="mt-1 block w-full rounded-md border-gray-300" placeholder="How are you feeling right now?"></textarea>
                        </div>

                        <button
                            type="submit"
                            class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700"
                        >
                            Save Mood Entry
                        </button>
                    </form>

                    @if($latestMood)
                        <p class="mt-4 text-sm text-gray-600">
                            Latest mood: <span class="font-semibold text-gray-800">{{ $latestMood->mood_emoji }} {{ $latestMood->mood_label }}</span>
                            on {{ optional($latestMood->logged_at)->format('M d, Y h:i A') }}
                        </p>
                    @endif
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800">Mood History</h3>
                    <p class="text-sm text-gray-600 mt-1">Your latest entries and notes.</p>

                    <div class="mt-4 space-y-3 max-h-72 overflow-y-auto pr-1">
                        @forelse($moodHistory as $log)
                            <div class="border border-gray-100 rounded-md px-4 py-3">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="font-semibold text-gray-800">{{ $log->mood_emoji }} {{ $log->mood_label }}</p>
                                    <p class="text-xs text-gray-400">{{ optional($log->logged_at)->format('M d, Y h:i A') }}</p>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">{{ $log->journal_note ?: 'No note added.' }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No mood entries yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800">Charts</h3>
                    <p class="text-sm text-gray-600 mt-1">Daily mood trend.</p>
                    <canvas id="userMoodTrendChart" class="mt-4" height="180"></canvas>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800">Mood Distribution</h3>
                    <p class="text-sm text-gray-600 mt-1">How your moods are distributed recently.</p>
                    <canvas id="userMoodDistributionChart" class="mt-4" height="180"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800">Recommendations</h3>
                    <p class="text-sm text-gray-600 mt-1">Community routines aligned with your mood context.</p>

                    <div class="mt-4 space-y-3">
                        @forelse($recommendations as $routine)
                            <div class="border border-gray-100 rounded-md p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $routine->display_title }}</p>
                                        <p class="text-xs text-gray-500 mt-1">By {{ $routine->user?->name ?? 'Community member' }}</p>
                                    </div>
                                    <span class="text-xs bg-emerald-50 text-emerald-700 px-2 py-1 rounded-full">Likes: {{ $routine->likes_count }}</span>
                                </div>
                                <p class="text-sm text-gray-600 mt-2">{{ \Illuminate\Support\Str::limit($routine->body, 120) }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No recommendations available yet.</p>
                        @endforelse
                    </div>

                    <a
                        href="{{ route('community.feed') }}"
                        class="mt-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                    >
                        Explore Community
                    </a>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800">Notifications</h3>
                    <p class="text-sm text-gray-600 mt-1">Recent personal and community notifications.</p>

                    <div class="mt-4 space-y-3">
                        @forelse($notifications as $notification)
                            <div class="border border-gray-100 rounded-md p-4 {{ $notification->read_at ? 'bg-white' : 'bg-blue-50/30' }}">
                                <p class="font-medium text-gray-800">{{ $notification->title }}</p>
                                <p class="text-sm text-gray-600 mt-1">{{ $notification->message }}</p>
                                <p class="text-xs text-gray-400 mt-2">{{ optional($notification->created_at)->diffForHumans() }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No notifications yet.</p>
                        @endforelse
                    </div>

                    <a
                        href="{{ route('notifications.index') }}"
                        class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                    >
                        Open Notifications
                    </a>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800">Achievements</h3>
                <p class="text-sm text-gray-600 mt-1">Badges unlocked through healthy consistency and community participation.</p>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse($achievementBadges as $badge)
                        <article class="rounded-md border border-emerald-100 bg-emerald-50/60 p-4">
                            <p class="font-semibold text-emerald-800">{{ $badge['title'] }}</p>
                            <p class="text-sm text-emerald-700 mt-1">{{ $badge['description'] }}</p>
                        </article>
                    @empty
                        <p class="text-sm text-gray-500">No badges unlocked yet. Keep logging moods and participating in the community.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800">Community Activity</h3>
                <p class="text-sm text-gray-600 mt-1">Latest routines being shared across the community.</p>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @forelse($communityActivity as $item)
                        <article class="border border-gray-100 rounded-md p-4">
                            <p class="font-semibold text-gray-800">{{ $item->display_title }}</p>
                            <p class="text-xs text-gray-500 mt-1">By {{ $item->user?->name ?? 'Community member' }}</p>
                            <p class="text-sm text-gray-600 mt-2">{{ \Illuminate\Support\Str::limit($item->body, 100) }}</p>
                            <div class="mt-3 flex items-center gap-3 text-xs text-gray-500">
                                <span>👍 {{ $item->likes_count }}</span>
                                <span>💬 {{ $item->comments_count }}</span>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-gray-500">No community activity yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const trendCanvas = document.getElementById('userMoodTrendChart');
        const distributionCanvas = document.getElementById('userMoodDistributionChart');

        const trendDates = @json($moodTrendDates);
        const trendScores = @json($moodTrendScores);
        const distributionLabels = @json($moodDistributionLabels);
        const distributionValues = @json($moodDistributionValues);

        if (trendCanvas) {
            new Chart(trendCanvas, {
                type: 'line',
                data: {
                    labels: trendDates,
                    datasets: [{
                        label: 'Mood score',
                        data: trendScores,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.2)',
                        fill: true,
                        tension: 0.35,
                    }],
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            min: 1,
                            max: 5,
                            ticks: {
                                stepSize: 1,
                            },
                        },
                    },
                },
            });
        }

        if (distributionCanvas) {
            new Chart(distributionCanvas, {
                type: 'doughnut',
                data: {
                    labels: distributionLabels,
                    datasets: [{
                        data: distributionValues,
                        backgroundColor: [
                            '#10b981', '#3b82f6', '#ef4444', '#f59e0b',
                            '#14b8a6', '#e11d48', '#6366f1', '#64748b',
                        ],
                    }],
                },
                options: {
                    responsive: true,
                },
            });
        }
    </script>
</x-app-layout>
