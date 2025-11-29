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
                    <p class="text-[11px] uppercase tracking-wide text-white/70">{{ __('respondent.portal') }}</p>
                    <h1 class="text-lg font-semibold">{{ $shortName }}</h1>
                </div>
            </a>

            <nav x-data="{ open:false }" class="relative">
                <ul class="hidden sm:flex items-center gap-3 text-sm">
                    @auth('respondent')
                    <li>
                        <a href="{{ route('respondent.cases.my') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-white/30 text-white/90 hover:bg-white/10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M3 6h6v6H3zm8 0h6v6h-6zm-8 8h6v6H3zm8 0h6v6h-6z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
                            </svg>
                            {{ __('respondent.my_cases') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('respondent.responses.index') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-white/30 text-white/90 hover:bg-white/10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M7 8h10M7 12h10M7 16h6" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
                                <path d="M5 20h14" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
                            </svg>
                            {{ __('respondent.my_responses') }}
                        </a>
                    </li>
                    @endauth
                    <li>
                        <a href="{{ route('respondent.case.search') }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-white/30 text-white/90 hover:bg-white/10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M11 5a6 6 0 1 0 0 12 6 6 0 0 0 0-12z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
                                <path d="M16 16l5 5" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
                            </svg>
                            {{ __('respondent.find_case') }}
                        </a>
                    </li>
                    @auth('respondent')
                    <li>
                        <a href="{{ route('respondent.profile.edit') }}"
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
                            <a href="{{ route('respondent.cases.my') }}" class="block px-4 py-2 hover:bg-slate-50 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path d="M3 6h6v6H3zm8 0h6v6h-6zm-8 8h6v6H3zm8 0h6v6h-6z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
                                </svg>
                                {{ __('respondent.my_cases') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('respondent.responses.index') }}" class="block px-4 py-2 hover:bg-slate-50 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path d="M7 8h10M7 12h10M7 16h6" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
                                    <path d="M4 20h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
                                </svg>
                                {{ __('respondent.my_responses') }}
                            </a>
                        </li>
                        @endauth
                        <li>
                            <a href="{{ route('respondent.case.search') }}" class="block px-4 py-2 hover:bg-slate-50 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path d="M11 5a6 6 0 1 0 0 12 6 6 0 0 0 0-12z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
                                    <path d="M16 16l5 5" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
                                </svg>
                                {{ __('respondent.find_case') }}
                            </a>
                        </li>
                        @auth('respondent')
                        <li>
                        <a href="{{ route('respondent.profile.edit') }}" class="block px-4 py-2 hover:bg-slate-50 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10z" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
                                    <path d="M4 20h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
                                </svg>
                                {{ __('respondent.profile') }}
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('respondent.logout') }}" class="px-4 py-2">
                                @csrf
                                <button class="w-full text-left hover:text-blue-700 inline-flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path d="M17 16l4-4m0 0l-4-4m4 4H7" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
                                        <path d="M7 8v-2a2 2 0 012-2h6" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
                                    </svg>
                                    {{ __('Logout') }}
                                </button>
                            </form>
                        </li>
                        @else
                        <li>
                            <a href="{{ route('respondent.login') }}" class="block px-4 py-2 hover:bg-slate-50 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path d="M15 12h4" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
                                    <path d="M9 5v-2a2 2 0 012-2h4" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
                                    <path d="M7 20h10" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
                                </svg>
                                {{ __('Login') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('respondent.register') }}" class="block px-4 py-2 hover:bg-slate-50 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path d="M12 7v6m0-6v-2" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
                                    <path d="M5 12h14" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"/>
                                </svg>
                                {{ __('Register') }}
                            </a>
                        </li>
                        @endauth
                        <li class="px-4 py-2 text-xs uppercase tracking-wide text-slate-400">Language</li>
                        <li>
                            <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}" class="block px-4 py-2 hover:bg-slate-50">
                                {{ __('app.English') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}" class="block px-4 py-2 hover:bg-slate-50">
                                {{ __('app.Amharic') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="flex items-center gap-2">
                <div x-data="{ open:false }" class="relative">
                    <button @click="open=!open"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-white/30 text-white/90 hover:bg-white/10 text-sm">
                        <span class="fi fi-{{ app()->getLocale() === 'am' ? 'et' : 'us' }}"></span>
                        {{ strtoupper(app()->getLocale()) }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-cloak x-show="open" @click.outside="open=false"
                        class="absolute right-0 mt-2 w-32 rounded-md border border-slate-200 bg-white text-slate-700 shadow-lg z-50">
                        <div class="p-2 space-y-1">
                            <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}"
                                class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-slate-50 rounded {{ app()->getLocale() == 'en' ? 'bg-blue-50 text-blue-700' : '' }}">
                                <span class="fi fi-us"></span>
                                {{ __('app.English') }}
                            </a>
                            <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}"
                                class="flex items-center gap-2 px-3 py-2 text-sm hover:bg-slate-50 rounded {{ app()->getLocale() == 'am' ? 'bg-blue-50 text-blue-700' : '' }}">
                                <span class="fi fi-et"></span>
                                {{ __('app.Amharic') }}
                            </a>
                        </div>
                    </div>
                </div>
                @auth('respondent')
                <div x-data="{ open:false }" class="relative">
                    <button @click="open=!open"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-white/30 text-white/90 hover:bg-white/10 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M5.121 18.364A9 9 0 1118.364 5.12 9 9 0 015.121 18.364z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>{{ auth('respondent')->user()?->first_name ?? __('Respondent') }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-cloak x-show="open" @click.outside="open=false"
                        class="absolute right-0 mt-2 w-40 rounded-md border border-slate-200 bg-white text-slate-700 shadow-lg z-50">
                        <a href="{{ route('respondent.profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-slate-50">
                            {{ __('respondent.profile') }}
                        </a>
                        <form method="POST" action="{{ route('respondent.logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50">
                                {{ __('Logout') }}
                            </button>
                        </form>
                    </div>
                </div>
                @endauth
            </div>
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
