<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Court MS') }} | {{ __('home.landing.public_portal') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    $stats = [
        ['label' => __('home.landing.total_cases'), 'value' => number_format($totalCases ?? 0), 'accent' => 'from-blue-900 to-blue-700'],
        ['label' => __('home.landing.open_cases'), 'value' => number_format($openCases ?? 0), 'accent' => 'from-orange-600 to-orange-500'],
        ['label' => __('home.landing.pending_review'), 'value' => number_format($pendingCases ?? 0), 'accent' => 'from-blue-800 to-orange-600'],
        ['label' => __('home.landing.upcoming_hearings'), 'value' => number_format($upcomingHearings ?? 0), 'accent' => 'from-blue-950 to-blue-800'],
    ];

    $statusStyles = [
        'pending' => 'bg-orange-100 text-orange-800 ring-1 ring-inset ring-orange-200',
        'closed' => 'bg-blue-100 text-blue-800 ring-1 ring-inset ring-blue-200',
        'dismissed' => 'bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200',
        'default' => 'bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200',
    ];
@endphp

<body class="min-h-screen bg-slate-950 text-slate-100">
    <div class="relative isolate overflow-hidden">
        <div class="absolute inset-x-0 top-0 -z-10 h-[42rem] bg-[radial-gradient(circle_at_top,_rgba(30,64,175,0.28),_transparent_45%),radial-gradient(circle_at_right,_rgba(234,88,12,0.14),_transparent_35%)]"></div>

        <header class="sticky top-0 z-50 border-b border-blue-900/60 bg-blue-950/80 backdrop-blur-xl">
            <div class="mx-auto flex max-w-[96rem] items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('landing.home') }}" class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-800/50 ring-1 ring-blue-600/60">
                        <svg class="h-6 w-6 text-orange-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m-7-4h14M6.5 7.5h11M9 3.75h6m-8.5 3L4 19.25A1.5 1.5 0 0 0 5.46 21h13.08A1.5 1.5 0 0 0 20 19.25L17.5 6.75" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.28em] text-blue-200">Court MS</p>
                        <h1 class="text-base font-semibold text-white sm:text-lg">{{ __('home.landing.admin_court_public_portal') }}</h1>
                    </div>
                </a>

                <nav class="hidden items-center gap-3 sm:flex">
                    <div class="inline-flex items-center gap-2 rounded-full border border-blue-700/60 bg-blue-900/40 p-1">
                        <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}"
                            class="rounded-full px-3 py-1 text-xs font-semibold transition {{ app()->getLocale() === 'en' ? 'bg-orange-500 text-white' : 'text-blue-100 hover:bg-blue-800/60' }}">
                            EN
                        </a>
                        <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}"
                            class="rounded-full px-3 py-1 text-xs font-semibold transition {{ app()->getLocale() === 'am' ? 'bg-orange-500 text-white' : 'text-blue-100 hover:bg-blue-800/60' }}">
                            AM
                        </a>
                    </div>
                    <a href="{{ route('public.terms') }}" class="rounded-full px-4 py-2 text-sm font-medium text-blue-100 transition hover:bg-blue-800/40 hover:text-white">{{ __('app.Terms') }}</a>
                    <a href="{{ route('public.signage') }}" class="rounded-full px-4 py-2 text-sm font-medium text-blue-100 transition hover:bg-blue-800/40 hover:text-white">{{ __('home.landing.public_signage') }}</a>
                    <a href="{{ route('applicant.login') }}" class="inline-flex items-center rounded-full bg-orange-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-orange-900/30 transition hover:bg-orange-400">{{ __('home.landing.portal_login') }}</a>
                </nav>

                <div class="sm:hidden inline-flex items-center gap-2 rounded-full border border-blue-700/60 bg-blue-900/40 p-1">
                    <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}"
                        class="rounded-full px-3 py-1 text-xs font-semibold transition {{ app()->getLocale() === 'en' ? 'bg-orange-500 text-white' : 'text-blue-100 hover:bg-blue-800/60' }}">
                        EN
                    </a>
                    <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}"
                        class="rounded-full px-3 py-1 text-xs font-semibold transition {{ app()->getLocale() === 'am' ? 'bg-orange-500 text-white' : 'text-blue-100 hover:bg-blue-800/60' }}">
                        AM
                    </a>
                </div>
            </div>
        </header>

        <main>
            <section class="mx-auto grid max-w-[96rem] gap-12 px-4 py-16 sm:px-6 lg:grid-cols-[1.2fr_0.8fr] lg:px-8 lg:py-24">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full border border-blue-400/30 bg-blue-700/20 px-4 py-2 text-sm text-blue-100">
                        <span class="inline-block h-2 w-2 rounded-full bg-orange-400"></span>
                        {{ __('home.landing.live_case_overview') }}
                    </div>

                    <h2 class="mt-6 max-w-3xl text-4xl font-semibold tracking-tight text-white sm:text-5xl lg:text-6xl">
                        {{ __('home.landing.hero_title') }}
                    </h2>

                    <p class="mt-6 max-w-2xl text-base leading-8 text-slate-300 sm:text-lg">
                        {{ __('home.landing.hero_description') }}
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <a href="{{ route('applicant.register') }}" class="inline-flex items-center justify-center rounded-2xl bg-blue-700 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-900/40 transition hover:bg-blue-600">
                            {{ __('home.landing.create_applicant_account') }}
                        </a>
                        <a href="{{ route('applicant.login', ['login_as' => 'respondent']) }}" class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-orange-900/30 transition hover:bg-orange-400">
                            {{ __('home.landing.respondent_access') }}
                        </a>
                    </div>

                    <dl class="mt-10 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($stats as $stat)
                            <div class="rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                                <div class="inline-flex rounded-2xl bg-gradient-to-br {{ $stat['accent'] }} px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-white">
                                    {{ $stat['label'] }}
                                </div>
                                <dd class="mt-4 text-3xl font-semibold text-white">{{ $stat['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                <div class="rounded-[2rem] border border-blue-900/60 bg-blue-950/55 p-6 shadow-2xl shadow-blue-950/30 sm:p-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium uppercase tracking-[0.25em] text-orange-200">{{ __('home.landing.recent_filings') }}</p>
                            <h3 class="mt-2 text-2xl font-semibold text-white">{{ __('home.landing.latest_registered_cases') }}</h3>
                        </div>
                        <div class="rounded-2xl bg-orange-500/20 px-3 py-2 text-xs font-semibold text-orange-100 ring-1 ring-inset ring-orange-300/30">
                            {{ __('home.landing.updated_on', ['date' => now()->format('M j, Y')]) }}
                        </div>
                    </div>

                    <div class="mt-6 space-y-4">
                        @forelse ($recentCases as $case)
                            @php
                                $status = strtolower((string) ($case->status ?? ''));
                                $statusClass = $statusStyles[$status] ?? $statusStyles['default'];
                            @endphp
                            <article class="rounded-3xl border border-blue-800/50 bg-blue-950/60 p-5 transition hover:border-orange-400/40">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-[0.25em] text-blue-200">{{ $case->case_number }}</p>
                                        <h4 class="mt-2 text-lg font-semibold text-white">{{ $case->title }}</h4>
                                    </div>
                                    <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold capitalize {{ $statusClass }}">
                                        {{ $case->status ?? __('home.landing.recorded') }}
                                    </span>
                                </div>
                                <p class="mt-4 text-sm text-slate-300">{{ __('home.landing.filed_on', ['date' => optional($case->created_at)->format('M j, Y')]) }}</p>
                            </article>
                        @empty
                            <div class="rounded-3xl border border-dashed border-blue-700/40 bg-blue-950/40 p-8 text-center text-slate-300">
                                {{ __('home.landing.no_recent_cases') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-blue-900/60 bg-blue-950/80">
            <div class="mx-auto flex max-w-[96rem] flex-col gap-6 px-4 py-8 text-sm text-blue-100 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                <div>
                    <p class="font-semibold text-white">{{ __('home.landing.admin_court_public_portal') }}</p>
                    <p class="mt-1 text-blue-200">{{ __('home.landing.footer_description') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('landing.home') }}" class="transition hover:text-orange-200">{{ __('app.home') }}</a>
                    <span class="text-blue-700">•</span>
                    <a href="{{ route('applicant.login') }}" class="transition hover:text-orange-200">{{ __('app.login') }}</a>
                    <span class="text-blue-700">•</span>
                    <a href="{{ route('applicant.register') }}" class="transition hover:text-orange-200">{{ __('app.register') }}</a>
                </div>
            </div>
        </footer>
    </div>
</body>

</html>
