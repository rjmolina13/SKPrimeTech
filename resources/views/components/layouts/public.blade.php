<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SKPrime') }} - Shared Access Portal</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
    @filamentStyles
    <!-- Load Filament Core CSS (needed for standalone tables/components) -->
    <link rel="stylesheet" href="{{ asset('css/filament/filament/app.css') }}">
    
    @vite(['resources/css/app.css', 'resources/css/filament/custom.css'])
</head>
<body class="fi-body min-h-dvh bg-gray-50 text-gray-950 font-sans antialiased dark:bg-gray-950 dark:text-white flex flex-col">
    <!-- Navbar -->
    <header class="sticky top-0 z-30 w-full border-b border-gray-200 bg-white/80 backdrop-blur dark:bg-gray-950/80 dark:border-gray-800">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <div class="flex items-center">
                <a href="{{ url('/') }}" class="block">
                    <x-skprime-logo class="h-9 sm:h-10" img-class="h-9 w-auto sm:h-10" />
                </a>
            </div>
            
            <div class="flex items-center gap-4">
                <span class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white/70 px-3 py-1 text-xs font-semibold text-gray-700 backdrop-blur dark:border-gray-800 dark:bg-gray-950/50 dark:text-gray-200">
                    <span class="inline-flex h-2 w-2 rounded-full bg-primary-600"></span>
                    Public Access
                </span>
            </div>
        </div>
    </header>

    <main class="flex-grow pb-16">
        {{ $slot }}
    </main>

    <!-- Footer -->
    @include('filament.footer')

    @filamentScripts
    @vite('resources/js/app.js')
</body>
</html>
