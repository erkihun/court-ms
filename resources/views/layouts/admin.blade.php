@use('Laravel\Pulse\Facades\Pulse')
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

        document.addEventListener('alpine:init', () => {
            Alpine.store('toasts', {
                items: [],
                _id: 0,

                initFromServer(serverToasts) {
                    serverToasts.forEach((t, i) => {
                        const id = ++this._id;
                        this.items.push({ id, message: t.message, type: t.type, details: t.details || [], show: true });
                        setTimeout(() => this.dismiss(id), 4500 + i * 400);
                    });
                },

                add(message, type = 'success', duration = 4500) {
                    const id = ++this._id;
                    this.items.push({ id, message, type, details: [], show: true });
                    setTimeout(() => this.dismiss(id), duration);
                },

                dismiss(id) {
                    const item = this.items.find(t => t.id === id);
                    if (item) item.show = false;
                    setTimeout(() => { this.items = this.items.filter(t => t.id !== id); }, 350);
                }
            });

            window.toast = (msg, type, dur) => Alpine.store('toasts').add(msg, type, dur);
        });
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->

    @vite(['resources/css/app.css','resources/js/app.js','resources/js/dashboard.js'])
    {!! Pulse::css() !!}
    @livewireStyles

    {!! Pulse::js() !!}
    @livewireScriptConfig

</head>

<body class="font-sans font-ui antialiased text-slate-800">
    <div class="min-h-screen bg-gray-100 text-slate-800">
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

    @include('partials.admin-toasts')
</body>


</html>
