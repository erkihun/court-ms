<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name') }}</title>
    @php($settings = rescue(fn () => \App\Models\SystemSetting::cached(), null, false))
    @if (! empty($settings?->favicon_path))
        <link rel="icon" href="{{ asset('storage/'.$settings->favicon_path) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}">
    @endif
    {{-- Apply the saved theme before first paint so dark mode never flashes. Public pages are always light. --}}
    <script>
        (() => {
            const theme = localStorage.getItem('theme') || 'system';
            const publicPage = @json(str_starts_with($page['component'], 'Public/'));
            const dark = !publicPage && (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches));
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.dataset.theme = theme;
            document.documentElement.dataset.accent = localStorage.getItem('accent') || 'blue';
        })();
    </script>
    {{-- UI strings are fetched in parallel with the JS bundle. --}}
    <link rel="preload" as="fetch" crossorigin="anonymous" href="{{ $page['props']['app']['translationsUrl'] ?? '' }}">
    <script>window.Ziggy = @json(\App\Support\FrontendRoutes::payload());</script>
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.tsx', "resources/js/Pages/{$page['component']}.tsx"])
    @inertiaHead
</head>
<body class="min-h-screen font-ui antialiased">
    @inertia
</body>
</html>
