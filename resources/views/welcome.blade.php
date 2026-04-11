<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SKPrimeTech - Empowering Youth Governance</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Instrument Sans', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        },
                        colors: {
                            primary: {
                                50: '#fffbeb',
                                100: '#fef3c7',
                                200: '#fde68a',
                                300: '#fcd34d',
                                400: '#fbbf24',
                                500: '#f59e0b',
                                600: '#d97706',
                                700: '#b45309',
                                800: '#92400e',
                                900: '#78350f',
                                950: '#451a03',
                            }
                        }
                    }
                }
            }
        </script>
    @endif
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-dvh bg-gray-50 text-gray-950 font-sans antialiased dark:bg-gray-950 dark:text-white flex flex-col">
    <!-- Navbar -->
    <header class="sticky top-0 z-50 w-full border-b border-gray-200 bg-white/80 backdrop-blur dark:bg-gray-950/80 dark:border-gray-800">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <div class="flex items-center">
                <x-skprime-logo class="h-9 sm:h-10" img-class="h-9 w-auto sm:h-10" />
            </div>
            
            <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-600 dark:text-gray-400">
                <a href="#features" class="hover:text-primary-600 dark:hover:text-primary-500 transition-colors">Features</a>
                <a href="#about" class="hover:text-primary-600 dark:hover:text-primary-500 transition-colors">About</a>
            </nav>

            <div class="flex items-center gap-4">
                @if (auth()->check())
                    <a href="{{ route('filament.admin.pages.dashboard') }}" class="text-sm font-semibold text-gray-600 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-500 transition-colors">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('filament.admin.auth.login') }}" class="text-sm font-semibold text-gray-600 hover:text-primary-600 dark:text-gray-400 dark:hover:text-primary-500 transition-colors">
                        Sign in
                    </a>
                    <a href="{{ route('filament.admin.auth.login') }}" class="hidden sm:inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 transition-all">
                        Open Admin
                    </a>
                @endif
            </div>
        </div>
    </header>

    <main class="flex-grow">
        <!-- Hero Section -->
        <section class="relative isolate overflow-hidden bg-white dark:bg-gray-900">
            <div class="absolute inset-0 -z-10">
                <div class="absolute -top-40 left-1/2 h-[520px] w-[520px] -translate-x-1/2 rounded-full bg-primary-500/20 blur-3xl"></div>
                <div class="absolute -bottom-48 left-1/4 h-[520px] w-[520px] -translate-x-1/2 rounded-full bg-gray-900/10 blur-3xl dark:bg-white/10"></div>
            </div>
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="py-20 sm:py-24 lg:py-28">
                    <div class="grid grid-cols-1 gap-14 lg:grid-cols-12 lg:items-center">
                        <div class="lg:col-span-6">
                            <div class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white/70 px-3 py-1 text-xs font-semibold text-gray-700 backdrop-blur dark:border-gray-800 dark:bg-gray-950/50 dark:text-gray-200">
                                <span class="inline-flex h-2 w-2 rounded-full bg-primary-600"></span>
                                Built for SK operations and compliance
                            </div>
                            <h1 class="mt-6 text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-6xl">
                                A dashboard-first system for
                                <span class="text-primary-600">Sangguniang Kabataan</span>
                            </h1>
                            <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300">
                                Manage youth profiles, programs, and reports with a consistent admin experience powered by the same design system used in the dashboard—built into SKPrimeTech.
                            </p>
                            <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:items-center">
                                <a href="{{ route('filament.admin.auth.login') }}" class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 transition-all">
                                    Access Admin
                                </a>
                                <a href="#features" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white/60 px-4 py-2.5 text-sm font-semibold text-gray-900 shadow-sm hover:bg-white dark:border-gray-800 dark:bg-gray-950/40 dark:text-white dark:hover:bg-gray-950 transition-all">
                                    Explore features
                                </a>
                            </div>
                            <dl class="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div class="rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-950/40">
                                    <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">Profiles</dt>
                                    <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">Youth database</dd>
                                </div>
                                <div class="rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-950/40">
                                    <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">Programs</dt>
                                    <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">Events & tracking</dd>
                                </div>
                                <div class="rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-950/40">
                                    <dt class="text-xs font-semibold text-gray-500 dark:text-gray-400">Reports</dt>
                                    <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">One-click outputs</dd>
                                </div>
                            </dl>
                        </div>
                
                <!-- Dashboard Preview -->
                        <div class="lg:col-span-6">
                            <div class="rounded-2xl border border-gray-200 bg-white/70 p-3 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-950/40">
                                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900">
                                    <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-800 dark:bg-gray-950/50">
                                        <div class="flex items-center gap-2">
                                            <div class="h-3 w-3 rounded-full bg-red-500"></div>
                                            <div class="h-3 w-3 rounded-full bg-yellow-500"></div>
                                            <div class="h-3 w-3 rounded-full bg-green-500"></div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="h-8 w-32 rounded-lg bg-gray-200/80 dark:bg-gray-800/80"></div>
                                            <div class="h-8 w-8 rounded-lg bg-primary-600/20 ring-1 ring-inset ring-primary-600/30"></div>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-12 gap-4 bg-gray-50 p-4 dark:bg-gray-950/30">
                                        <div class="col-span-4 hidden md:block">
                                            <div class="space-y-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                                <div class="h-3 w-20 rounded bg-gray-200 dark:bg-gray-800"></div>
                                                <div class="h-8 w-full rounded-lg bg-gray-200/80 dark:bg-gray-800/80"></div>
                                                <div class="h-8 w-full rounded-lg bg-gray-200/80 dark:bg-gray-800/80"></div>
                                                <div class="h-8 w-full rounded-lg bg-gray-200/80 dark:bg-gray-800/80"></div>
                                            </div>
                                        </div>
                                        <div class="col-span-12 md:col-span-8">
                                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                                    <div class="h-3 w-16 rounded bg-gray-200 dark:bg-gray-800"></div>
                                                    <div class="mt-3 h-10 w-28 rounded-lg bg-primary-600/20 ring-1 ring-inset ring-primary-600/30"></div>
                                                    <div class="mt-3 h-3 w-40 rounded bg-gray-200 dark:bg-gray-800"></div>
                                                </div>
                                                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                                    <div class="h-3 w-16 rounded bg-gray-200 dark:bg-gray-800"></div>
                                                    <div class="mt-3 h-10 w-28 rounded-lg bg-primary-600/20 ring-1 ring-inset ring-primary-600/30"></div>
                                                    <div class="mt-3 h-3 w-40 rounded bg-gray-200 dark:bg-gray-800"></div>
                                                </div>
                                                <div class="sm:col-span-2 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                                    <div class="h-3 w-24 rounded bg-gray-200 dark:bg-gray-800"></div>
                                                    <div class="mt-4 h-40 w-full rounded-lg bg-gray-200/70 dark:bg-gray-800/70"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 flex items-center justify-between px-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span>Dashboard-style layout</span>
                                    <span class="font-medium text-primary-700 dark:text-primary-400">Primary: Amber</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="border-t border-gray-200 bg-gray-50 py-20 dark:border-gray-800 dark:bg-gray-950">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-base font-semibold leading-7 text-primary-700 dark:text-primary-400">Features</h2>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">Everything you need to manage your SK</p>
                    <p class="mt-4 text-base leading-7 text-gray-600 dark:text-gray-300">
                        A consistent interface from homepage to dashboard, with role-based access and reporting built in.
                    </p>
                </div>
                <div class="mx-auto mt-14 grid max-w-2xl grid-cols-1 gap-6 lg:mt-16 lg:max-w-none lg:grid-cols-3">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-600">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Youth profiling</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Secure records, demographics, and searchable lists.</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-600">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Automated reports</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Generate outputs in minutes, not days.</p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-600">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Program management</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">Plan activities, track attendance, and evaluate impact.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="about" class="border-t border-gray-200 bg-white py-20 dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-10 lg:grid-cols-12 lg:items-start">
                    <div class="lg:col-span-5">
                        <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">Designed like the dashboard</h2>
                        <p class="mt-4 text-base leading-7 text-gray-600 dark:text-gray-300">
                            The homepage now uses the same core visual language as the admin panel: clean surfaces, subtle borders, consistent spacing, and an amber primary color.
                        </p>
                        <div class="mt-8">
                            <a href="{{ route('filament.admin.auth.login') }}" class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 transition-all">
                                Go to Admin
                            </a>
                        </div>
                    </div>
                    <div class="lg:col-span-7">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Consistent branding</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">Same font and primary palette across public and admin UI.</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Clear hierarchy</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">Dashboard-like cards and spacing reduce visual noise.</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Responsive by default</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">Optimized layout for mobile, tablet, and desktop screens.</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-6 shadow-sm dark:border-gray-800 dark:bg-gray-950">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Single entrypoint</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">Access Admin link is always available (no missing routes).</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="py-4 text-center text-xs text-gray-500 dark:text-gray-400 w-full border-t border-gray-200 dark:border-gray-800 bg-white/50 dark:bg-gray-900/50 backdrop-blur-xl mt-auto">F
        <div class="px-4 mx-auto max-w-7xl w-full flex flex-col md:flex-row justify-between items-center gap-2">
            <x-skprime-logo class="h-8" img-class="h-8 w-auto" />
            <div class="flex items-center gap-1">
                <span>&copy; {{ date('Y') }}</span>
                <span class="mx-1">•</span>
                <span>v{{ config('app.version') }}</span>
                <span class="mx-1">•</span>
                <span>Developer <a href="https://github.com/rjmolina13" target="_blank" class="text-primary-600 hover:text-primary-500 font-medium">@rjmolina13</a></span>
            </div>
        </div>
    </footer>
</body>
</html>
