<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            User Management
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 bg-green-100 border border-green-200 text-green-800 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-red-100 border border-red-200 text-red-800 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current Role</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Activity</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Change Role</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach ($users as $user)
                                <tr>
                                    <td class="px-4 py-3">{{ $user->name }}</td>
                                    <td class="px-4 py-3">{{ $user->email }}</td>
                                    <td class="px-4 py-3 capitalize">{{ $user->role }}</td>
                                    <td class="px-4 py-3">
                                        @if($user->is_suspended)
                                            <span class="inline-flex items-center px-2 py-1 rounded bg-red-100 text-red-700 text-xs">
                                                Suspended
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded bg-emerald-100 text-emerald-700 text-xs">
                                                Active
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        @if($user->last_activity_at)
                                            {{ \Carbon\Carbon::parse($user->last_activity_at)->diffForHumans() }}
                                        @else
                                            No activity yet
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <form method="POST" action="{{ route('admin.users.role.update', $user) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="role" class="rounded border-gray-300 text-sm">
                                                <option value="user" @selected($user->role === 'user')>user</option>
                                                <option value="admin" @selected($user->role === 'admin')>admin</option>
                                            </select>
                                            <x-primary-button>
                                                Save
                                            </x-primary-button>
                                        </form>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2">
                                            <a
                                                href="{{ route('admin.users.activity', $user) }}"
                                                class="inline-flex items-center px-3 py-1.5 rounded bg-indigo-100 text-indigo-700 text-xs hover:bg-indigo-200"
                                            >
                                                Activity History
                                            </a>

                                            @if($user->is_suspended)
                                                <form method="POST" action="{{ route('admin.users.unsuspend', $user) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded bg-emerald-100 text-emerald-700 text-xs hover:bg-emerald-200">
                                                        Reactivate
                                                    </button>
                                                </form>
                                            @else
                                                <details>
                                                    <summary class="inline-flex cursor-pointer items-center px-3 py-1.5 rounded bg-amber-100 text-amber-700 text-xs hover:bg-amber-200">
                                                        Suspend
                                                    </summary>
                                                    <form method="POST" action="{{ route('admin.users.suspend', $user) }}" class="mt-2 p-3 border border-amber-200 rounded bg-amber-50/40 space-y-2 w-56">
                                                        @csrf
                                                        @method('PATCH')
                                                        <textarea name="reason" rows="2" class="w-full rounded border-gray-300 text-xs" placeholder="Optional suspension reason"></textarea>
                                                        <button type="submit" class="w-full inline-flex justify-center items-center px-3 py-1.5 rounded bg-amber-600 text-white text-xs hover:bg-amber-700">
                                                            Confirm Suspend
                                                        </button>
                                                    </form>
                                                </details>
                                            @endif

                                            @if((int) auth()->id() !== (int) $user->id)
                                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this user account? This action cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded bg-red-100 text-red-700 text-xs hover:bg-red-200">
                                                        Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
