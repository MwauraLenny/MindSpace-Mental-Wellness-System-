<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold tracking-tight text-stone-800 leading-tight">
            User Management
        </h2>
    </x-slot>

    <div class="py-10 bg-gradient-to-b from-amber-50 via-orange-50 to-rose-100/70" x-data="{ activeModal: null }" @keydown.escape.window="activeModal = null">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('success'))
                <div class="p-4 bg-emerald-100 border border-emerald-200 text-emerald-800 rounded-xl shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-rose-100 border border-rose-200 text-rose-800 rounded-xl shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white/95 overflow-hidden shadow-sm ring-1 ring-amber-100 sm:rounded-xl" :class="activeModal ? 'blur-sm pointer-events-none select-none' : ''">
                <div class="p-6 text-gray-900 overflow-x-auto">
                    <table class="min-w-full divide-y divide-amber-100/80">
                        <thead class="bg-amber-50/70">
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
                        <tbody class="bg-white/90 divide-y divide-amber-100/70">
                            @foreach ($users as $user)
                                <tr class="hover:bg-amber-50/40 transition-colors">
                                    <td class="px-4 py-3">{{ $user->name }}</td>
                                    <td class="px-4 py-3">{{ $user->email }}</td>
                                    <td class="px-4 py-3 capitalize">{{ $user->role }}</td>
                                    <td class="px-4 py-3">
                                        @if($user->is_banned)
                                            <span class="inline-flex items-center px-2 py-1 rounded bg-red-200 text-red-800 text-xs">
                                                Banned
                                            </span>
                                        @elseif($user->is_suspended)
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
                                            <select name="role" class="rounded-md border-amber-300 bg-amber-50/40 text-sm">
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

                                            @if((int) auth()->id() === (int) $user->id)
                                                <span class="inline-flex items-center px-3 py-1.5 rounded bg-slate-100 text-slate-700 text-xs">
                                                    Self actions disabled
                                                </span>
                                            @elseif($user->is_banned)
                                                <form method="POST" action="{{ route('admin.users.unban', $user) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded bg-emerald-100 text-emerald-700 text-xs hover:bg-emerald-200">
                                                        Unban
                                                    </button>
                                                </form>
                                            @elseif($user->is_suspended)
                                                <form method="POST" action="{{ route('admin.users.unsuspend', $user) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded bg-emerald-100 text-emerald-700 text-xs hover:bg-emerald-200">
                                                        Reactivate
                                                    </button>
                                                </form>
                                            @else
                                                <button
                                                    type="button"
                                                    @click="activeModal = 'suspend-{{ $user->id }}'"
                                                    class="inline-flex items-center px-3 py-1.5 rounded bg-amber-100 text-amber-700 text-xs hover:bg-amber-200"
                                                >
                                                    Suspend
                                                </button>

                                                <button
                                                    type="button"
                                                    @click="activeModal = 'ban-{{ $user->id }}'"
                                                    class="inline-flex items-center px-3 py-1.5 rounded bg-red-100 text-red-700 text-xs hover:bg-red-200"
                                                >
                                                    Ban
                                                </button>
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

            @foreach ($users as $user)
                @if((int) auth()->id() !== (int) $user->id && ! $user->is_banned && ! $user->is_suspended)
                    <div
                        x-show="activeModal === 'suspend-{{ $user->id }}'"
                        x-cloak
                        class="fixed inset-0 z-50 flex items-center justify-center p-4"
                        role="dialog"
                        aria-modal="true"
                    >
                        <div class="absolute inset-0 bg-black/35 backdrop-blur-sm" @click="activeModal = null"></div>
                        <form method="POST" action="{{ route('admin.users.suspend', $user) }}" class="relative z-10 w-full max-w-lg rounded-xl bg-amber-50 shadow-2xl border border-amber-200 max-h-[85vh] flex flex-col overflow-hidden">
                            @csrf
                            @method('PATCH')

                            <div class="p-6 space-y-5 overflow-y-auto">
                                <div>
                                <h3 class="text-lg font-semibold text-gray-900">Suspend {{ $user->name }}</h3>
                                <p class="text-sm text-gray-600 mt-1">Set a clear reason and duration for this suspension.</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Suspension reason</label>
                                    <textarea name="reason" rows="4" class="w-full rounded-md border-amber-200 bg-white/90 text-sm" placeholder="Explain why this user is being suspended" required></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Suspension period</label>
                                    <select name="duration" class="w-full rounded-md border-amber-200 bg-white/90 text-sm" required>
                                        <option value="3d">3 days</option>
                                        <option value="1w">1 week</option>
                                        <option value="1m">1 month</option>
                                        <option value="3m">3 months</option>
                                        <option value="1y">1 year</option>
                                    </select>
                                </div>
                            </div>

                            <div class="border-t border-amber-200 bg-amber-50 px-6 py-4 flex items-center justify-end gap-3">
                                <button type="button" @click="activeModal = null" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-100">
                                    Cancel
                                </button>
                                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md bg-amber-600 text-white text-sm font-semibold shadow hover:bg-amber-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2">
                                    Submit Suspension
                                </button>
                            </div>
                        </form>
                    </div>

                    <div
                        x-show="activeModal === 'ban-{{ $user->id }}'"
                        x-cloak
                        class="fixed inset-0 z-50 flex items-center justify-center p-4"
                        role="dialog"
                        aria-modal="true"
                    >
                        <div class="absolute inset-0 bg-black/35 backdrop-blur-sm" @click="activeModal = null"></div>
                        <form method="POST" action="{{ route('admin.users.ban', $user) }}" class="relative z-10 w-full max-w-lg rounded-xl bg-rose-50 shadow-2xl border border-rose-200 max-h-[85vh] flex flex-col overflow-hidden">
                            @csrf
                            @method('PATCH')

                            <div class="p-6 space-y-5 overflow-y-auto">
                                <div>
                                <h3 class="text-lg font-semibold text-gray-900">Ban {{ $user->name }}</h3>
                                <p class="text-sm text-gray-600 mt-1">Set a clear reason and duration for this ban.</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ban reason</label>
                                    <textarea name="reason" rows="4" class="w-full rounded-md border-amber-200 bg-white/90 text-sm" placeholder="Explain why this user is being banned" required></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Ban period</label>
                                    <select name="duration" class="w-full rounded-md border-amber-200 bg-white/90 text-sm" required>
                                        <option value="3d">3 days</option>
                                        <option value="1w">1 week</option>
                                        <option value="1m">1 month</option>
                                        <option value="3m">3 months</option>
                                        <option value="1y">1 year</option>
                                    </select>
                                </div>
                            </div>

                            <div class="border-t border-rose-200 bg-rose-50 px-6 py-4 flex items-center justify-end gap-3">
                                <button type="button" @click="activeModal = null" class="inline-flex items-center px-4 py-2 rounded-md border border-gray-300 text-sm text-gray-700 hover:bg-gray-100">
                                    Cancel
                                </button>
                                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-md bg-red-600 text-white text-sm font-semibold shadow hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                    Submit Ban
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</x-app-layout>
