<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold tracking-tight text-stone-800 leading-tight">
            Audit Logs
        </h2>
    </x-slot>

    <div class="py-10 bg-gradient-to-b from-amber-50 via-orange-50 to-rose-100/70">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white/95 shadow-sm ring-1 ring-amber-100 sm:rounded-xl p-6">
                <form method="GET" class="flex flex-wrap items-center gap-3">
                    <label for="action" class="text-sm font-medium text-gray-700">Filter action</label>
                    <select id="action" name="action" class="rounded-md border-amber-300 bg-amber-50/40 text-sm">
                        <option value="">All actions</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" @selected($selectedAction === $action)>{{ $action }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-amber-700 text-white text-[11px] font-semibold uppercase tracking-[0.08em] hover:bg-amber-800">Apply</button>
                </form>
            </div>

            <div class="bg-white/95 shadow-sm ring-1 ring-amber-100 sm:rounded-xl overflow-x-auto">
                <table class="min-w-full divide-y divide-amber-100/80">
                    <thead class="bg-amber-50/70">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">When</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Target</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Metadata</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-100/70 bg-white/90">
                        @forelse($logs as $log)
                            <tr class="hover:bg-amber-50/40 transition-colors">
                                <td class="px-4 py-3 text-sm text-gray-600">{{ optional($log->performed_at)->format('Y-m-d H:i:s') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $log->actor?->name ?? 'System' }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $log->action }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    {{ class_basename($log->target_type ?? 'n/a') }}
                                    @if($log->target_id)
                                        #{{ $log->target_id }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">
                                    @if(!empty($log->meta) && is_array($log->meta))
                                        <div class="space-y-1">
                                            @foreach(array_slice($log->meta, 0, 4, true) as $metaKey => $metaValue)
                                                <div>
                                                    <span class="font-semibold text-gray-600">{{ \Illuminate\Support\Str::headline((string) $metaKey) }}:</span>
                                                    <span>{{ \Illuminate\Support\Str::limit(is_scalar($metaValue) ? (string) $metaValue : json_encode($metaValue), 80) }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-sm text-gray-500 text-center">No audit entries found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
