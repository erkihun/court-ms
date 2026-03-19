<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="themeSystem()" x-init="init()">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <script>
        (() => {
            const theme = localStorage.getItem('theme') || 'system';
            const dark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.dataset.theme = theme;
        })();
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->

    @vite(['resources/css/app.css','resources/js/app.js','resources/js/dashboard.js'])

</head>

<body class="font-sans antialiased text-[var(--text)]">
    <div class="min-h-screen bg-[var(--bg)] text-[var(--text)]">
        @include('layouts.navigation')
        @isset($header)
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 text-slate-900">
                {{ $header }}
            </div>
        </header>
        @endisset

        <main class="text-slate-900">
            {{ $slot }}
        </main>
    </div>
</body>


</html>
