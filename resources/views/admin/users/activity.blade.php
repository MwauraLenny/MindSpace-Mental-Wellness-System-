<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                User Activity History
            </h2>

            <a
                href="{{ route('admin.users.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
            >
                Back To User Management
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <section class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800">User Details</h3>
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div class="rounded border border-gray-100 px-4 py-3">Name: <span class="font-semibold">{{ $user->name }}</span></div>
                    <div class="rounded border border-gray-100 px-4 py-3">Email: <span class="font-semibold">{{ $user->email }}</span></div>
                    <div class="rounded border border-gray-100 px-4 py-3">Role: <span class="font-semibold capitalize">{{ $user->role }}</span></div>
                    <div class="rounded border border-gray-100 px-4 py-3">
                        Status:
                        <span class="font-semibold {{ $user->is_suspended ? 'text-red-700' : 'text-emerald-700' }}">
                            {{ $user->is_suspended ? 'Suspended' : 'Active' }}
                        </span>
                    </div>
                </div>
            </section>

            <section class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800">Activity Summary</h3>
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-sm">
                    <div class="rounded border border-gray-100 px-4 py-3">Mood logs: <span class="font-semibold">{{ $activitySummary['mood_logs'] }}</span></div>
                    <div class="rounded border border-gray-100 px-4 py-3">Journals: <span class="font-semibold">{{ $activitySummary['journals'] }}</span></div>
                    <div class="rounded border border-gray-100 px-4 py-3">Routines: <span class="font-semibold">{{ $activitySummary['routines'] }}</span></div>
                    <div class="rounded border border-gray-100 px-4 py-3">Comments: <span class="font-semibold">{{ $activitySummary['comments'] }}</span></div>
                    <div class="rounded border border-gray-100 px-4 py-3">Notifications: <span class="font-semibold">{{ $activitySummary['notifications'] }}</span></div>
                </div>
            </section>

            <section class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800">Recent Activity Timeline</h3>
                <div class="space-y-3 mt-4">
                    @forelse($activityTimeline as $entry)
                        <article class="rounded border border-gray-100 px-4 py-3">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-medium text-gray-800">{{ $entry['type'] }}</p>
                                <p class="text-xs text-gray-500">{{ optional($entry['at'])->diffForHumans() }}</p>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">{{ $entry['description'] }}</p>
                        </article>
                    @empty
                        <p class="text-sm text-gray-500">No activity history found for this user yet.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
