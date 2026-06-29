<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MindSpace') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            (function () {
                const THEME_KEY = 'mindspace-theme-v2';
                const LEGACY_THEME_KEY = 'mindspace-theme';
                const THEME_RESET_KEY = 'mindspace-theme-reset-version';
                const THEME_RESET_VERSION = '2026-06-29';
                let storedTheme = localStorage.getItem(THEME_KEY);

                if (localStorage.getItem(THEME_RESET_KEY) !== THEME_RESET_VERSION) {
                    localStorage.setItem(THEME_KEY, 'light');
                    localStorage.removeItem(LEGACY_THEME_KEY);
                    localStorage.setItem(THEME_RESET_KEY, THEME_RESET_VERSION);
                    storedTheme = 'light';
                }

                if (storedTheme !== 'dark' && storedTheme !== 'light') {
                    localStorage.setItem(THEME_KEY, 'light');
                    localStorage.removeItem(LEGACY_THEME_KEY);
                    storedTheme = 'light';
                }

                const shouldUseDark = storedTheme === 'dark';
                document.documentElement.classList.toggle('dark', shouldUseDark);
            })();
        </script>
    </head>
    <body class="text-slate-800 antialiased dark:text-slate-100" style="font-family: 'Manrope', sans-serif;">
        <div class="min-h-screen flex flex-col sm:justify-center items-center px-4 py-8 bg-[radial-gradient(circle_at_20%_15%,_rgba(251,191,36,0.26),_transparent_38%),radial-gradient(circle_at_85%_12%,_rgba(251,146,60,0.22),_transparent_36%),linear-gradient(145deg,_#fffbeb_0%,_#fff7ed_45%,_#fff1f2_100%)] dark:bg-[radial-gradient(circle_at_20%_15%,_rgba(251,191,36,0.14),_transparent_36%),radial-gradient(circle_at_85%_12%,_rgba(251,146,60,0.1),_transparent_32%),linear-gradient(145deg,_#020617_0%,_#0f172a_45%,_#111827_100%)]">
            <div class="text-center">
                <a href="/" class="inline-flex items-center">
                    <x-application-logo class="h-9 w-auto max-w-[6.25rem] max-[380px]:h-8 max-[380px]:max-w-[5.1rem] sm:h-11 sm:max-w-[7.5rem] lg:h-12 lg:max-w-[8.5rem]" />
                </a>
                <p class="mt-3 text-sm font-medium tracking-wide text-amber-800 dark:text-amber-200/95">A calm space for healing, reflection, and growth</p>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-white/80 shadow-xl ring-1 ring-amber-100/80 backdrop-blur-sm overflow-hidden sm:rounded-2xl dark:bg-slate-900/75 dark:ring-amber-300/20 dark:shadow-2xl dark:shadow-black/45">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
