<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MindSpace') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            (function () {
                const storedTheme = localStorage.getItem('mindspace-theme');
                if (storedTheme === 'dark') {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>
    </head>
    <body class="font-sans text-gray-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <div class="min-h-screen flex flex-col sm:justify-center items-center px-4 py-8 bg-gradient-to-b from-amber-50 via-orange-50 to-rose-100/70 dark:bg-[radial-gradient(circle_at_top,_rgba(251,191,36,0.12),_rgba(15,23,42,0.98)_52%)] dark:from-transparent dark:via-transparent dark:to-transparent">
            <div class="text-center">
                <a href="/" class="inline-flex items-center gap-3 rounded-2xl border border-amber-200/80 bg-white/90 px-5 py-3 shadow-sm transition hover:shadow-md dark:border-amber-300/20 dark:bg-slate-900/90 dark:shadow-amber-500/10 dark:hover:border-amber-300/30">
                    <x-application-logo class="w-20 h-20" />
                    <span class="text-left leading-tight">
                        <span class="block text-2xl font-bold tracking-tight text-stone-800 dark:text-amber-100">MindSpace</span>
                        <span class="block text-xs font-semibold uppercase tracking-[0.08em] text-amber-700 dark:text-amber-300">Mental Wellness System</span>
                    </span>
                </a>
                <p class="mt-3 text-sm font-medium tracking-wide text-amber-700 dark:text-amber-200/90">A calm space for healing, reflection, and growth</p>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-white/95 shadow-lg ring-1 ring-amber-100 overflow-hidden sm:rounded-xl dark:bg-slate-900/92 dark:ring-amber-300/15 dark:shadow-2xl dark:shadow-black/45">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
