<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'MindSpace') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-amber-50 via-orange-50 to-rose-100/70 text-slate-800">
    <main class="mx-auto flex min-h-screen w-full max-w-5xl flex-col items-center justify-center px-6 py-12">
        <section class="w-full rounded-3xl border border-amber-200/70 bg-white/95 p-8 shadow-xl backdrop-blur sm:p-12">
            <div class="mb-6 inline-flex items-center gap-4 rounded-2xl border border-amber-200/80 bg-amber-50/60 px-5 py-4 shadow-sm">
                <x-application-logo class="h-20 w-auto" />
                <div class="leading-tight">
                    <p class="text-3xl font-bold tracking-tight text-stone-800">MindSpace</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-[0.1em] text-amber-700">Mental Wellness System</p>
                </div>
            </div>

            <p class="mb-4 inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-700">
                A calm space to heal, reflect, and grow
            </p>

            <h1 class="text-3xl font-bold leading-tight text-slate-900 sm:text-5xl">
                Welcome to your calm corner for mood tracking and growth.
            </h1>

            <p class="mt-4 max-w-2xl text-base text-slate-600 sm:text-lg">
                Track your mood, write private journal entries, and discover routines shared by people who understand your journey.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                <a href="{{ route('login') }}"
                   class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-amber-700">
                    Log In
                </a>

                <a href="{{ route('register') }}"
                   class="inline-flex items-center justify-center rounded-xl border border-amber-600 px-6 py-3 text-sm font-semibold text-amber-700 transition hover:bg-amber-50">
                    Register
                </a>
            </div>

            @auth
                <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    You are already signed in.
                    <a href="{{ Auth::user()?->role === 'admin' ? route('admin.dashboard') : route('dashboard') }}" class="font-semibold underline">Go to Dashboard</a>
                </div>
            @endauth
        </section>
    </main>
</body>
</html>
