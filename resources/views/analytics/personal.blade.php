<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Personal Analytics
            </h2>
            <a href="{{ route('mood.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                Open Mood Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <section class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800">Mood Frequency</h3>
                <p class="text-sm text-gray-600 mt-1">Total entries: {{ $totalMoodEntries }}</p>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    @forelse($moodFrequency as $item)
                        <div class="rounded border border-gray-100 px-4 py-3">
                            <p class="text-sm text-gray-600">{{ $item['emoji'] }} {{ $item['label'] }}</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $item['count'] }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No mood data yet.</p>
                    @endforelse
                </div>
            </section>

            <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800">Mood Distribution</h3>
                    <canvas id="moodDistributionChart" class="mt-4" height="180"></canvas>
                    <div class="mt-4 space-y-2 text-sm">
                        @foreach($moodDistribution as $item)
                            <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                                <span class="text-gray-600">{{ $item['emoji'] }} {{ $item['label'] }}</span>
                                <span class="font-semibold text-gray-800">{{ $item['percentage'] }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800">Mood Trends</h3>
                    <p class="text-sm text-gray-600 mt-1">Daily average mood score over recent entries.</p>
                    <canvas id="moodTrendChart" class="mt-4" height="180"></canvas>
                </div>
            </section>

            <section class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800">Most Used Routines</h3>
                <p class="text-sm text-gray-600 mt-1">Based on your likes, saves, reactions, and comments.</p>

                <div class="mt-4 space-y-3">
                    @forelse($mostUsedRoutines as $routine)
                        <article class="rounded border border-gray-100 px-4 py-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $routine['title'] }}</p>
                                    <p class="text-sm text-gray-600 mt-1">{{ \Illuminate\Support\Str::limit($routine['body'] ?? 'No description.', 120) }}</p>
                                </div>
                                <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded">Score: {{ $routine['usage_count'] }}</span>
                            </div>
                            <div class="mt-2 text-xs text-gray-500">
                                Likes: {{ $routine['likes_used'] }} · Saves: {{ $routine['saves_used'] }} · Reactions: {{ $routine['reactions_used'] }} · Comments: {{ $routine['comments_used'] }}
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-gray-500">Routine usage data will appear when you engage with community routines.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const moodDistributionLabels = @json($moodDistributionLabels);
        const moodDistributionCounts = @json($moodDistributionCounts);
        const moodTrendDates = @json($moodTrendDates);
        const moodTrendScores = @json($moodTrendScores);

        const distributionCanvas = document.getElementById('moodDistributionChart');
        if (distributionCanvas) {
            new Chart(distributionCanvas, {
                type: 'doughnut',
                data: {
                    labels: moodDistributionLabels,
                    datasets: [{
                        data: moodDistributionCounts,
                        backgroundColor: ['#22c55e', '#3b82f6', '#ef4444', '#eab308', '#14b8a6', '#8b5cf6', '#f97316', '#64748b'],
                    }],
                },
                options: {
                    responsive: true,
                },
            });
        }

        const trendCanvas = document.getElementById('moodTrendChart');
        if (trendCanvas) {
            new Chart(trendCanvas, {
                type: 'line',
                data: {
                    labels: moodTrendDates,
                    datasets: [{
                        label: 'Average Mood Score',
                        data: moodTrendScores,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.15)',
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
                            ticks: { stepSize: 1 },
                        },
                    },
                },
            });
        }
    </script>
</x-app-layout>
