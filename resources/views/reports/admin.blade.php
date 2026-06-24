<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Admin Reports
            </h2>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.reports.export.csv') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700">
                    Export CSV
                </a>
                <a href="{{ route('admin.reports.export.pdf') }}" class="inline-flex items-center px-4 py-2 bg-rose-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-rose-700">
                    Export PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <section class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800">User Statistics</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div class="rounded border border-gray-100 p-4">
                        <p class="text-xs uppercase text-gray-500">Total Users</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $totalUsers }}</p>
                    </div>
                    <div class="rounded border border-gray-100 p-4">
                        <p class="text-xs uppercase text-gray-500">Active Users ({{ $activeWindowLabel }})</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $activeUsers }}</p>
                    </div>
                </div>
            </section>

            <section class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800">Most Common Moods</h3>
                <div class="space-y-2 mt-4">
                    @forelse($mostCommonMoods as $mood)
                        <div class="flex items-center justify-between rounded border border-gray-100 px-4 py-3 text-sm">
                            <span class="text-gray-700">{{ $mood['label'] }}</span>
                            <span class="font-semibold text-gray-900">{{ $mood['total'] }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No mood data yet.</p>
                    @endforelse
                </div>
            </section>

            <section class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800">Most Liked Routines</h3>
                <div class="space-y-2 mt-4">
                    @forelse($mostLikedRoutines as $routine)
                        <div class="flex items-center justify-between rounded border border-gray-100 px-4 py-3 text-sm gap-3">
                            <div>
                                <p class="font-semibold text-gray-800">{{ $routine->display_title }}</p>
                                <p class="text-xs text-gray-500">{{ $routine->is_anonymous ? 'Anonymous user' : $routine->user?->name }}</p>
                            </div>
                            <span class="font-semibold text-gray-900">{{ $routine->likes_count }} likes</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No routines available yet.</p>
                    @endforelse
                </div>
            </section>

            <section class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800">Reported Content Statistics</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-4 text-sm">
                    <div class="rounded border border-gray-100 px-4 py-3">Total reports: <span class="font-semibold">{{ $reportStats['total'] }}</span></div>
                    <div class="rounded border border-gray-100 px-4 py-3">Pending: <span class="font-semibold">{{ $reportStats['pending'] }}</span></div>
                    <div class="rounded border border-gray-100 px-4 py-3">Resolved: <span class="font-semibold">{{ $reportStats['resolved'] }}</span></div>
                    <div class="rounded border border-gray-100 px-4 py-3">Dismissed: <span class="font-semibold">{{ $reportStats['dismissed'] }}</span></div>
                </div>

                <div class="space-y-2 mt-4">
                    @forelse($reportStats['by_type'] as $typeStat)
                        <div class="flex items-center justify-between rounded border border-gray-100 px-4 py-3 text-sm">
                            <span class="text-gray-700">{{ class_basename($typeStat->reportable_type) }}</span>
                            <span class="font-semibold text-gray-900">{{ $typeStat->total }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No reported content yet.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
