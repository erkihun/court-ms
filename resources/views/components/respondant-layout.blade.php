@props(['title' => __('app.court_portal')])

@php
$layout = $publicLayout ?? [];
$systemSettings = $layout['systemSettings'] ?? null;
$brandName = $layout['brandName'] ?? config('app.name', __('app.court_ms'));
$shortName = $layout['shortName'] ?? $brandName;
$logoPath = $layout['logoPath'] ?? null;
$footerText = $layout['footerText'] ?? __('app.all_rights_reserved');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $title }} | {{ $brandName }}</title>
    @if(!empty($systemSettings?->favicon_path))
    <link rel="icon" href="{{ asset('storage/'.$systemSettings->favicon_path) }}">
    @endif
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50 text-slate-800 flex flex-col">
    {{-- Header --}}
    <header class="sticky top-0 z-40 bg-blue-800 text-white border-b border-blue-900/50 shadow">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ auth('respondent')->check() ? route('respondent.dashboard') : route('respondent.login') }}"
                class="flex items-center gap-3">
                @if($logoPath)
                <div class=" rounded-lg flex items-center justify-center ">
                    <img src="{{ asset('storage/'.$logoPath) }}"
                        alt="{{ $brandName }}"
                        class="h-9 w-auto object-contain">
                </div>
                @else
                <div class="h-10 w-10 rounded-lg bg-blue-900/70 border border-blue-700/60 flex items-center justify-center font-semibold uppercase tracking-wide">
                    {{ \Illuminate\Support\Str::of($shortName)->substr(0,2) }}
                </div>
                @endif
                <div class="leading-tight">
                    <p class="text-[11px] uppercase tracking-wide text-white/70">{{ __('Respondent Portal') }}</p>
                    <h1 class="text-lg font-semibold">{{ $shortName }}</h1>
                </div>
            </a>

            <nav x-data="{ open:false }" class="relative">
                <ul class="hidden sm:flex items-center gap-3 text-sm">
                    @auth('respondent')
                    <li>
                        <a href="{{ route('respondent.dashboard') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-white/30 text-white/90 hover:bg-white/10">
                            {{ __('Dashboard') }}
                        </a>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('respondent.logout') }}">
                            @csrf
                            <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-white/30 text-white/90 hover:bg-white/10">
                                {{ __('Logout') }}
                            </button>
                        </form>
                    </li>
                    @else
                    <li>
                        <a href="{{ route('respondent.login') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-white/30 text-white/90 hover:bg-white/10">
                            {{ __('Login') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('respondent.register') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-orange-500 text-white font-semibold hover:bg-orange-600 border border-orange-400">
                            {{ __('Register') }}
                        </a>
                    </li>
                    @endauth
                </ul>

                {{-- Mobile trigger --}}
                <button @click="open=!open" class="sm:hidden inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-white/30 text-white">
                    {{ __('Menu') }}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                {{-- Mobile menu --}}
                <div x-cloak x-show="open" @click.outside="open=false"
                    class="sm:hidden absolute right-0 mt-2 w-56 rounded-md border border-slate-200 bg-white shadow-lg text-slate-700">
                    <ul class="py-2 text-sm">
                        @auth('respondent')
                        <li>
                            <a href="{{ route('respondent.dashboard') }}"
                                class="block px-4 py-2 hover:bg-slate-50">
                                {{ __('Dashboard') }}
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('respondent.logout') }}" class="px-4 py-2">
                                @csrf
                                <button class="w-full text-left hover:text-blue-700">
                                    {{ __('Logout') }}
                                </button>
                            </form>
                        </li>
                        @else
                        <li>
                            <a href="{{ route('respondent.login') }}"
                                class="block px-4 py-2 hover:bg-slate-50">
                                {{ __('Login') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('respondent.register') }}"
                                class="block px-4 py-2 hover:bg-slate-50">
                                {{ __('Register') }}
                            </a>
                        </li>
                        @endauth
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    {{-- Flash messages --}}
    <div class="max-w-7xl mx-auto px-4 pt-4">
        @if(session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm flex items-start gap-2">
            <span class="mt-0.5">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </span>
            <span>{{ session('success') }}</span>
        </div>
        @endif
        @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm flex items-start gap-2">
            <span class="mt-0.5">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M12 9v3m0 3h.01M12 5a7 7 0 100 14 7 7 0 000-14z" />
                </svg>
            </span>
            <span>{{ session('error') }}</span>
        </div>
        @endif
    </div>

    {{-- Page content --}}
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 py-8">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="mt-10 border-top border-slate-200 bg-white ">
        <div class="max-w-7xl mx-auto px-4 py-5 sm:text-sm text-slate-500 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <div>
                © {{ date('Y') }} <span class="font-semibold text-blue-700">{{ $brandName }}</span>.
                <span class="text-slate-500">{{ $footerText }}</span>
            </div>
            <div class="flex items-center gap-2 text-[11px] uppercase tracking-wide text-slate-400">
                <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>
                <span>{{ __('Respondent Portal') }}</span>
            </div>
        </div>
    </footer>
</body>

</html>