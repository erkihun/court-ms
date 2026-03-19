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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

@php
    $variant = $attributes->get('variant', 'default');
@endphp

<body class="font-sans antialiased text-[var(--text)]">
    @if($variant === 'split-auth')
        <div class="guest-container min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.18),_transparent_32%),radial-gradient(circle_at_bottom_right,_rgba(249,115,22,0.14),_transparent_26%),linear-gradient(180deg,#e2e8f0_0%,#f8fafc_35%,#e2e8f0_100%)] dark:bg-[radial-gradient(circle_at_top_left,_rgba(59,130,246,0.14),_transparent_34%),radial-gradient(circle_at_bottom_right,_rgba(249,115,22,0.12),_transparent_30%),linear-gradient(180deg,#020617_0%,#0f172a_100%)]">
            {{ $slot }}
        </div>
    @else
        <div class="guest-container flex min-h-screen items-center justify-center bg-[radial-gradient(circle_at_top,_rgba(59,130,246,0.32),_transparent_38%),radial-gradient(circle_at_bottom_right,_rgba(249,115,22,0.16),_transparent_30%),linear-gradient(180deg,#020617_0%,#0f172a_100%)] px-4 py-8 sm:px-6">
            <div class="w-full overflow-hidden rounded-[1.75rem] border border-white/10 bg-white/95 px-6 py-6 text-slate-900 shadow-2xl shadow-slate-950/30 ring-1 ring-white/40 sm:mt-0 sm:max-w-md sm:px-8">
                {{ $slot }}
            </div>
        </div>
    @endif
</body>

</html>
