<x-guest-layout>
    @php
        $isAdminLogin = $isAdminLogin ?? false;
    @endphp

    <div class="mb-6">
        @if ($isAdminLogin)
            <h1 class="text-2xl font-semibold text-gray-900">Admin Sign In</h1>
            <p class="mt-1 text-sm text-gray-600">Use your admin account to access moderation tools.</p>
        @else
            <h1 class="text-2xl font-semibold text-gray-900">Welcome back to MindSpace</h1>
            <p class="mt-1 text-sm text-gray-600">Log in to continue your wellness journey.</p>
        @endif
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ $isAdminLogin ? route('admin.login.submit') : route('login') }}">
        @csrf

        @if ($isAdminLogin)
            <input type="hidden" name="admin_login" value="1">
        @endif

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @unless ($isAdminLogin)
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 me-auto" href="{{ route('register') }}">
                    {{ __('Create an account') }}
                </a>
            @else
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 me-auto" href="{{ route('login') }}">
                    {{ __('User login') }}
                </a>
            @endunless

            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ $isAdminLogin ? __('Admin Log in') : __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
