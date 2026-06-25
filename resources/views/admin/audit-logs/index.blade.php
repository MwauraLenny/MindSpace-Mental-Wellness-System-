<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Audit Logs
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <form method="GET" class="flex flex-wrap items-center gap-3">
                    <label for="action" class="text-sm font-medium text-gray-700">Filter action</label>
                    <select id="action" name="action" class="rounded border-gray-300 text-sm">
                        <option value="">All actions</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" @selected($selectedAction === $action)>{{ $action }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-3 py-1.5 rounded bg-gray-700 text-white text-sm hover:bg-gray-800">Apply</button>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">When</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Target</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Metadata</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($logs as $log)
                            <tr>
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
