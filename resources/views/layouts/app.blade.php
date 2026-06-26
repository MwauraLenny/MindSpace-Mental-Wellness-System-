<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#111827">
        <meta name="color-scheme" content="light">
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
                background-color: #0f172a;
                color: #e5e7eb;
            }

            html.dark .bg-white {
                background-color: #111827 !important;
            }

            html.dark .bg-gray-50,
            html.dark .bg-gray-100 {
                background-color: #1f2937 !important;
            }

            html.dark .text-gray-900,
            html.dark .text-gray-800,
            html.dark .text-gray-700,
            html.dark .text-gray-600,
            html.dark .text-gray-500,
            html.dark .text-gray-400 {
                color: #e5e7eb !important;
            }

            html.dark .border-gray-100,
            html.dark .border-gray-200,
            html.dark .border-gray-300 {
                border-color: #374151 !important;
            }

            html.dark input,
            html.dark select,
            html.dark textarea {
                background-color: #111827;
                color: #e5e7eb;
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
