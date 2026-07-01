<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#0f172a">
        <meta name="color-scheme" content="light dark">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Mindspace') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
        <link rel="shortcut icon" href="{{ asset('logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
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

        <style>
            [x-cloak] {
                display: none !important;
            }

            html.dark body,
            html.dark .min-h-screen {
                background-color: #020617;
                color: #e2e8f0;
            }

            html.dark .bg-white {
                background-color: #0f172a !important;
            }

            html.dark .bg-gray-50,
            html.dark .bg-gray-100 {
                background-color: #1e293b !important;
            }

            html.dark .text-gray-900 {
                color: #f8fafc !important;
            }

            html.dark .text-gray-800 {
                color: #e2e8f0 !important;
            }

            html.dark .text-gray-700 {
                color: #cbd5e1 !important;
            }

            html.dark .text-gray-600 {
                color: #94a3b8 !important;
            }

            html.dark .text-gray-500 {
                color: #64748b !important;
            }

            html.dark .text-gray-400 {
                color: #475569 !important;
            }

            html.dark .text-slate-900,
            html.dark .text-slate-800,
            html.dark .text-slate-700,
            html.dark .text-slate-600 {
                color: #e2e8f0 !important;
            }

            html.dark .border-gray-100,
            html.dark .border-gray-200,
            html.dark .border-gray-300 {
                border-color: #334155 !important;
            }

            html.dark input,
            html.dark select,
            html.dark textarea {
                background-color: #0b1220;
                color: #e2e8f0;
            }

            html.dark .shadow-sm,
            html.dark .shadow-lg {
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35) !important;
            }

            html.dark [class*='bg-amber-50'] {
                background-color: rgba(251, 191, 36, 0.1) !important;
            }

            html.dark [class*='border-amber-100'],
            html.dark [class*='border-amber-200'] {
                border-color: rgba(251, 191, 36, 0.28) !important;
            }
        </style>
    </head>
    <body class="antialiased text-slate-800 dark:text-slate-100" style="font-family: 'Manrope', sans-serif;">
        <div class="min-h-screen bg-[radial-gradient(circle_at_20%_15%,_rgba(251,191,36,0.24),_transparent_36%),radial-gradient(circle_at_85%_10%,_rgba(251,146,60,0.2),_transparent_34%),linear-gradient(145deg,_#fffbeb_0%,_#fff7ed_45%,_#fff1f2_100%)] dark:bg-[radial-gradient(circle_at_15%_10%,_rgba(251,191,36,0.15),_transparent_34%),radial-gradient(circle_at_85%_15%,_rgba(251,146,60,0.12),_transparent_32%),linear-gradient(145deg,_#020617_0%,_#0f172a_45%,_#111827_100%)]">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white/75 shadow-sm border-b border-amber-100 backdrop-blur dark:bg-slate-900/65 dark:border-amber-300/20">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

    </body>
</html>
