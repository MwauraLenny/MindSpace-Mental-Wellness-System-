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
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Password" />

            <div class="relative mt-1">
                <x-text-input id="password" class="block w-full pe-12"
                                type="password"
                                name="password"
                                maxlength="20"
                                required autocomplete="current-password" />
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-amber-300 text-amber-600 shadow-sm focus:ring-amber-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Remember me</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @unless ($isAdminLogin)
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 me-auto" href="{{ route('register') }}">
                    Create an account
                </a>
            @else
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 me-auto" href="{{ route('login') }}">
                    User login
                </a>
            @endunless

            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500" href="{{ route('password.request') }}">
                    Forgot your password?
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ $isAdminLogin ? 'Admin Log in' : 'Log in' }}
            </x-primary-button>
        </div>

        @unless ($isAdminLogin)
            <div class="mt-4 text-center">
                <a href="{{ route('admin.login') }}" class="inline-flex items-center text-sm font-medium text-amber-700 hover:text-amber-800">
                    Admin portal login
                </a>
            </div>
        @endunless
    </form>

</x-guest-layout>
