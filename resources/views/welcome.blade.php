<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'MindSpace') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-teal-100 text-slate-800">
    <main class="mx-auto flex min-h-screen w-full max-w-5xl flex-col items-center justify-center px-6 py-12">
        <section class="w-full rounded-3xl border border-emerald-100 bg-white/90 p-8 shadow-xl backdrop-blur sm:p-12">
            <p class="mb-4 inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-700">
                MindSpace Mental Wellness
            </p>

            <h1 class="text-3xl font-bold leading-tight text-slate-900 sm:text-5xl">
                Welcome to your calm corner for mood tracking and growth.
            </h1>

            <p class="mt-4 max-w-2xl text-base text-slate-600 sm:text-lg">
                Track your mood, write private journal entries, and discover routines shared by people who understand your journey.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center">
                <a href="{{ route('login') }}"
                   class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                    Log In
                </a>

                <a href="{{ route('register') }}"
                   class="inline-flex items-center justify-center rounded-xl border border-emerald-600 px-6 py-3 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50">
                    Register
                </a>
            </div>

            @auth
                <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    You are already signed in.
                    <a href="{{ route('dashboard') }}" class="font-semibold underline">Go to Dashboard</a>
                </div>
            @endauth
        </section>
    </main>
</body>
</html>
