<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Mood Dashboard
            </h2>

            <div class="flex items-center gap-2">
                <a
                    href="{{ route('mood.index') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                >
                    Back To Mood Logging
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm rounded-lg p-4 section-animate">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <form method="GET" action="{{ route('mood.dashboard') }}" class="flex flex-wrap items-center gap-3">
                        <label for="period" class="text-sm font-medium text-gray-700">Analytics period</label>
                        <select id="period" name="period" class="rounded border-gray-300 text-sm">
                            @foreach($periodOptions as $key => $label)
                                <option value="{{ $key }}" @selected($period === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button
                            type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                        >
                            Apply
                        </button>
                    </form>

                    <div class="flex flex-wrap items-center gap-2">
                        <a
                            href="{{ route('mood.export.csv', ['period' => $period]) }}"
                            class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700"
                        >
                            Export CSV
                        </a>
                        <a
                            href="{{ route('mood.export.pdf', ['period' => $period]) }}"
                            class="inline-flex items-center px-4 py-2 bg-rose-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-rose-700"
                        >
                            Export PDF
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 section-animate" data-stagger="true">
                <div class="bg-white shadow-sm rounded-lg p-4 stat-card">
                    <p class="text-xs uppercase text-gray-500">Total Entries</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1" data-count-up="{{ $totalEntries }}">{{ $totalEntries }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 stat-card">
                    <p class="text-xs uppercase text-gray-500">Average Mood Score</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1" data-count-up="{{ $averageScore }}">{{ $averageScore }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 stat-card">
                    <p class="text-xs uppercase text-gray-500">Positive Mood Rate</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1" data-count-up="{{ $positiveRate }}" data-suffix="%">{{ $positiveRate }}%</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 stat-card">
                    <p class="text-xs uppercase text-gray-500">Most Frequent Mood</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ $mostFrequentMood }}</p>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6 section-animate">
                <h3 class="text-lg font-semibold text-gray-800">Emotional Summary</h3>
                <p class="text-sm text-gray-600 mt-2">{{ $trendSummary }}</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 section-animate">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Mood Trend Chart</h3>
                    <canvas id="moodTrendChart" height="180"></canvas>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Mood Category Distribution</h3>
                    <canvas id="moodDistributionChart" height="180"></canvas>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 section-animate">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Behavior Pattern Analysis (Day)</h3>
                    <div class="space-y-2">
                        @forelse($dayOfWeekPattern as $day => $score)
                            <div class="flex items-center justify-between text-sm border-b border-gray-100 pb-2">
                                <span class="text-gray-600">{{ $day }}</span>
                                <span class="font-semibold text-gray-800">Avg score: {{ $score }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Not enough data yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Behavior Pattern Analysis (Time of day)</h3>
                    <div class="space-y-2">
                        @forelse($timeOfDayPattern as $range => $score)
                            <div class="flex items-center justify-between text-sm border-b border-gray-100 pb-2">
                                <span class="text-gray-600">{{ $range }}</span>
                                <span class="font-semibold text-gray-800">Avg score: {{ $score }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Not enough data yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-6 section-animate">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Mood History</h3>
                <div class="space-y-3">
                    @forelse($logs as $log)
                        <div class="border border-gray-100 rounded-md px-4 py-3 flex items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold text-gray-800">{{ $log->mood_emoji }} {{ $log->mood_label }}</p>
                                <p class="text-sm text-gray-600">{{ $log->journal_note ?? 'No note added.' }}</p>
                            </div>
                            <p class="text-xs text-gray-400">{{ optional($log->logged_at)->format('M d, Y h:i A') }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No mood history found yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <style>
        .section-animate {
            opacity: 0;
            transform: translateY(14px);
            transition: opacity 500ms ease, transform 500ms ease;
        }

        .section-animate.is-visible {
            opacity: 1;
            transform: translateY(0);
        }

        .stat-card {
            transition: transform 250ms ease, box-shadow 250ms ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(17, 24, 39, 0.08);
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const animatedSections = document.querySelectorAll('.section-animate');

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                threshold: 0.12,
            });

            animatedSections.forEach((section, index) => {
                section.style.transitionDelay = `${index * 60}ms`;
                observer.observe(section);
            });
        } else {
            animatedSections.forEach((section) => section.classList.add('is-visible'));
        }

        function animateCount(element) {
            const target = Number(element.dataset.countUp || '0');
            const suffix = element.dataset.suffix || '';
            const isDecimal = String(target).includes('.');
            const duration = 900;
            const start = performance.now();

            function frame(now) {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                const current = target * eased;
                element.textContent = (isDecimal ? current.toFixed(2) : Math.round(current)) + suffix;

                if (progress < 1) {
                    requestAnimationFrame(frame);
                } else {
                    element.textContent = (isDecimal ? target.toFixed(2) : Math.round(target)) + suffix;
                }
            }

            requestAnimationFrame(frame);
        }

        document.querySelectorAll('[data-count-up]').forEach((element) => animateCount(element));

        const trendContext = document.getElementById('moodTrendChart');
        const distributionContext = document.getElementById('moodDistributionChart');

        const trendDates = @json($chartDates);
        const trendScores = @json($chartScores);

        const moodLabels = @json($chartMoodLabels);
        const moodCounts = @json($chartMoodCounts);

        if (trendContext) {
            new Chart(trendContext, {
                type: 'line',
                data: {
                    labels: trendDates,
                    datasets: [{
                        label: 'Average Mood Score',
                        data: trendScores,
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.2)',
                        tension: 0.35,
                        fill: true,
                    }],
                },
                options: {
                    responsive: true,
                    animation: {
                        duration: 1200,
                    },
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

        if (distributionContext) {
            new Chart(distributionContext, {
                type: 'bar',
                data: {
                    labels: moodLabels,
                    datasets: [{
                        label: 'Entries',
                        data: moodCounts,
                        backgroundColor: [
                            '#10b981', '#3b82f6', '#ef4444', '#f59e0b',
                            '#8b5cf6', '#06b6d4', '#ec4899', '#64748b',
                        ],
                    }],
                },
                options: {
                    responsive: true,
                    animation: {
                        duration: 1200,
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                            },
                        },
                    },
                },
            });
        }
    </script>
</x-app-layout>
