<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
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
<body class="min-h-screen overflow-x-hidden bg-[radial-gradient(circle_at_20%_15%,_rgba(251,191,36,0.28),_transparent_38%),radial-gradient(circle_at_85%_12%,_rgba(251,146,60,0.23),_transparent_36%),linear-gradient(145deg,_#fffbeb_0%,_#fff7ed_45%,_#fff1f2_100%)] text-slate-800 dark:bg-[radial-gradient(circle_at_20%_15%,_rgba(251,191,36,0.16),_transparent_34%),radial-gradient(circle_at_85%_12%,_rgba(251,146,60,0.12),_transparent_30%),linear-gradient(145deg,_#020617_0%,_#0f172a_45%,_#111827_100%)] dark:text-slate-100" style="font-family: 'Manrope', sans-serif;">
    <main class="relative mx-auto flex min-h-screen w-full max-w-6xl items-center px-6 py-14 sm:px-10 sm:py-20">
        <div class="pointer-events-none absolute -top-8 left-8 h-28 w-28 rounded-full bg-amber-200/45 blur-2xl sm:h-36 sm:w-36 dark:bg-amber-300/20"></div>
        <div class="pointer-events-none absolute bottom-10 right-2 h-36 w-36 rounded-full bg-rose-200/45 blur-2xl sm:right-10 sm:h-44 sm:w-44 dark:bg-rose-300/20"></div>

        <section class="relative z-10 w-full">
            <div class="mb-7">
                <x-application-logo class="h-11 w-auto max-w-[8rem] max-[380px]:h-9 max-[380px]:max-w-[6.25rem] sm:h-14 sm:max-w-[10rem] lg:h-[4.5rem] lg:max-w-[13rem] xl:h-20 xl:max-w-[15rem]" />
            </div>

            <p class="mb-5 text-xs font-semibold uppercase tracking-[0.22em] text-amber-800 dark:text-amber-200">
                A calm space to heal, reflect, and grow
            </p>

            <h1 class="max-w-3xl text-4xl font-extrabold leading-[1.1] tracking-tight text-slate-900 max-[380px]:text-3xl sm:text-5xl lg:text-6xl dark:text-slate-100">
                Recenter your day with mindful mood tracking and restorative routines.
            </h1>

            <p class="mt-6 max-w-2xl text-base leading-relaxed text-slate-700 sm:text-lg dark:text-slate-300">
                Build emotional awareness, capture private reflections, and explore supportive routines shared by a thoughtful wellness community.
            </p>

            <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:items-center">
                <a href="{{ route('login') }}"
                   class="inline-flex items-center justify-center rounded-full bg-slate-900 px-8 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900">
                    Log In
                </a>

                <a href="{{ route('register') }}"
                   class="inline-flex items-center justify-center rounded-full bg-white/80 px-8 py-3 text-sm font-semibold text-slate-800 ring-1 ring-amber-200/80 backdrop-blur transition hover:bg-white dark:bg-slate-900/70 dark:text-slate-100 dark:ring-amber-300/25 dark:hover:bg-slate-900">
                    Register
                </a>
            </div>

            @auth
                <div class="mt-8 inline-flex flex-wrap items-center gap-2 rounded-full bg-white/75 px-4 py-2 text-sm font-medium text-slate-700 ring-1 ring-amber-200/70 dark:bg-slate-900/65 dark:text-slate-200 dark:ring-amber-300/25">
                    <span>You are already signed in.</span>
                    <a href="{{ Auth::user()?->role === 'admin' ? route('admin.dashboard') : route('dashboard') }}" class="font-semibold text-amber-700 underline underline-offset-2 dark:text-amber-200">Go to Dashboard</a>
                </div>
            @endauth
        </section>
    </main>
</body>
</html>
