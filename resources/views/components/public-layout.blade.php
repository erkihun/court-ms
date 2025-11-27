@props(['title' => __('app.court_portal'), 'hideFooter' => false])

@php
$layout = $publicLayout ?? [];
$systemSettings = $layout['systemSettings'] ?? null;
$brandName = $layout['brandName'] ?? config('app.name', __('app.court_ms'));
$shortName = $layout['shortName'] ?? $brandName;
$logoPath = $layout['logoPath'] ?? null;
$footerText = $layout['footerText'] ?? __('app.all_rights_reserved');
$notificationCount = $layout['notificationCount'] ?? 0;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title>{{ $title }} | {{ $brandName }}</title>

    {{-- Optional favicon if you later store it in system_settings --}}
    @if(!empty($systemSettings?->favicon_path))
    <link rel="icon" href="{{ asset('storage/'.$systemSettings->favicon_path) }}">
    @endif

    @vite(['resources/css/app.css','resources/js/app.js'])
    @stack('head')

</head>

<body class="min-h-screen bg-slate-50 text-slate-800">

    {{-- Header / Nav --}}
    <header class="sticky top-0 z-40 bg-blue-800 text-white border-b border-blue-900/60 shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ auth('applicant')->check() ? route('applicant.dashboard') : route('applicant.login') }}"
                class="flex items-center gap-2">
                <div class="flex items-center gap-2">
                    @if($logoPath)
                    <div class=" rounded-lg flex items-center justify-center ">
                        <img src="{{ asset('storage/'.$logoPath) }}"
                            alt="{{ $brandName }}"
                            class="h-9 w-auto object-contain">
                    </div>
                    @else
                    <div class="h-9 w-9 rounded-lg bg-blue-900/60 flex items-center justify-center border border-blue-700/80  font-semibold uppercase tracking-wide">
                        {{ \Illuminate\Support\Str::of($shortName)->substr(0,2) }}
                    </div>
                    @endif

                    <div class="flex flex-col leading-tight">
                        <span class="font-semibold text-base md:text-lg">
                            {{ $shortName }}
                        </span>
                        <span class="text-[11px] md: text-blue-100">
                            {{ __('app.court_portal') }}
                        </span>
                    </div>
                </div>
            </a>

            <nav x-data="{ open:false }" class="relative">
                {{-- Desktop --}}
                <ul class="hidden md:flex items-center gap-4 text-sm">
                    {{-- Language Switcher --}}
                    <li x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-blue-600 bg-blue-700  font-medium hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-800 focus:ring-orange-400">
                            <span class="fi fi-{{ app()->getLocale() == 'am' ? 'et' : 'us' }}"></span>
                            <span>{{ __('app.Language') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 9l6 6 6-6" />
                            </svg>
                        </button>

                        <div x-cloak x-show="open" @click.outside="open = false"
                            class="absolute right-0 mt-2 w-36 rounded-md border border-slate-200 bg-white shadow-lg z-50">
                            <div class="p-2 space-y-1 text-slate-700 text-sm">
                                <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}"
                                    class="flex items-center gap-2 w-full px-3 py-2 rounded hover:bg-slate-50 {{ app()->getLocale() == 'en' ? 'bg-blue-50 text-blue-700' : '' }}">
                                    <span class="fi fi-us"></span>
                                    {{ __('app.English') }}
                                </a>
                                <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}"
                                    class="flex items-center gap-2 w-full px-3 py-2 rounded hover:bg-slate-50 {{ app()->getLocale() == 'am' ? 'bg-orange-50 text-orange-700' : '' }}">
                                    <span class="fi fi-et"></span>
                                    {{ __('app.Amharic') }}
                                </a>
                            </div>
                        </div>
                    </li>

                    @if(auth('applicant')->check())
                    {{-- Common base style for nav items (desktop) --}}
                    @php
                    $navBase = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full font-medium transition-colors';
                    $navIdle = 'text-blue-50 hover:bg-blue-700 hover:text-white';
                    $navActive = 'bg-white text-blue-800';
                    $navDangerActive = 'bg-orange-500 text-white';
                    @endphp

                    <li>
                        <a href="{{ route('applicant.dashboard') }}"
                            class="{{ $navBase }} {{ request()->routeIs('applicant.dashboard') ? $navActive : $navIdle }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            {{ __('app.home') }}
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('applicant.cases.index') }}"
                            class="{{ $navBase }} {{ request()->routeIs('applicant.cases.*') ? $navActive : $navIdle }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            {{ __('app.my_cases') }}
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('applicant.cases.create') }}"
                            class="{{ $navBase }} {{ request()->routeIs('applicant.cases.create') ? $navDangerActive : $navIdle }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            {{ __('app.new_case') }}
                        </a>
                    </li>

                    {{-- Notifications (desktop) --}}
                    <li x-data="{ bell:false }" class="relative">
                        <button @click="bell=!bell"
                            class="relative inline-flex items-center gap-1.5 rounded-full border border-blue-500 px-3 py-1.5 bg-blue-700  font-medium hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-800 focus:ring-orange-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                    d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0h6z" />
                            </svg>

                            @if($notificationCount > 0)
                            <span class="absolute -top-1 -right-1 grid h-5 min-w-[20px] place-items-center rounded-full bg-orange-500 px-1 text-[11px] font-semibold text-white">
                                {{ $notificationCount > 9 ? '9+' : $notificationCount }}
                            </span>
                            @endif
                        </button>

                        {{-- Dropdown --}}
                        <div x-cloak x-show="bell" @click.outside="bell=false"
                            class="absolute right-0 mt-2 w-[28rem] max-w-[90vw] rounded-md border border-slate-200 bg-white shadow-xl">
                            @include('partials.applicant-notifications')
                        </div>
                    </li>

                    {{-- Profile --}}
                    <li>
                        <a href="{{ route('applicant.profile.edit') }}"
                            class="{{ $navBase }} {{ request()->routeIs('applicant.profile.*') ? $navActive : $navIdle }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ __('app.profile') }}
                        </a>
                    </li>

                    {{-- Logout --}}
                    <li>
                        <form method="POST" action="{{ route('applicant.logout') }}">
                            @csrf
                            <button
                                class="{{ $navBase }} {{ $navIdle }} border border-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                {{ __('app.logout') }}
                            </button>
                        </form>
                    </li>
                    @else
                    <li>
                        <a href="{{ route('applicant.register') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full  font-semibold bg-orange-500 text-white hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-blue-800 focus:ring-orange-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            {{ __('app.register') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('applicant.login') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full  font-semibold border border-white/60 text-white hover:bg-blue-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            {{ __('app.login') }}
                        </a>
                    </li>
                    @endif
                </ul>

                {{-- Mobile trigger --}}
                <button @click="open = !open"
                    class="md:hidden inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-blue-600 bg-blue-700  font-medium hover:bg-blue-600"
                    aria-label="{{ __('app.menu') }}" aria-haspopup="true" :aria-expanded="open">
                    {{ __('app.menu') }}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                {{-- Mobile menu --}}
                <div x-cloak x-show="open" @click.outside="open=false"
                    class="md:hidden absolute right-0 mt-2 w-64 rounded-md border border-slate-200 bg-white shadow-xl text-slate-700">
                    <ul class="py-2 text-sm">
                        {{-- Mobile Language Switcher --}}
                        <li class="border-b border-slate-100 pb-2 mb-2">
                            <div class="px-4 pt-2 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                                {{ __('app.language') }}
                            </div>
                            <div class="px-4 pb-2 flex gap-2">
                                <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}"
                                    class="flex-1 px-2 py-1  rounded-full border text-center
                                   {{ app()->getLocale() == 'en' ? 'bg-blue-600 text-white border-blue-600' : 'bg-slate-50 border-slate-200' }}">
                                    English
                                </a>
                                <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}"
                                    class="flex-1 px-2 py-1  rounded-full border text-center
                                   {{ app()->getLocale() == 'am' ? 'bg-orange-500 text-white border-orange-500' : 'bg-slate-50 border-slate-200' }}">
                                    አማርኛ
                                </a>
                            </div>
                        </li>

                        <li>
                            <a href="{{ route('applicant.dashboard') }}"
                                class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 {{ request()->routeIs('applicant.dashboard') ? 'text-blue-700 font-medium' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                </svg>
                                {{ __('app.home') }}
                            </a>
                        </li>

                        @if(auth('applicant')->check())
                        <li>
                            <a href="{{ route('applicant.cases.index') }}"
                                class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 {{ request()->routeIs('applicant.cases.*') ? 'text-blue-700 font-medium' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                {{ __('app.my_cases') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('applicant.cases.create') }}"
                                class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 {{ request()->routeIs('applicant.cases.create') ? 'text-orange-600 font-medium' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                {{ __('app.new_case') }}
                            </a>
                        </li>

                        {{-- Mobile: notifications (inline list) --}}
                        <li x-data="{ bell:false }" class="relative">
                            <button @click="bell=!bell"
                                class="flex w-full items-center justify-between px-4 py-2 hover:bg-slate-50">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                            d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0h6z" />
                                    </svg>
                                    {{ __('app.notifications') }}
                                </div>
                                @if($notificationCount > 0)
                                <span class="ml-2 inline-flex items-center justify-center rounded-full bg-orange-500 px-2 py-0.5 text-[11px] font-semibold text-white">
                                    {{ $notificationCount > 9 ? '9+' : $notificationCount }}
                                </span>
                                @endif
                            </button>
                            <div x-cloak x-show="bell" class="px-2 pb-2">
                                <div class="rounded-md border border-slate-200 bg-white max-h-80 overflow-auto">
                                    @include('partials.applicant-notifications')
                                </div>
                            </div>
                        </li>

                        <li>
                            <a href="{{ route('applicant.profile.edit') }}"
                                class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 {{ request()->routeIs('applicant.profile.*') ? 'text-blue-700 font-medium' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ __('app.profile') }}
                            </a>
                        </li>

                        <li>
                            <form method="POST" action="{{ route('applicant.logout') }}">
                                @csrf
                                <button class="flex items-center gap-2 w-full text-left px-4 py-2 hover:bg-slate-50">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    {{ __('app.logout') }}
                                </button>
                            </form>
                        </li>
                        @else
                        <li>
                            <a href="{{ route('applicant.register') }}"
                                class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                                {{ __('app.register') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('applicant.login') }}"
                                class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                {{ __('app.login') }}
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </nav>
        </div>
    </header>

    {{-- Flash messages --}}
    <div class="max-w-6xl mx-auto px-4 pt-4">
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

        {{-- Email verification notice --}}
        @includeIf('applicant.partials.email-unverified')
    </div>

    {{-- Page content --}}
    <main class="max-w-7xl mx-auto px-4 py-8">

        {{ $slot }}

    </main>

    @unless($hideFooter)
    {{-- Footer --}}
    <footer class="mt-10 border-t border-slate-200 bg-white">
        <div class="max-w-7xl mx-auto px-4 py-5  sm:text-sm text-slate-500 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <div>
                Ac {{ date('Y') }} <span class="font-semibold text-blue-700">{{ $brandName }}</span>.
                <span class="text-slate-500">{{ $footerText }}</span>
            </div>
            <div class="flex items-center gap-2 text-[11px] uppercase tracking-wide text-slate-400">
                <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>
                <span>{{ __('app.court_portal') }}</span>
            </div>
        </div>
    </footer>
    @endunless
</body>

</html>

