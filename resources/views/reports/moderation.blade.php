<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-2xl font-semibold tracking-tight text-stone-800 leading-tight">
                Moderation Queue
            </h2>

            <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-700 border border-transparent rounded-lg font-semibold text-[11px] text-white uppercase tracking-[0.08em] hover:bg-slate-800">
                Back To Admin Reports
            </a>
        </div>
    </x-slot>

    <div class="py-10 bg-gradient-to-b from-amber-50 via-orange-50 to-rose-100/70">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <section class="bg-white/95 shadow-sm ring-1 ring-amber-100 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800">Pending Reports</h3>
                <p class="text-sm text-gray-600 mt-1">Review reports, remove unsafe content when needed, and capture moderation notes.</p>

                @if(($reportStats['self_harm_pending'] ?? 0) > 0)
                    <div class="mt-3 rounded-lg border border-rose-300 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        <p class="font-semibold">Urgent safety alert</p>
                        <p class="mt-1">
                            {{ $reportStats['self_harm_pending'] }} pending self-harm risk report(s) are prioritized at the top of this queue.
                        </p>
                    </div>
                @endif

                <div class="space-y-4 mt-4">
                    @forelse($pendingReports as $report)
                        <article class="rounded-xl {{ $report->reason === 'self_harm_risk' ? 'border-rose-300 bg-rose-50/60' : 'border-amber-200 bg-amber-50/50' }} p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <p class="font-semibold text-gray-800">{{ class_basename($report->reportable_type) }} report #{{ $report->id }}</p>
                                    <p class="text-xs text-gray-500">
                                        Reported by {{ $report->reporter?->name ?? 'Unknown user' }} · {{ optional($report->created_at)->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($report->reason === 'self_harm_risk')
                                        <span class="text-xs bg-rose-100 text-rose-800 px-2 py-1 rounded-full">Urgent Safety</span>
                                    @endif
                                    <span class="text-xs bg-amber-100 text-amber-800 px-2 py-1 rounded-full">Pending</span>
                                </div>
                            </div>

                            <p class="text-sm text-gray-700 mt-2">Reason: <span class="font-medium">{{ str_replace('_', ' ', $report->reason) }}</span></p>
                            @if($report->details)
                                <p class="text-sm text-gray-600 mt-1">{{ $report->details }}</p>
                            @endif

                            <div class="mt-3 text-sm text-gray-700 bg-white border border-amber-100 rounded-lg p-3">
                                @if($report->reportable_type === App\Models\Routine::class)
                                    <p class="font-semibold">Routine: {{ $report->reportable?->display_title ?? 'Unavailable' }}</p>
                                    <p class="mt-1 text-gray-600">{{ \Illuminate\Support\Str::limit((string) ($report->reportable?->body), 220) }}</p>
                                @elseif($report->reportable_type === App\Models\Comment::class)
                                    <p class="font-semibold">Comment</p>
                                    <p class="mt-1 text-gray-600">{{ \Illuminate\Support\Str::limit((string) ($report->reportable?->body), 220) }}</p>
                                @else
                                    <p class="text-gray-500">Reported content preview unavailable.</p>
                                @endif
                            </div>

                            <div class="mt-4 grid grid-cols-1 lg:grid-cols-4 gap-2">
                                <form method="POST" action="{{ route('admin.reports.moderate', $report) }}" class="space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="resolve">
                                    <textarea name="admin_note" rows="2" class="w-full rounded-md border-amber-200 bg-white/90 text-xs" placeholder="Optional admin note"></textarea>
                                    <button type="submit" class="w-full text-[11px] px-3 py-2 rounded-lg bg-emerald-600 text-white uppercase tracking-[0.08em] hover:bg-emerald-700">Mark Resolved</button>
                                </form>

                                <form method="POST" action="{{ route('admin.reports.moderate', $report) }}" class="space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="warn">
                                    <textarea name="admin_note" rows="2" class="w-full rounded-md border-amber-200 bg-white/90 text-xs" placeholder="Explain the warning to the content owner" required></textarea>
                                    <button type="submit" class="w-full text-[11px] px-3 py-2 rounded-lg bg-amber-600 text-white uppercase tracking-[0.08em] hover:bg-amber-700">Warn User</button>
                                </form>

                                <form method="POST" action="{{ route('admin.reports.moderate', $report) }}" class="space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="dismiss">
                                    <textarea name="admin_note" rows="2" class="w-full rounded-md border-amber-200 bg-white/90 text-xs" placeholder="Optional admin note"></textarea>
                                    <button type="submit" class="w-full text-[11px] px-3 py-2 rounded-lg bg-slate-600 text-white uppercase tracking-[0.08em] hover:bg-slate-700">Dismiss</button>
                                </form>

                                <form method="POST" action="{{ route('admin.reports.moderate', $report) }}" class="space-y-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="action" value="remove">
                                    <textarea name="admin_note" rows="2" class="w-full rounded-md border-amber-200 bg-white/90 text-xs" placeholder="Optional admin note"></textarea>
                                    <button type="submit" class="w-full text-[11px] px-3 py-2 rounded-lg bg-rose-600 text-white uppercase tracking-[0.08em] hover:bg-rose-700">Remove Content + Resolve</button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-gray-500">No pending reports in moderation queue.</p>
                    @endforelse
                </div>
            </section>

            <section class="bg-white/95 shadow-sm ring-1 ring-amber-100 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-gray-800">Recent Moderation Actions</h3>
                <p class="text-sm text-gray-600 mt-1">Last completed moderation outcomes across the platform.</p>

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
            </section>
        </div>
    </div>
</x-app-layout>
