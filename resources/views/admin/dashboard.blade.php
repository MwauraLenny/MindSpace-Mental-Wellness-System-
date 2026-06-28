<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-2xl font-semibold tracking-tight text-stone-800 dark:text-slate-100 leading-tight">
                Admin Dashboard
            </h2>

            <a
                href="{{ route('admin.moderation.index') }}"
                class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-lg font-semibold text-[11px] text-white uppercase tracking-[0.08em] hover:bg-amber-700"
            >
                Open Moderation Queue
            </a>
        </div>
    </x-slot>

    <div class="py-10 bg-gradient-to-b from-amber-50 via-orange-50 to-rose-100/70 dark:bg-[radial-gradient(circle_at_top,_rgba(251,191,36,0.12),_rgba(2,6,23,0.98)_56%)]">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                <div class="bg-white/95 overflow-hidden shadow-sm ring-1 ring-amber-100 sm:rounded-xl p-6 dark:bg-slate-900/92 dark:ring-slate-700/60">
                    <p class="text-xs uppercase text-gray-500">Total Users</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalUsers }}</p>
                </div>

                <div class="bg-white/95 overflow-hidden shadow-sm ring-1 ring-amber-100 sm:rounded-xl p-6 dark:bg-slate-900/92 dark:ring-slate-700/60">
                    <p class="text-xs uppercase text-gray-500">Total Routines</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalRoutines }}</p>
                </div>

                <div class="bg-white/95 overflow-hidden shadow-sm ring-1 ring-amber-100 sm:rounded-xl p-6 dark:bg-slate-900/92 dark:ring-slate-700/60">
                    <p class="text-xs uppercase text-gray-500">Total Reports</p>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalReports }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white/95 overflow-hidden shadow-sm ring-1 ring-amber-100 sm:rounded-xl p-6 dark:bg-slate-900/92 dark:ring-slate-700/60">
                    <h3 class="text-lg font-semibold text-gray-800">Community Activity</h3>
                    <p class="text-sm text-gray-600 mt-1">Overview for the last 7 days.</p>

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-lg border border-amber-200/70 bg-amber-50/50 p-4 dark:border-amber-300/25 dark:bg-amber-400/10">
                            <p class="text-xs uppercase text-gray-500">New Users</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $communityActivity['newUsers7d'] }}</p>
                        </div>
                        <div class="rounded-lg border border-amber-200/70 bg-amber-50/50 p-4 dark:border-amber-300/25 dark:bg-amber-400/10">
                            <p class="text-xs uppercase text-gray-500">New Routines</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $communityActivity['newRoutines7d'] }}</p>
                        </div>
                        <div class="rounded-lg border border-amber-200/70 bg-amber-50/50 p-4 dark:border-amber-300/25 dark:bg-amber-400/10">
                            <p class="text-xs uppercase text-gray-500">New Comments</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $communityActivity['newComments7d'] }}</p>
                        </div>
                        <div class="rounded-lg border border-amber-200/70 bg-amber-50/50 p-4 dark:border-amber-300/25 dark:bg-amber-400/10">
                            <p class="text-xs uppercase text-gray-500">New Reports</p>
                            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $communityActivity['newReports7d'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white/95 overflow-hidden shadow-sm ring-1 ring-amber-100 sm:rounded-xl p-6 dark:bg-slate-900/92 dark:ring-slate-700/60">
                    <h3 class="text-lg font-semibold text-gray-800">Moderation Queue</h3>
                    <p class="text-sm text-gray-600 mt-1">Pending reports requiring admin action.</p>

                    <div class="mt-4 space-y-3 max-h-96 overflow-y-auto pr-1">
                        @forelse($moderationQueue as $report)
                            <div class="rounded-md border border-amber-200 bg-amber-50/40 p-4 dark:border-amber-300/25 dark:bg-amber-400/10">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="font-semibold text-gray-800">{{ class_basename($report->reportable_type) }} Report</p>
                                    <span class="text-xs bg-amber-100 text-amber-800 px-2 py-1 rounded-full dark:bg-amber-400/20 dark:text-amber-200">Pending</span>
                                </div>
                                <p class="text-sm text-gray-700 mt-1">Reason: {{ $report->reason }}</p>
                                @if($report->details)
                                    <p class="text-sm text-gray-600 mt-1">{{ \Illuminate\Support\Str::limit($report->details, 120) }}</p>
                                @endif
                                <p class="text-xs text-gray-500 mt-2">
                                    Reported by {{ $report->reporter?->name ?? 'Unknown user' }}
                                    • {{ optional($report->created_at)->diffForHumans() }}
                                </p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">Moderation queue is clear.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-white/95 overflow-hidden shadow-sm ring-1 ring-amber-100 sm:rounded-xl p-6 dark:bg-slate-900/92 dark:ring-slate-700/60">
                <h3 class="text-lg font-semibold text-gray-800">Recent Community Activity</h3>
                <p class="text-sm text-gray-600 mt-1">Latest public routines and engagement signals.</p>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    @forelse($recentCommunityItems as $item)
                        <article class="rounded-lg border border-amber-200/70 bg-amber-50/40 p-4 dark:border-amber-300/25 dark:bg-amber-400/10">
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

                <div class="mt-6 flex items-center gap-2">
                    <a
                        href="{{ route('admin.users.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-amber-700 border border-transparent rounded-lg font-semibold text-[11px] text-white uppercase tracking-[0.08em] hover:bg-amber-800"
                    >
                        Manage User Roles
                    </a>

                    <a
                        href="{{ route('admin.reports.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-slate-700 border border-transparent rounded-lg font-semibold text-[11px] text-white uppercase tracking-[0.08em] hover:bg-slate-800"
                    >
                        Review Reports
                    </a>

                    <a
                        href="{{ route('admin.audit-logs.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-emerald-700 border border-transparent rounded-lg font-semibold text-[11px] text-white uppercase tracking-[0.08em] hover:bg-emerald-800"
                    >
                        Open Audit Logs
                    </a>
                </div>
            </div>

            <div class="bg-white/95 overflow-hidden shadow-sm ring-1 ring-amber-100 sm:rounded-xl p-6 dark:bg-slate-900/92 dark:ring-slate-700/60">
                <h3 class="text-lg font-semibold text-gray-800">Activity History Preview</h3>
                <p class="text-sm text-gray-600 mt-1">Latest platform events across mood logs, journals, and community comments.</p>

                <div class="mt-4 space-y-2">
                    @forelse($recentActivityPreview as $event)
                        <div class="rounded-lg border border-amber-200/70 bg-amber-50/40 px-4 py-3 text-sm flex items-center justify-between gap-3 dark:border-amber-300/25 dark:bg-amber-400/10">
                            <div>
                                <p class="font-medium text-gray-800">{{ $event['type'] }}</p>
                                <p class="text-gray-600">{{ $event['summary'] }}</p>
                            </div>
                            <p class="text-xs text-gray-500">{{ optional($event['at'])->diffForHumans() }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No recent activity to display yet.</p>
                    @endforelse
                </div>

                <div class="mt-4">
                    <a
                        href="{{ route('admin.users.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-amber-700 border border-transparent rounded-lg font-semibold text-[11px] text-white uppercase tracking-[0.08em] hover:bg-amber-800"
                    >
                        Open User Management
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
