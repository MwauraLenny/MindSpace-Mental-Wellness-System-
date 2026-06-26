<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-2xl font-semibold tracking-tight text-stone-800 leading-tight">
                Admin Reports
            </h2>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.moderation.index') }}" class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-lg font-semibold text-[11px] text-white uppercase tracking-[0.08em] hover:bg-amber-700">
                    Open Moderation
                </a>
                <a href="{{ route('admin.reports.export.csv') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-lg font-semibold text-[11px] text-white uppercase tracking-[0.08em] hover:bg-emerald-700">
                    Export CSV
                </a>
                <a href="{{ route('admin.reports.export.pdf') }}" class="inline-flex items-center px-4 py-2 bg-rose-600 border border-transparent rounded-lg font-semibold text-[11px] text-white uppercase tracking-[0.08em] hover:bg-rose-700">
                    Export PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10 bg-gradient-to-b from-amber-50 via-orange-50 to-rose-100/70">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <section class="bg-white/95 shadow-sm ring-1 ring-amber-100 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800">User Statistics</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div class="rounded-xl border border-amber-200/70 bg-amber-50/50 p-4">
                        <p class="text-xs uppercase text-gray-500">Total Users</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $totalUsers }}</p>
                    </div>
                    <div class="rounded-xl border border-orange-200/70 bg-orange-50/60 p-4">
                        <p class="text-xs uppercase text-gray-500">Active Users ({{ $activeWindowLabel }})</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $activeUsers }}</p>
                    </div>
                </div>
            </section>

            <section class="bg-white/95 shadow-sm ring-1 ring-amber-100 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800">Most Common Moods</h3>
                <div class="space-y-2 mt-4">
                    @forelse($mostCommonMoods as $mood)
                        <div class="flex items-center justify-between rounded-lg border border-amber-200/70 bg-amber-50/40 px-4 py-3 text-sm">
                            <span class="text-gray-700">{{ $mood['label'] }}</span>
                            <span class="font-semibold text-gray-900">{{ $mood['total'] }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No mood data yet.</p>
                    @endforelse
                </div>
            </section>

            <section class="bg-white/95 shadow-sm ring-1 ring-amber-100 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800">Most Liked Routines</h3>
                <div class="space-y-2 mt-4">
                    @forelse($mostLikedRoutines as $routine)
                        <div class="flex items-center justify-between rounded-lg border border-orange-200/70 bg-orange-50/40 px-4 py-3 text-sm gap-3">
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

            <section class="bg-white/95 shadow-sm ring-1 ring-amber-100 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800">Reported Content Statistics</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-4 text-sm">
                    <div class="rounded-lg border border-amber-200/70 bg-amber-50/50 px-4 py-3">Total reports: <span class="font-semibold">{{ $reportStats['total'] }}</span></div>
                    <div class="rounded-lg border border-amber-200/70 bg-amber-50/50 px-4 py-3">Pending: <span class="font-semibold">{{ $reportStats['pending'] }}</span></div>
                    <div class="rounded-lg border border-emerald-200/70 bg-emerald-50/50 px-4 py-3">Resolved: <span class="font-semibold">{{ $reportStats['resolved'] }}</span></div>
                    <div class="rounded-lg border border-slate-200/80 bg-slate-50/70 px-4 py-3">Dismissed: <span class="font-semibold">{{ $reportStats['dismissed'] }}</span></div>
                </div>

                <div class="space-y-2 mt-4">
                    @forelse($reportStats['by_type'] as $typeStat)
                        <div class="flex items-center justify-between rounded-lg border border-orange-200/70 bg-orange-50/40 px-4 py-3 text-sm">
                            <span class="text-gray-700">{{ class_basename($typeStat->reportable_type) }}</span>
                            <span class="font-semibold text-gray-900">{{ $typeStat->total }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No reported content yet.</p>
                    @endforelse
                </div>
            </section>

            <section class="bg-white/95 shadow-sm ring-1 ring-amber-100 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800">Moderation Status</h3>
                <p class="text-sm text-gray-600 mt-1">Use the dedicated moderation page to review and process reported content.</p>
                <div class="mt-4 rounded-lg border border-amber-200/70 bg-amber-50/50 px-4 py-3 text-sm text-gray-700">
                    Pending reports in queue: <span class="font-semibold text-gray-900">{{ $pendingReports->count() }}</span>
                </div>
                <div class="mt-4">
                    <a href="{{ route('admin.moderation.index') }}" class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-lg font-semibold text-[11px] text-white uppercase tracking-[0.08em] hover:bg-amber-700">
                        Go To Moderation Queue
                    </a>
                </div>
            </section>

            <section class="bg-white/95 shadow-sm ring-1 ring-amber-100 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800">Monitor Activity</h3>
                <p class="text-sm text-gray-600 mt-1">Moderation throughput and safety trends.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mt-4 text-sm">
                    <div class="rounded-lg border border-orange-200/70 bg-orange-50/40 px-4 py-3">Reports (7d): <span class="font-semibold">{{ $monitoringMetrics['reports_7d'] }}</span></div>
                    <div class="rounded-lg border border-emerald-200/70 bg-emerald-50/50 px-4 py-3">Resolved (7d): <span class="font-semibold">{{ $monitoringMetrics['resolved_7d'] }}</span></div>
                    <div class="rounded-lg border border-slate-200/80 bg-slate-50/70 px-4 py-3">Dismissed (7d): <span class="font-semibold">{{ $monitoringMetrics['dismissed_7d'] }}</span></div>
                    <div class="rounded-lg border border-rose-200/70 bg-rose-50/50 px-4 py-3">Removed Routines: <span class="font-semibold">{{ $monitoringMetrics['removed_routines_total'] }}</span></div>
                    <div class="rounded-lg border border-rose-200/70 bg-rose-50/50 px-4 py-3">Removed Comments: <span class="font-semibold">{{ $monitoringMetrics['removed_comments_total'] }}</span></div>
                </div>

                <div class="mt-5">
                    <h4 class="font-semibold text-gray-800">Recent Moderation Actions</h4>
                    <div class="space-y-2 mt-3">
                        @forelse($recentModerationActions as $action)
                            <div class="rounded-lg border border-orange-200/70 bg-orange-50/40 px-4 py-3 text-sm flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <p class="font-medium text-gray-800">
                                        {{ class_basename($action->reportable_type) }} report #{{ $action->id }}
                                        · {{ ucfirst($action->status) }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        By {{ $action->resolver?->name ?? 'Unknown admin' }} · {{ optional($action->resolved_at)->diffForHumans() }}
                                    </p>
                                </div>
                                <span class="text-xs px-2 py-1 rounded {{ $action->status === 'resolved' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ ucfirst($action->status) }}
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No completed moderation actions yet.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
