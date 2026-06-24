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

            <section class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800">Moderation Queue</h3>
                <p class="text-sm text-gray-600 mt-1">Review reports, remove content when needed, and update report status.</p>

                <div class="space-y-4 mt-4">
                    @forelse($pendingReports as $report)
                        <article class="rounded border border-amber-200 bg-amber-50/30 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <p class="font-semibold text-gray-800">{{ class_basename($report->reportable_type) }} report #{{ $report->id }}</p>
                                    <p class="text-xs text-gray-500">
                                        Reported by {{ $report->reporter?->name ?? 'Unknown user' }} · {{ optional($report->created_at)->diffForHumans() }}
                                    </p>
                                </div>
                                <span class="text-xs bg-amber-100 text-amber-800 px-2 py-1 rounded-full">Pending</span>
                            </div>

                            <p class="text-sm text-gray-700 mt-2">Reason: <span class="font-medium">{{ str_replace('_', ' ', $report->reason) }}</span></p>
                            @if($report->details)
                                <p class="text-sm text-gray-600 mt-1">{{ $report->details }}</p>
                            @endif

                            <div class="mt-3 text-sm text-gray-700 bg-white border border-gray-100 rounded p-3">
                                @if($report->reportable_type === App\Models\Routine::class)
                                    <p class="font-semibold">Routine: {{ $report->reportable?->display_title ?? 'Unavailable' }}</p>
                                    <p class="mt-1 text-gray-600">{{ \Illuminate\Support\Str::limit((string) ($report->reportable?->body), 200) }}</p>
                                @elseif($report->reportable_type === App\Models\Comment::class)
                                    <p class="font-semibold">Comment</p>
                                    <p class="mt-1 text-gray-600">{{ \Illuminate\Support\Str::limit((string) ($report->reportable?->body), 200) }}</p>
                                @else
                                    <p class="text-gray-500">Reported content preview unavailable.</p>
                                @endif
                            </div>

                            <div class="mt-4 grid grid-cols-1 lg:grid-cols-3 gap-2">
                                <form method="POST" action="{{ route('admin.reports.moderate', $report) }}" class="space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="resolve">
                                    <textarea name="admin_note" rows="2" class="w-full rounded border-gray-300 text-xs" placeholder="Optional admin note"></textarea>
                                    <button type="submit" class="w-full text-xs px-3 py-2 rounded bg-emerald-600 text-white hover:bg-emerald-700">Mark Resolved</button>
                                </form>

                                <form method="POST" action="{{ route('admin.reports.moderate', $report) }}" class="space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="dismiss">
                                    <textarea name="admin_note" rows="2" class="w-full rounded border-gray-300 text-xs" placeholder="Optional admin note"></textarea>
                                    <button type="submit" class="w-full text-xs px-3 py-2 rounded bg-slate-600 text-white hover:bg-slate-700">Dismiss</button>
                                </form>

                                <form method="POST" action="{{ route('admin.reports.moderate', $report) }}" class="space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="remove">
                                    <textarea name="admin_note" rows="2" class="w-full rounded border-gray-300 text-xs" placeholder="Optional admin note"></textarea>
                                    <button type="submit" class="w-full text-xs px-3 py-2 rounded bg-rose-600 text-white hover:bg-rose-700">Remove Content + Resolve</button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-gray-500">No pending reports in moderation queue.</p>
                    @endforelse
                </div>
            </section>

            <section class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800">Monitor Activity</h3>
                <p class="text-sm text-gray-600 mt-1">Moderation throughput and safety trends.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mt-4 text-sm">
                    <div class="rounded border border-gray-100 px-4 py-3">Reports (7d): <span class="font-semibold">{{ $monitoringMetrics['reports_7d'] }}</span></div>
                    <div class="rounded border border-gray-100 px-4 py-3">Resolved (7d): <span class="font-semibold">{{ $monitoringMetrics['resolved_7d'] }}</span></div>
                    <div class="rounded border border-gray-100 px-4 py-3">Dismissed (7d): <span class="font-semibold">{{ $monitoringMetrics['dismissed_7d'] }}</span></div>
                    <div class="rounded border border-gray-100 px-4 py-3">Removed Routines: <span class="font-semibold">{{ $monitoringMetrics['removed_routines_total'] }}</span></div>
                    <div class="rounded border border-gray-100 px-4 py-3">Removed Comments: <span class="font-semibold">{{ $monitoringMetrics['removed_comments_total'] }}</span></div>
                </div>

                <div class="mt-5">
                    <h4 class="font-semibold text-gray-800">Recent Moderation Actions</h4>
                    <div class="space-y-2 mt-3">
                        @forelse($recentModerationActions as $action)
                            <div class="rounded border border-gray-100 px-4 py-3 text-sm flex flex-wrap items-center justify-between gap-2">
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
