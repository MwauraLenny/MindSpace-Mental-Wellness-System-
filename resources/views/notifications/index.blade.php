<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Notifications
            </h2>
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="px-3 py-2 rounded text-sm bg-indigo-600 text-white hover:bg-indigo-700">
                    Mark all as read
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if(session('success'))
                <div class="bg-green-100 text-green-800 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow sm:rounded-lg p-4 text-sm text-gray-700">
                You have <span class="font-semibold">{{ $unreadCount }}</span> unread notification{{ $unreadCount === 1 ? '' : 's' }}.
            </div>

            <div class="space-y-3">
                @forelse($notifications as $notification)
                    <article class="bg-white shadow sm:rounded-lg p-4 border {{ $notification->read_at ? 'border-gray-100' : 'border-indigo-200' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold {{ $notification->read_at ? 'text-gray-700' : 'text-indigo-700' }}">
                                    {{ $notification->title }}
                                </h3>
                                <p class="text-sm text-gray-700 mt-1">{{ $notification->message }}</p>
                                <p class="text-xs text-gray-500 mt-2">
                                    {{ $notification->created_at?->diffForHumans() }} · type: {{ str_replace('_', ' ', $notification->type) }}
                                </p>
                            </div>

                            @if(! $notification->read_at)
                                <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                    @csrf
                                    <button type="submit" class="text-xs px-2.5 py-1 rounded bg-indigo-100 text-indigo-700 hover:bg-indigo-200">
                                        Mark read
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="bg-white shadow sm:rounded-lg p-6 text-center text-gray-500">
                        No notifications yet.
                    </div>
                @endforelse
            </div>

            <div>
                {{ $notifications->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
