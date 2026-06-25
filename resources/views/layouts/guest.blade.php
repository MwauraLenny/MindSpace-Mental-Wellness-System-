<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
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
                if (storedTheme === 'dark') {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        <style>
            html.dark body,
            html.dark .min-h-screen {
                background: #0f172a;
                color: #e5e7eb;
            }

            html.dark .bg-white\/95 {
                background-color: #111827 !important;
            }

            html.dark .text-gray-900,
            html.dark .text-teal-700 {
                color: #e5e7eb !important;
            }

            html.dark .ring-teal-100 {
                --tw-ring-color: #374151 !important;
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center px-4 py-8 bg-gradient-to-b from-teal-50 via-white to-emerald-50">
            <div class="text-center">
                <a href="/" class="inline-flex justify-center">
                    <x-application-logo class="w-16 h-16 fill-current text-teal-700" />
                </a>
                <p class="mt-3 text-sm font-medium tracking-wide text-teal-700">MindSpace Mental Wellness</p>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-white/95 shadow-lg ring-1 ring-teal-100 overflow-hidden sm:rounded-xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
