<nav x-data="{ open: false }" class="bg-white border-b border-gray-200">
    @php
        $unreadNotificationCount = (int) ($unreadNotificationCount ?? 0);
    @endphp

    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ Auth::user()?->role === 'admin' ? __('Admin Home') : __('Dashboard') }}
                    </x-nav-link>
                    @if (Auth::user()?->role !== 'admin')
                        <x-nav-link :href="route('mood.index')" :active="request()->routeIs('mood.*')">
                            {{ __('Log Mood') }}
                        </x-nav-link>
                    @endif
<x-nav-link :href="route('community.feed')" :active="request()->routeIs('community.*', 'routines.*')">
    {{ __('Community') }}
</x-nav-link>
<x-nav-link :href="route('journals.index')" :active="request()->routeIs('journals.*')">
    {{ __('Journal') }}
</x-nav-link>
                    @if (Auth::user()?->role !== 'admin')
                        <x-nav-link :href="route('reports.personal')" :active="request()->routeIs('reports.*')">
                            {{ __('Reports') }}
                        </x-nav-link>
                        <x-nav-link :href="route('analytics.personal')" :active="request()->routeIs('analytics.*')">
                            {{ __('Analytics') }}
                        </x-nav-link>
                    @endif
                    @if (Auth::user()?->role === 'admin')
                        <x-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')">
                            {{ __('Admin Reports') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.analytics')" :active="request()->routeIs('admin.analytics')">
                            {{ __('Admin Analytics') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.audit-logs.index')" :active="request()->routeIs('admin.audit-logs.*')">
                            {{ __('Audit Logs') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 shrink-0">
                <a href="{{ route('notifications.index') }}"
                    class="relative inline-flex items-center justify-center p-2 mr-3 rounded-md bg-transparent text-gray-900 hover:text-indigo-900 dark:text-gray-100 dark:hover:text-white {{ request()->routeIs('notifications.*') ? 'text-indigo-900 dark:text-white' : '' }}"
                    aria-label="Notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 relative z-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17a3 3 0 006 0" />
                    </svg>
                    @if($unreadNotificationCount > 0)
                        <span class="absolute z-20 rounded-full bg-red-600" style="top: 2px; right: 2px; width: 8px; height: 8px;"></span>
                    @endif
                </a>

                <button
                    type="button"
                    data-theme-toggle
                    class="inline-flex items-center justify-center p-2 mr-3 rounded-md bg-transparent text-gray-900 hover:text-indigo-900 dark:text-white dark:hover:text-white"
                    aria-label="Toggle theme"
                >
                    <svg data-theme-icon-sun xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m0 13.5V21m9-9h-2.25M5.25 12H3m15.114 6.364-1.591-1.591M7.477 7.477 5.886 5.886m12.228 0-1.591 1.591M7.477 16.523l-1.591 1.591M15.75 12A3.75 3.75 0 1 1 8.25 12a3.75 3.75 0 0 1 7.5 0Z"/>
                    </svg>
                    <svg data-theme-icon-moon xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79Z"/>
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
                        <x-dropdown-link :href="route('profile.show')">
                            {{ __('View Profile') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Edit Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
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

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ Auth::user()?->role === 'admin' ? __('Admin Home') : __('Dashboard') }}
            </x-responsive-nav-link>

            @if (Auth::user()?->role !== 'admin')
                <x-responsive-nav-link :href="route('mood.index')" :active="request()->routeIs('mood.*')">
                    {{ __('Log Mood') }}
                </x-responsive-nav-link>
            @endif

            <x-responsive-nav-link :href="route('community.feed')" :active="request()->routeIs('community.*', 'routines.*')">
                {{ __('Community') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link :href="route('journals.index')" :active="request()->routeIs('journals.*')">
                {{ __('Journal') }}
            </x-responsive-nav-link>

            @if (Auth::user()?->role !== 'admin')
                <x-responsive-nav-link :href="route('reports.personal')" :active="request()->routeIs('reports.*')">
                    {{ __('Reports') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('analytics.personal')" :active="request()->routeIs('analytics.*')">
                    {{ __('Analytics') }}
                </x-responsive-nav-link>
            @endif

            <x-responsive-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
                {{ __('Notifications') }}
            </x-responsive-nav-link>

            @if (Auth::user()?->role === 'admin')
                <x-responsive-nav-link :href="route('admin.reports.index')" :active="request()->routeIs('admin.reports.*')">
                    {{ __('Admin Reports') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.analytics')" :active="request()->routeIs('admin.analytics')">
                    {{ __('Admin Analytics') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('admin.audit-logs.index')" :active="request()->routeIs('admin.audit-logs.*')">
                    {{ __('Audit Logs') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
            <div class="pt-4 pb-1 border-t border-gray-300">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <button
                    type="button"
                    data-theme-toggle
                    class="w-full text-left px-4 py-2 text-sm bg-transparent text-gray-900 hover:text-indigo-900 inline-flex items-center gap-2 dark:text-white dark:hover:text-white"
                >
                    <svg data-theme-icon-sun xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m0 13.5V21m9-9h-2.25M5.25 12H3m15.114 6.364-1.591-1.591M7.477 7.477 5.886 5.886m12.228 0-1.591 1.591M7.477 16.523l-1.591 1.591M15.75 12A3.75 3.75 0 1 1 8.25 12a3.75 3.75 0 0 1 7.5 0Z"/>
                    </svg>
                    <svg data-theme-icon-moon xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3a7 7 0 0 0 9.79 9.79Z"/>
                    </svg>
                    <span>Theme</span>
                </button>

                <x-responsive-nav-link :href="route('profile.show')">
                    {{ __('View Profile') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Edit Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
