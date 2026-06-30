<nav
    x-data="{ open: false, exploreDesktopOpen: false, adminDesktopOpen: false }"
    class="bg-white border-b border-amber-200/80"
>
    @php
        $unreadNotificationCount = (int) ($unreadNotificationCount ?? 0);
        $isAdmin = Auth::user()?->role === 'admin';
    @endphp

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-[4.5rem] max-[380px]:h-[4.25rem] sm:h-24">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ $isAdmin ? route('admin.dashboard') : route('dashboard') }}" class="shrink-0 inline-flex items-center">
                    <x-application-logo class="h-9 w-auto max-w-[6rem] max-[380px]:h-8 max-[380px]:max-w-[5rem] sm:h-12 sm:max-w-[8.5rem] lg:h-16 lg:max-w-[12rem] xl:h-[4.5rem] xl:max-w-[13.5rem]" />
                </a>

                <div class="hidden sm:flex sm:items-center sm:space-x-2 sm:ms-4">
                    <x-nav-link :href="$isAdmin ? route('admin.dashboard') : route('dashboard')" :active="request()->routeIs('dashboard', 'admin.dashboard')">
                        {{ $isAdmin ? __('Admin Home') : __('Dashboard') }}
                    </x-nav-link>

                    @if (! $isAdmin)
                        <x-nav-link :href="route('mood.index')" :active="request()->routeIs('mood.*')">
                            {{ __('Log Mood') }}
                        </x-nav-link>

                        <x-nav-link :href="route('community.feed')" :active="request()->routeIs('community.*', 'routines.*')">
                            {{ __('Community') }}
                        </x-nav-link>

                        <div class="relative" @click.outside="exploreDesktopOpen = false">
                            <button
                                @click="exploreDesktopOpen = !exploreDesktopOpen"
                                class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-amber-900"
                                :class="{ 'text-amber-900': exploreDesktopOpen }"
                                type="button"
                            >
                                Explore
                                <svg class="ms-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div
                                x-show="exploreDesktopOpen"
                                x-transition
                                class="absolute left-0 mt-2 w-56 rounded-lg border border-amber-200/80 bg-white shadow-lg py-1 z-40"
                            >
                                <a href="{{ route('routines.recommendations') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50">Recommendations</a>
                                <a href="{{ route('journals.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50">Journal</a>
                                <a href="{{ route('reports.personal') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50">Reports</a>
                                <a href="{{ route('analytics.personal') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50">Analytics</a>
                                <a href="{{ route('notifications.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-50">Notifications</a>
                            </div>
                        </div>
                    @endif

                    @if ($isAdmin)
                        <div class="relative" @click.outside="adminDesktopOpen = false">
                            <button
                                @click="adminDesktopOpen = !adminDesktopOpen"
                                class="inline-flex items-center px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-amber-900"
                                :class="{ 'text-amber-900': adminDesktopOpen }"
                                type="button"
                            >
                                Admin Tools
                                <svg class="ms-1 h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div
                                x-show="adminDesktopOpen"
                                x-transition
                                class="absolute left-0 mt-2 w-64 rounded-xl border border-amber-200/80 bg-amber-50/95 shadow-lg py-1.5 z-40"
                            >
                                <p class="px-4 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-[0.08em] text-amber-700">Safety & Oversight</p>
                                <a href="{{ route('admin.moderation.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-100/60 {{ request()->routeIs('admin.moderation.*') ? 'bg-amber-100/70 text-amber-900 font-semibold' : '' }}">Moderation Queue</a>
                                <a href="{{ route('admin.reports.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-100/60 {{ request()->routeIs('admin.reports.*') ? 'bg-amber-100/70 text-amber-900 font-semibold' : '' }}">Reports & Insights</a>

                                <p class="px-4 pt-3 pb-1 text-[11px] font-semibold uppercase tracking-[0.08em] text-amber-700">Administration</p>
                                <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-100/60 {{ request()->routeIs('admin.users.*') ? 'bg-amber-100/70 text-amber-900 font-semibold' : '' }}">User Accounts</a>
                                <a href="{{ route('admin.analytics') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-100/60 {{ request()->routeIs('admin.analytics') ? 'bg-amber-100/70 text-amber-900 font-semibold' : '' }}">Platform Analytics</a>
                                <a href="{{ route('admin.audit-logs.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-amber-100/60 {{ request()->routeIs('admin.audit-logs.*') ? 'bg-amber-100/70 text-amber-900 font-semibold' : '' }}">Audit Trail</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6 shrink-0">
                @if (! $isAdmin)
                    <a
                        href="{{ route('notifications.index') }}"
                        class="relative inline-flex items-center justify-center p-2 mr-2 rounded-md bg-transparent text-gray-900 hover:text-amber-900 {{ request()->routeIs('notifications.*') ? 'text-amber-900' : '' }}"
                        aria-label="Notifications"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 relative z-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a3 3 0 006 0" />
                        </svg>
                        @if ($unreadNotificationCount > 0)
                            <span class="absolute z-20 rounded-full bg-red-600" style="top: 2px; right: 2px; width: 8px; height: 8px;"></span>
                        @endif
                    </a>
                @endif

                <button
                    type="button"
                    data-theme-toggle
                    class="inline-flex items-center justify-center p-2 mr-2 rounded-md bg-transparent text-gray-900 hover:text-amber-900"
                    aria-label="Toggle theme"
                >
                    <svg data-theme-icon-sun xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m0 13.5V21m9-9h-2.25M5.25 12H3m15.114 6.364-1.591-1.591M7.477 7.477 5.886 5.886m12.228 0-1.591 1.591M7.477 16.523l-1.591 1.591M15.75 12A3.75 3.75 0 1 1 8.25 12a3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                    <svg data-theme-icon-moon xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79Z" />
                    </svg>
                </button>

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex max-w-[11rem] items-center gap-1 px-2 py-2 border border-transparent text-sm leading-4 font-semibold rounded-md text-gray-800 bg-white hover:text-gray-900 focus:outline-none transition ease-in-out duration-150">
                            <div class="truncate">{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        @if (! $isAdmin)
                            <x-dropdown-link :href="route('profile.show')">
                                {{ __('View Profile') }}
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Edit Profile') }}
                            </x-dropdown-link>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md bg-transparent text-gray-500 hover:text-gray-700 focus:outline-none focus:text-gray-700 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-amber-200/80">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="$isAdmin ? route('admin.dashboard') : route('dashboard')" :active="request()->routeIs('dashboard', 'admin.dashboard')">
                {{ $isAdmin ? __('Admin Home') : __('Dashboard') }}
            </x-responsive-nav-link>

            @if (! $isAdmin)
                <x-responsive-nav-link :href="route('mood.index')" :active="request()->routeIs('mood.*')">
                    {{ __('Log Mood') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('community.feed')" :active="request()->routeIs('community.*', 'routines.*')">
                    {{ __('Community') }}
                </x-responsive-nav-link>

                <details class="px-4 py-1">
                    <summary class="text-sm font-semibold text-gray-700 cursor-pointer">Explore</summary>
                    <div class="mt-2 space-y-1">
                        <x-responsive-nav-link :href="route('routines.recommendations')" :active="request()->routeIs('routines.recommendations')">
                            {{ __('Recommendations') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('journals.index')" :active="request()->routeIs('journals.*')">
                            {{ __('Journal') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('reports.personal')" :active="request()->routeIs('reports.*')">
                            {{ __('Reports') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('analytics.personal')" :active="request()->routeIs('analytics.*')">
                            {{ __('Analytics') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
                            {{ __('Notifications') }}
                        </x-responsive-nav-link>
                    </div>
                </details>
            @endif

            @if ($isAdmin)
                <details class="px-4 py-1">
                    <summary class="text-sm font-semibold text-gray-700 cursor-pointer">Admin Tools</summary>
                    <div class="mt-2 space-y-1">
                        <x-responsive-nav-link :href="route('admin.moderation.index')" :active="request()->routeIs('admin.moderation.*')">
                            {{ __('Moderation Queue') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')">
                            {{ __('Reports & Insights') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                            {{ __('User Accounts') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('admin.analytics')" :active="request()->routeIs('admin.analytics')">
                            {{ __('Platform Analytics') }}
                        </x-responsive-nav-link>
                        <x-responsive-nav-link :href="route('admin.audit-logs.index')" :active="request()->routeIs('admin.audit-logs.*')">
                            {{ __('Audit Trail') }}
                        </x-responsive-nav-link>
                    </div>
                </details>
            @endif
        </div>

        <div class="pt-4 pb-1 border-t border-gray-300">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <button
                    type="button"
                    data-theme-toggle
                    class="w-full text-left px-4 py-2 text-sm bg-transparent text-gray-900 hover:text-amber-900 inline-flex items-center gap-2"
                >
                    <svg data-theme-icon-sun xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m0 13.5V21m9-9h-2.25M5.25 12H3m15.114 6.364-1.591-1.591M7.477 7.477 5.886 5.886m12.228 0-1.591 1.591M7.477 16.523l-1.591 1.591M15.75 12A3.75 3.75 0 1 1 8.25 12a3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                    <svg data-theme-icon-moon xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79Z" />
                    </svg>
                    <span>Theme</span>
                </button>

                @if (! $isAdmin)
                    <x-responsive-nav-link :href="route('profile.show')">
                        {{ __('View Profile') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Edit Profile') }}
                    </x-responsive-nav-link>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
