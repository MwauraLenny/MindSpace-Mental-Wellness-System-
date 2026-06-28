<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-slate-100">Admin Control Access</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-slate-300">Restricted sign in for moderators and platform administrators.</p>
    </div>

    <div class="mb-5 rounded-xl border border-orange-200 bg-orange-50 px-4 py-3 text-sm text-orange-900 dark:border-amber-300/30 dark:bg-amber-300/10 dark:text-amber-100">
        This portal is monitored. Unauthorized access attempts are logged.
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('admin.login.submit') }}">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Admin Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 sm:me-auto dark:text-slate-300 dark:hover:text-amber-200 dark:focus:ring-offset-slate-900" href="{{ route('login') }}">
                {{ __('Back to user login') }}
            </a>

            <div class="flex items-center gap-4 sm:gap-5">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 dark:text-slate-300 dark:hover:text-amber-200 dark:focus:ring-offset-slate-900" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-primary-button>
                    {{ __('Admin Log in') }}
                </x-primary-button>
            </div>
        </div>
    </form>
</x-guest-layout>
