<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#111827">
        <meta name="color-scheme" content="light dark">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            (function () {
                const storedTheme = localStorage.getItem('mindspace-theme');
                const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                const shouldUseDark = storedTheme ? storedTheme === 'dark' : prefersDark;

                if (shouldUseDark) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        <style>
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
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gradient-to-b from-amber-50 via-orange-50 to-rose-100/60">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white/95 shadow-sm border-b border-amber-100">
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
