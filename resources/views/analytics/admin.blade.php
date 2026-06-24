<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Platform Analytics
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <section class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <p class="text-xs uppercase text-gray-500">Active Users</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $activeUsers }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ $activeUsersWindowLabel }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <p class="text-xs uppercase text-gray-500">Engagement Rate</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $engagementRate }}%</p>
                    <p class="text-sm text-gray-500 mt-1">Community engagement users / total users</p>
                </div>
            </section>

            <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800">Routine Popularity</h3>
                    <p class="text-sm text-gray-600 mt-1">Engagement score based on likes, saves, reactions, and comments.</p>
                    <canvas id="routinePopularityChart" class="mt-4" height="180"></canvas>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800">Most Common Emotions</h3>
                    <canvas id="emotionChart" class="mt-4" height="180"></canvas>
                </div>
            </section>

            <section class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800">Routine Popularity Table</h3>
                <div class="mt-4 space-y-2">
                    @forelse($routinePopularity as $routine)
                        <div class="rounded border border-gray-100 px-4 py-3 text-sm">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-semibold text-gray-800">{{ $routine['title'] }}</p>
                                <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded">Score: {{ $routine['popularity_score'] }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Likes: {{ $routine['likes_count'] }} · Saves: {{ $routine['saves_count'] }} · Reactions: {{ $routine['reactions_count'] }} · Comments: {{ $routine['comments_count'] }}
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No routine engagement data yet.</p>
                    @endforelse
                </div>
            </section>

            <section class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800">Emotion Frequency</h3>
                <div class="mt-4 space-y-2">
                    @forelse($mostCommonEmotions as $emotion)
                        <div class="rounded border border-gray-100 px-4 py-3 text-sm flex items-center justify-between gap-3">
                            <p class="text-gray-700">{{ $emotion['emoji'] }} {{ $emotion['label'] }}</p>
                            <span class="font-semibold text-gray-800">{{ $emotion['total'] }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No mood logs available yet.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const routinePopularityLabels = @json($routinePopularityLabels);
        const routinePopularityScores = @json($routinePopularityScores);
        const emotionLabels = @json($emotionLabels);
        const emotionTotals = @json($emotionTotals);

        const routineCanvas = document.getElementById('routinePopularityChart');
        if (routineCanvas) {
            new Chart(routineCanvas, {
                type: 'bar',
                data: {
                    labels: routinePopularityLabels,
                    datasets: [{
                        label: 'Popularity Score',
                        data: routinePopularityScores,
                        backgroundColor: '#4f46e5',
                    }],
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 },
                        },
                    },
                },
            });
        }

        const emotionCanvas = document.getElementById('emotionChart');
        if (emotionCanvas) {
            new Chart(emotionCanvas, {
                type: 'polarArea',
                data: {
                    labels: emotionLabels,
                    datasets: [{
                        data: emotionTotals,
                        backgroundColor: ['#22c55e', '#3b82f6', '#ef4444', '#eab308', '#14b8a6', '#8b5cf6', '#f97316', '#64748b'],
                    }],
                },
                options: {
                    responsive: true,
                },
            });
        }
    </script>
</x-app-layout>
