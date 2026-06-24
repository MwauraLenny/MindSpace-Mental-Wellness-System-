<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Personal Reports
            </h2>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('reports.personal.export.csv') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700">
                    Export CSV
                </a>
                <a href="{{ route('reports.personal.export.pdf') }}" class="inline-flex items-center px-4 py-2 bg-rose-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-rose-700">
                    Export PDF
                </a>
                <a href="{{ route('mood.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                    Open Mood Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <section class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800">Mood Report Overview</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
                    <div class="rounded border border-gray-100 p-4">
                        <p class="text-xs uppercase text-gray-500">Total Mood Entries</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $totalEntries }}</p>
                    </div>
                    <div class="rounded border border-gray-100 p-4">
                        <p class="text-xs uppercase text-gray-500">Average Mood Score</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $averageScore }}</p>
                    </div>
                    <div class="rounded border border-gray-100 p-4">
                        <p class="text-xs uppercase text-gray-500">Positive Mood Rate</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $positiveRate }}%</p>
                    </div>
                    <div class="rounded border border-gray-100 p-4">
                        <p class="text-xs uppercase text-gray-500">Most Common Mood</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $mostFrequentMood }}</p>
                    </div>
                </div>
            </section>

            <section class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800">Mood Trends</h3>
                <p class="text-sm text-gray-600 mt-2">{{ $trendSummary }}</p>
                <div class="mt-4">
                    <canvas id="userMoodTrendChart" height="120"></canvas>
                </div>
            </section>

            <section class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800">Emotional Statistics</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4">
                    @foreach($moodCounts as $key => $count)
                        <div class="flex items-center justify-between rounded border border-gray-100 px-4 py-3 text-sm">
                            <span class="text-gray-600">{{ $categories[$key]['emoji'] }} {{ $categories[$key]['label'] }}</span>
                            <span class="font-semibold text-gray-800">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800">Activity Summary</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mt-4 text-sm">
                    <div class="rounded border border-gray-100 px-4 py-3">Mood logs: <span class="font-semibold">{{ $activitySummary['mood_logs_total'] }}</span></div>
                    <div class="rounded border border-gray-100 px-4 py-3">Journal entries: <span class="font-semibold">{{ $activitySummary['journals_total'] }}</span></div>
                    <div class="rounded border border-gray-100 px-4 py-3">Routines shared: <span class="font-semibold">{{ $activitySummary['routines_shared_total'] }}</span></div>
                    <div class="rounded border border-gray-100 px-4 py-3">Comments posted: <span class="font-semibold">{{ $activitySummary['comments_total'] }}</span></div>
                    <div class="rounded border border-gray-100 px-4 py-3">Likes given: <span class="font-semibold">{{ $activitySummary['likes_given_total'] }}</span></div>
                    <div class="rounded border border-gray-100 px-4 py-3">Saved routines: <span class="font-semibold">{{ $activitySummary['saves_total'] }}</span></div>
                    <div class="rounded border border-gray-100 px-4 py-3">Reactions posted: <span class="font-semibold">{{ $activitySummary['reactions_total'] }}</span></div>
                    <div class="rounded border border-gray-100 px-4 py-3">Mood logs (7d): <span class="font-semibold">{{ $activitySummary['mood_logs_7d'] }}</span></div>
                    <div class="rounded border border-gray-100 px-4 py-3">Journals (7d): <span class="font-semibold">{{ $activitySummary['journals_7d'] }}</span></div>
                </div>
            </section>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const trendContext = document.getElementById('userMoodTrendChart');
        const trendDates = @json($dailyMoodTrendDates);
        const trendScores = @json($dailyMoodTrendScores);

        if (trendContext) {
            new Chart(trendContext, {
                type: 'line',
                data: {
                    labels: trendDates,
                    datasets: [{
                        label: 'Average Mood Score',
                        data: trendScores,
                        borderColor: '#0f766e',
                        backgroundColor: 'rgba(15, 118, 110, 0.15)',
                        fill: true,
                        tension: 0.3,
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
