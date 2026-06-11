<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="{{ __('home.meta.description') }}" />
    <meta name="keywords" content="{{ __('home.meta.keywords') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $publicLayout['brandName'] ?? config('app.name', 'Court MS') }} – {{ __('home.meta.title') }}</title>
    <script>
        (function(){
            var t = localStorage.getItem('theme') || 'dark';
            var a = localStorage.getItem('accent') || 'blue';
            document.documentElement.setAttribute('data-theme', t);
            document.documentElement.setAttribute('data-accent', a);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        html { scroll-behavior: smooth; }

        /* ── Accent color palette (RGB triplets) ── */
        [data-accent="blue"]    { --ac: 59 130 246; --ac-d: 37 99 235;  --ac-l: 147 197 253; }
        [data-accent="orange"]  { --ac: 249 115 22; --ac-d: 234 88 12;  --ac-l: 253 186 116; }
        [data-accent="emerald"] { --ac: 16 185 129; --ac-d: 5 150 105;  --ac-l: 110 231 183; }
        [data-accent="violet"]  { --ac: 139 92 246; --ac-d: 124 58 237; --ac-l: 196 181 253; }
        [data-accent="rose"]    { --ac: 244 63 94;  --ac-d: 225 29 72;  --ac-l: 253 164 175; }

        /* Accent utility classes */
        .ac-btn { background-color: rgb(var(--ac)) !important; color: #fff !important; }
        .ac-btn:hover { background-color: rgb(var(--ac-d)) !important; }
        .ac-text { color: rgb(var(--ac-l)) !important; }

        /* ── Light mode ── */
        [data-theme="light"] body { background-color: #f8fafc; color: #0f172a; }

        /* Header */
        [data-theme="light"] header {
            background-color: rgba(255,255,255,0.93) !important;
            border-bottom-color: #e2e8f0 !important;
        }
        [data-theme="light"] header .text-blue-100 { color: #475569 !important; }
        [data-theme="light"] header .text-blue-200 { color: #64748b !important; }
        [data-theme="light"] header .text-blue-300 { color: #3b82f6 !important; }
        [data-theme="light"] header .text-white    { color: #1e293b !important; }
        [data-theme="light"] header .bg-blue-900\/30  { background-color: #f1f5f9 !important; }
        [data-theme="light"] header .bg-blue-800\/50  { background-color: #e2e8f0 !important; }
        [data-theme="light"] header .border-blue-700\/50 { border-color: #e2e8f0 !important; }
        [data-theme="light"] header .hover\:bg-blue-800\/40:hover { background-color: #f1f5f9 !important; }
        [data-theme="light"] header .hover\:bg-blue-800\/50:hover { background-color: #e2e8f0 !important; }
        [data-theme="light"] header .hover\:bg-blue-800\/60:hover { background-color: #dbeafe !important; }

        /* Mobile menu */
        [data-theme="light"] header .bg-slate-950\/95 { background-color: rgba(255,255,255,0.97) !important; }
        [data-theme="light"] .border-blue-900\/60 { border-color: #e2e8f0 !important; }

        /* Section alt backgrounds */
        [data-theme="light"] .bg-blue-950\/30 { background-color: #f1f5f9 !important; }

        /* Glass cards in main */
        [data-theme="light"] main .bg-white\/4  { background-color: #ffffff !important; }
        [data-theme="light"] main .border-white\/8 { border-color: #e2e8f0 !important; }
        [data-theme="light"] main .bg-white\/5  { background-color: #f8fafc !important; }
        [data-theme="light"] main .bg-white\/6  { background-color: #f1f5f9 !important; }
        [data-theme="light"] main .bg-white\/8  { background-color: #f1f5f9 !important; }
        [data-theme="light"] main .hover\:bg-white\/6:hover  { background-color: #f8fafc !important; }
        [data-theme="light"] main .hover\:bg-white\/10:hover { background-color: #e2e8f0 !important; }
        [data-theme="light"] main .hover\:border-white\/15:hover { border-color: #cbd5e1 !important; }
        [data-theme="light"] main .border-white\/15 { border-color: #e2e8f0 !important; }

        /* Service icon bg */
        [data-theme="light"] main .bg-slate-800 { background-color: #e2e8f0 !important; }

        /* Case cards */
        [data-theme="light"] main .bg-blue-950\/50 { background-color: #ffffff !important; }
        [data-theme="light"] main .border-blue-900\/50 { border-color: #e2e8f0 !important; }
        [data-theme="light"] main .hover\:bg-blue-950\/70:hover { background-color: #f8fafc !important; }
        [data-theme="light"] main .hover\:border-blue-700\/60:hover { border-color: #cbd5e1 !important; }
        [data-theme="light"] main .border-white\/5 { border-color: #e2e8f0 !important; }
        [data-theme="light"] main .border-dashed { border-color: #bfdbfe !important; }
        [data-theme="light"] main .bg-blue-950\/30 { background-color: #eff6ff !important; }

        /* Portal / action buttons */
        [data-theme="light"] main .bg-blue-900\/30 { background-color: #f1f5f9 !important; }
        [data-theme="light"] main .border-blue-700\/50 { border-color: #e2e8f0 !important; }
        [data-theme="light"] main .hover\:bg-blue-800\/50:hover { background-color: #e2e8f0 !important; }

        /* Text in non-hero sections */
        [data-theme="light"] main section:not(#home) .text-white  { color: #0f172a !important; }
        [data-theme="light"] main section:not(#home) .text-slate-200 { color: #334155 !important; }
        [data-theme="light"] main section:not(#home) .text-slate-400 { color: #475569 !important; }
        [data-theme="light"] main section:not(#home) .text-slate-500 { color: #64748b !important; }
        [data-theme="light"] main section:not(#home) .text-slate-600 { color: #94a3b8 !important; }
        [data-theme="light"] main section:not(#home) .text-blue-100 { color: #334155 !important; }
        [data-theme="light"] main section:not(#home) .text-blue-300 { color: #3b82f6 !important; }
        [data-theme="light"] main section:not(#home) .text-blue-400 { color: #2563eb !important; }
        [data-theme="light"] main section:not(#home) .text-orange-300 { color: #ea580c !important; }
        [data-theme="light"] main section:not(#home) .text-orange-400 { color: rgb(var(--ac-d, 234 88 12)) !important; }

        /* Section badges */
        [data-theme="light"] main section:not(#home) .bg-orange-500\/10 { background-color: #fff7ed !important; }
        [data-theme="light"] main section:not(#home) .border-orange-400\/30 { border-color: #fed7aa !important; }
        [data-theme="light"] main section:not(#home) .bg-blue-700\/15 { background-color: #dbeafe !important; }
        [data-theme="light"] main section:not(#home) .border-blue-400\/30 { border-color: #93c5fd !important; }
        [data-theme="light"] main section:not(#home) .bg-violet-700\/15 { background-color: #ede9fe !important; }
        [data-theme="light"] main section:not(#home) .border-violet-400\/30 { border-color: #c4b5fd !important; }
        [data-theme="light"] main section:not(#home) .text-violet-300 { color: #7c3aed !important; }

        /* Timeline step connector */
        [data-theme="light"] main .ring-slate-950 { --tw-ring-color: #f1f5f9 !important; }
        [data-theme="light"] main .from-blue-800\/0 { --tw-gradient-from: transparent !important; }
        [data-theme="light"] main .via-blue-700\/60 { --tw-gradient-stops: var(--tw-gradient-from), #bfdbfe, var(--tw-gradient-to) !important; }

        /* Step meta/duration badges */
        [data-theme="light"] main .bg-blue-900\/50 { background-color: #dbeafe !important; }
        [data-theme="light"] main .bg-orange-900\/40 { background-color: #ffedd5 !important; }

        /* FAQ hover */
        [data-theme="light"] main .hover\:bg-white\/4:hover { background-color: #f8fafc !important; }

        /* Footer stays dark */
        [data-theme="light"] footer { background-color: #0f172a; }
        [data-theme="light"] footer .border-blue-900\/50 { border-color: #1e3a5f !important; }
    </style>
</head>

@php
    $layout    = is_array($publicLayout ?? null) ? $publicLayout : (array)($publicLayout ?? []);
    $brandName = $layout['brandName'] ?? config('app.name', 'Court MS');
    $shortName = $layout['shortName'] ?? $brandName;
    $logoPath  = $layout['logoPath'] ?? null;

    // DB-managed content (falls back to translations when tables are empty)
    $dbSlides    = $dbSlides    ?? collect();
    $dbFaqs      = $dbFaqs      ?? collect();
    $dbSections  = $dbSections  ?? [];
    $dbServices  = $dbServices  ?? collect();
    $dbTimeline  = $dbTimeline  ?? collect();
    $dbResources = $dbResources ?? collect();
    $dbFooter    = $dbFooter    ?? null;

    $sec = function (string $section, string $key, string $fallback = '') use ($dbSections): string {
        return !empty($dbSections[$section][$key]) ? $dbSections[$section][$key] : $fallback;
    };
    $secVisible = function (string $section) use ($dbSections): bool {
        if (!isset($dbSections[$section])) return true;
        return (bool) ($dbSections[$section]['visible'] ?? true);
    };

    $metrics = [
        [
            'label'       => __('home.metrics.cards.total_cases.label'),
            'description' => __('home.metrics.cards.total_cases.description'),
            'value'       => number_format($totalCases ?? 0),
            'icon'        => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'accent'      => 'from-blue-700 to-blue-500',
            'ring'        => 'ring-blue-500/30',
        ],
        [
            'label'       => __('home.metrics.cards.resolved_cases.label'),
            'description' => __('home.metrics.cards.resolved_cases.description'),
            'value'       => number_format($resolvedCases ?? 0),
            'icon'        => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'accent'      => 'from-emerald-700 to-emerald-500',
            'ring'        => 'ring-emerald-500/30',
        ],
        [
            'label'       => __('home.metrics.cards.pending_cases.label'),
            'description' => __('home.metrics.cards.pending_cases.description'),
            'value'       => number_format($pendingCases ?? 0),
            'icon'        => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            'accent'      => 'from-orange-600 to-orange-400',
            'ring'        => 'ring-orange-400/30',
        ],
        [
            'label'       => __('home.metrics.cards.active_caseload.label'),
            'description' => __('home.metrics.cards.active_caseload.description'),
            'value'       => number_format($openCases ?? 0),
            'icon'        => 'M13 10V3L4 14h7v7l9-11h-7z',
            'accent'      => 'from-blue-800 to-blue-600',
            'ring'        => 'ring-blue-600/30',
        ],
        [
            'label'       => __('home.metrics.cards.hearings_this_week.label'),
            'description' => __('home.metrics.cards.hearings_this_week.description'),
            'value'       => number_format($hearingsThisWeek ?? 0),
            'icon'        => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
            'accent'      => 'from-violet-700 to-violet-500',
            'ring'        => 'ring-violet-500/30',
        ],
        [
            'label'       => __('home.metrics.cards.avg_resolution_time.label'),
            'description' => __('home.metrics.cards.avg_resolution_time.description'),
            'value'       => number_format($upcomingHearings ?? 0),
            'icon'        => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
            'accent'      => 'from-slate-600 to-slate-400',
            'ring'        => 'ring-slate-400/30',
        ],
    ];

    $timelineSteps = [
        ['key' => 'case_initiation',    'num' => 1, 'color' => 'bg-blue-500'],
        ['key' => 'case_assignment',    'num' => 2, 'color' => 'bg-orange-500'],
        ['key' => 'evidence_submission','num' => 3, 'color' => 'bg-blue-600'],
        ['key' => 'adjudication',       'num' => 4, 'color' => 'bg-orange-600'],
        ['key' => 'implementation',     'num' => 5, 'color' => 'bg-emerald-600'],
    ];

    $serviceCards = [
        ['key' => 'digital_case_filing',     'icon' => 'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12', 'accent' => 'text-blue-400'],
        ['key' => 'hearing_management',      'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'accent' => 'text-orange-400'],
        ['key' => 'evidence_repository',     'icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'accent' => 'text-emerald-400'],
        ['key' => 'case_tracking_portal',    'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'accent' => 'text-violet-400'],
        ['key' => 'decision_database',       'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'accent' => 'text-amber-400'],
        ['key' => 'online_dispute_resolution','icon' => 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z', 'accent' => 'text-pink-400'],
    ];

    $statusStyles = [
        'pending'   => 'bg-orange-500/15 text-orange-300 ring-1 ring-inset ring-orange-400/30',
        'closed'    => 'bg-emerald-500/15 text-emerald-300 ring-1 ring-inset ring-emerald-400/30',
        'dismissed' => 'bg-slate-500/15 text-slate-300 ring-1 ring-inset ring-slate-400/30',
        'default'   => 'bg-blue-500/15 text-blue-300 ring-1 ring-inset ring-blue-400/30',
    ];

    $faqItems = $dbFaqs->isNotEmpty()
        ? $dbFaqs->map(fn($f) => ['question' => $f->question, 'answer' => $f->answer])->all()
        : (array) __('home.faq.questions');
@endphp

<body class="bg-slate-950 text-slate-100 antialiased" x-data="{
    mobileOpen: false,
    theme: localStorage.getItem('theme') || 'dark',
    accent: localStorage.getItem('accent') || 'blue',
    themePanel: false,
    init() {
        document.documentElement.setAttribute('data-theme', this.theme);
        document.documentElement.setAttribute('data-accent', this.accent);
        this.$watch('theme', v => {
            localStorage.setItem('theme', v);
            document.documentElement.setAttribute('data-theme', v);
        });
        this.$watch('accent', v => {
            localStorage.setItem('accent', v);
            document.documentElement.setAttribute('data-accent', v);
        });
    }
}">

    {{-- Skip link --}}
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 z-[100] bg-orange-500 text-white px-4 py-2 rounded-lg text-sm font-semibold">
        {{ __('home.accessibility.skip_to_content') }}
    </a>

    {{-- =========================================================
         HEADER
    ========================================================= --}}
    <header class="sticky top-0 z-50 border-b border-blue-900/60 bg-slate-950/80 backdrop-blur-xl">
        <div class="mx-auto flex max-w-[96rem] items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">

            {{-- Logo --}}
            <a href="{{ route('landing.home') }}" class="flex items-center gap-3 flex-shrink-0">
                @if($logoPath)
                    <img src="{{ asset('storage/'.$logoPath) }}" alt="{{ $brandName }}"
                         class="h-10 w-auto max-w-[2.5rem] object-contain rounded-xl">
                @else
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-800/50 ring-1 ring-blue-600/50">
                        <svg class="h-5 w-5 text-orange-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m-7-4h14M6.5 7.5h11M9 3.75h6m-8.5 3L4 19.25A1.5 1.5 0 005.46 21h13.08A1.5 1.5 0 0020 19.25L17.5 6.75"/>
                        </svg>
                    </div>
                @endif
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.3em] text-blue-300 hidden sm:block">{{ __('home.nav.agency_label') }}</p>
                    <p class="text-sm font-semibold text-white leading-none">{{ $brandName }}</p>
                </div>
            </a>

            {{-- Desktop nav --}}
            @php
            $navLinks = [
                ['#home',       __('home.nav.home'),       'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['#statistics', __('home.nav.statistics'), 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ['#process',    __('home.nav.process'),    'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                ['#services',   __('home.nav.services'),   'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                ['#cases',      __('home.nav.cases'),      'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z'],
                ['#resources',  __('home.nav.resources'),  'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
                ['#faq',        __('home.nav.faq'),        'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ];
            @endphp
            <nav class="hidden lg:flex items-center gap-0.5" aria-label="Main navigation">
                @foreach($navLinks as [$href, $label, $icon])
                <a href="{{ $href }}"
                   class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm text-blue-100 hover:bg-blue-800/40 hover:text-white transition">
                    <svg class="h-3.5 w-3.5 opacity-70 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                    </svg>
                    {{ $label }}
                </a>
                @endforeach
            </nav>

            {{-- Right controls --}}
            <div class="flex items-center gap-2">
                {{-- Language switcher --}}
                <div class="hidden sm:inline-flex items-center gap-1 rounded-full border border-blue-700/50 bg-blue-900/30 p-0.5">
                    <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}"
                       class="rounded-full px-3 py-1 text-xs font-semibold transition {{ app()->getLocale() === 'en' ? 'bg-orange-500 text-white' : 'text-blue-200 hover:bg-blue-800/60' }}">EN</a>
                    <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}"
                       class="rounded-full px-3 py-1 text-xs font-semibold transition {{ app()->getLocale() === 'am' ? 'bg-orange-500 text-white' : 'text-blue-200 hover:bg-blue-800/60' }}">AM</a>
                </div>

                {{-- Portal links --}}
                <a href="{{ route('applicant.login') }}"
                   class="hidden sm:inline-flex items-center gap-1.5 rounded-full border border-blue-700/50 bg-blue-900/30 px-3 py-1.5 text-xs font-semibold text-blue-100 hover:bg-blue-800/50 hover:text-white transition">
                    {{ __('home.nav.applicant_portal') }}
                </a>
                <a href="{{ route('applicant.login', ['login_as' => 'respondent']) }}"
                   class="ac-btn hidden md:inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold transition shadow-lg shadow-orange-900/30">
                    {{ __('home.nav.respondent_portal') }}
                </a>

                {{-- Mobile hamburger --}}
                <button @click="mobileOpen = !mobileOpen"
                        class="lg:hidden flex h-9 w-9 items-center justify-center rounded-lg border border-blue-700/50 bg-blue-900/30 text-blue-200 hover:text-white transition"
                        :aria-label="mobileOpen ? 'Close menu' : '{{ __('home.accessibility.menu_toggle') }}'">
                    <svg x-show="!mobileOpen" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileOpen" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div x-show="mobileOpen" x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="lg:hidden border-t border-blue-900/60 bg-slate-950/95 backdrop-blur-xl px-4 pb-4 pt-3 space-y-1">
            @foreach($navLinks as [$href, $label, $icon])
            <a href="{{ $href }}" @click="mobileOpen = false"
               class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm text-blue-100 hover:bg-blue-800/40 hover:text-white transition">
                <svg class="h-4 w-4 opacity-60 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                </svg>
                {{ $label }}
            </a>
            @endforeach
            <div class="pt-2 border-t border-blue-900/60 grid grid-cols-2 gap-2">
                <div class="inline-flex items-center justify-center gap-1 rounded-full border border-blue-700/50 bg-blue-900/30 p-0.5 col-span-2 w-fit mx-auto">
                    <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}"
                       class="rounded-full px-3 py-1 text-xs font-semibold transition {{ app()->getLocale() === 'en' ? 'bg-orange-500 text-white' : 'text-blue-200' }}">EN</a>
                    <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}"
                       class="rounded-full px-3 py-1 text-xs font-semibold transition {{ app()->getLocale() === 'am' ? 'bg-orange-500 text-white' : 'text-blue-200' }}">AM</a>
                </div>
                <a href="{{ route('applicant.login') }}"
                   class="col-span-1 text-center rounded-lg border border-blue-700/50 bg-blue-900/30 px-3 py-2 text-xs font-semibold text-blue-100 hover:bg-blue-800/50 transition">
                    {{ __('home.nav.applicant_portal') }}
                </a>
                <a href="{{ route('applicant.login', ['login_as' => 'respondent']) }}"
                   class="ac-btn col-span-1 text-center rounded-lg px-3 py-2 text-xs font-semibold transition">
                    {{ __('home.nav.respondent_portal') }}
                </a>
            </div>
        </div>
    </header>

    <main id="main-content">

        {{-- =====================================================
             HERO SLIDESHOW
        ===================================================== --}}
        @php
        $bgMap = [
            'blue'    => [
                'bg'          => 'from-blue-950 via-blue-900/90 to-slate-950',
                'glow'        => 'bg-[radial-gradient(ellipse_at_top_left,_rgba(59,130,246,0.35),_transparent_55%),radial-gradient(ellipse_at_bottom_right,_rgba(234,88,12,0.18),_transparent_50%)]',
                'badge_color' => 'border-blue-500/30 bg-blue-700/20 text-blue-200',
                'dot_color'   => 'bg-orange-400',
                'accent'      => 'from-orange-400 to-orange-200',
            ],
            'orange'  => [
                'bg'          => 'from-slate-950 via-indigo-950/80 to-orange-950/40',
                'glow'        => 'bg-[radial-gradient(ellipse_at_top_right,_rgba(234,88,12,0.28),_transparent_55%),radial-gradient(ellipse_at_bottom_left,_rgba(99,102,241,0.2),_transparent_50%)]',
                'badge_color' => 'border-orange-500/30 bg-orange-700/20 text-orange-200',
                'dot_color'   => 'bg-indigo-400',
                'accent'      => 'from-orange-300 to-amber-200',
            ],
            'emerald' => [
                'bg'          => 'from-slate-950 via-blue-950 to-emerald-950/50',
                'glow'        => 'bg-[radial-gradient(ellipse_at_center,_rgba(16,185,129,0.18),_transparent_60%),radial-gradient(ellipse_at_top,_rgba(30,64,175,0.3),_transparent_50%)]',
                'badge_color' => 'border-emerald-500/30 bg-emerald-700/20 text-emerald-200',
                'dot_color'   => 'bg-emerald-400',
                'accent'      => 'from-emerald-400 to-teal-200',
            ],
        ];

        if ($dbSlides->isNotEmpty()) {
            $slides = $dbSlides->map(fn($s) => array_merge($bgMap[$s->bg_style] ?? $bgMap['blue'], [
                'badge'    => $s->badge ?? '',
                'title'    => $s->title,
                'desc'     => $s->description ?? '',
                'cta'      => $s->primary_label,
                'cta_href' => $s->primary_href ?: '#',
                'sec'      => $s->secondary_label ?? '',
                'sec_href' => $s->secondary_href ?: '#',
                'bg_image' => $s->bg_image ? asset('storage/' . $s->bg_image) : null,
            ]))->values()->all();
        } else {
            $slides = [
                array_merge($bgMap['blue'], [
                    'badge'    => __('home.hero.slides.digital.badge'),
                    'title'    => __('home.hero.slides.digital.title'),
                    'desc'     => __('home.hero.slides.digital.description'),
                    'cta'      => __('home.hero.slides.digital.cta_label'),
                    'cta_href' => route('applicant.register'),
                    'sec'      => __('home.hero.slides.digital.secondary_label'),
                    'sec_href' => route('applicant.login'),
                ]),
                array_merge($bgMap['orange'], [
                    'badge'    => __('home.hero.slides.hearing.badge'),
                    'title'    => __('home.hero.slides.hearing.title'),
                    'desc'     => __('home.hero.slides.hearing.description'),
                    'cta'      => __('home.hero.slides.hearing.cta_label'),
                    'cta_href' => route('applicant.login'),
                    'sec'      => __('home.hero.slides.hearing.secondary_label'),
                    'sec_href' => route('public.terms'),
                ]),
                array_merge($bgMap['emerald'], [
                    'badge'    => __('home.hero.slides.analytics.badge'),
                    'title'    => __('home.hero.slides.analytics.title'),
                    'desc'     => __('home.hero.slides.analytics.description'),
                    'cta'      => __('home.hero.slides.analytics.cta_label'),
                    'cta_href' => '#statistics',
                    'sec'      => __('home.hero.slides.analytics.secondary_label'),
                    'sec_href' => route('applicant.login'),
                ]),
            ];
        }
        @endphp

        <section id="home"
                 class="relative isolate overflow-hidden h-[calc(100vh-3.75rem)] min-h-[600px]"
                 x-data="{
                     current: 0,
                     total: {{ count($slides) }},
                     paused: false,
                     timer: null,
                     init() {
                         this.timer = setInterval(() => {
                             if (!this.paused) this.next();
                         }, 6000);
                     },
                     destroy() { clearInterval(this.timer); },
                     next() { this.current = (this.current + 1) % this.total; },
                     prev() { this.current = (this.current - 1 + this.total) % this.total; },
                     goTo(n) {
                         this.current = n;
                         clearInterval(this.timer);
                         this.timer = setInterval(() => { if (!this.paused) this.next(); }, 6000);
                     }
                 }"
                 @mouseenter="paused = true"
                 @mouseleave="paused = false">

            {{-- Base dark fallback --}}
            <div class="absolute inset-0 -z-30 bg-slate-950"></div>

            {{-- Slide backgrounds --}}
            @foreach($slides as $i => $slide)
            <div x-show="current === {{ $i }}"
                 x-cloak
                 x-transition:enter="transition-opacity ease-in-out duration-1000"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-in-out duration-1000"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 -z-10">
                @if(!empty($slide['bg_image']))
                    {{-- Photo background --}}
                    <img src="{{ $slide['bg_image'] }}" alt=""
                         class="absolute inset-0 h-full w-full object-cover">
                    {{-- Dark overlay so text stays readable --}}
                    <div class="absolute inset-0 bg-slate-950/60"></div>
                    {{-- Subtle colour tint from the chosen style --}}
                    <div class="absolute inset-0 bg-gradient-to-br {{ $slide['bg'] }} opacity-40"></div>
                @else
                    <div class="absolute inset-0 bg-gradient-to-br {{ $slide['bg'] }}"></div>
                    <div class="absolute inset-0 {{ $slide['glow'] }}"></div>
                @endif
            </div>
            @endforeach

            {{-- First slide bg visible without JS --}}
            <div class="absolute inset-0 -z-20">
                @if(!empty($slides[0]['bg_image']))
                    <img src="{{ $slides[0]['bg_image'] }}" alt=""
                         class="absolute inset-0 h-full w-full object-cover">
                    <div class="absolute inset-0 bg-slate-950/60"></div>
                    <div class="absolute inset-0 bg-gradient-to-br {{ $slides[0]['bg'] }} opacity-40"></div>
                @else
                    <div class="absolute inset-0 bg-gradient-to-br {{ $slides[0]['bg'] }}"></div>
                    <div class="absolute inset-0 {{ $slides[0]['glow'] }}"></div>
                @endif
            </div>

            {{-- Slide content layers --}}
            @foreach($slides as $i => $slide)
            <div x-show="current === {{ $i }}"
                 x-cloak
                 x-transition:enter="transition ease-in-out duration-700"
                 x-transition:enter-start="opacity-0 translate-y-6"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in-out duration-500"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-4"
                 class="absolute inset-0 flex items-center pb-28">
                <div class="mx-auto max-w-[96rem] w-full px-4 sm:px-6 lg:px-8">
                    <div class="max-w-3xl">

                        {{-- Badge --}}
                        <div class="inline-flex items-center gap-2 rounded-full border {{ $slide['badge_color'] }} px-4 py-1.5 text-sm font-medium mb-6">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $slide['dot_color'] }} opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 {{ $slide['dot_color'] }}"></span>
                            </span>
                            {{ $slide['badge'] }}
                        </div>

                        {{-- Title --}}
                        <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl xl:text-7xl leading-tight">
                            <span class="text-transparent bg-clip-text bg-gradient-to-r {{ $slide['accent'] }}">
                                {{ $slide['title'] }}
                            </span>
                        </h1>

                        <p class="mt-6 text-base sm:text-lg leading-8 text-slate-300 max-w-2xl">
                            {{ $slide['desc'] }}
                        </p>

                        {{-- CTA buttons --}}
                        <div class="mt-8 flex flex-col sm:flex-row gap-3">
                            <a href="{{ $slide['cta_href'] }}"
                               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-orange-500 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-orange-900/40 transition hover:bg-orange-400">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                {{ $slide['cta'] }}
                            </a>
                            <a href="{{ $slide['sec_href'] }}"
                               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/25 bg-white/10 px-6 py-3.5 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/20">
                                {{ $slide['sec'] }}
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>

                        {{-- Slide counter --}}
                        <p class="mt-8 text-xs font-semibold tracking-[0.25em] uppercase text-white/30">
                            {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }} / {{ str_pad(count($slides), 2, '0', STR_PAD_LEFT) }}
                        </p>
                    </div>
                </div>
            </div>
            @endforeach

            {{-- Prev / Next arrows --}}
            <button @click="prev()"
                    class="absolute left-4 top-1/2 -translate-y-10 z-20 flex h-11 w-11 items-center justify-center rounded-full border border-white/20 bg-black/30 text-white backdrop-blur-sm transition hover:bg-black/50 hover:border-white/40 focus:outline-none"
                    aria-label="Previous slide">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <button @click="next()"
                    class="absolute right-4 top-1/2 -translate-y-10 z-20 flex h-11 w-11 items-center justify-center rounded-full border border-white/20 bg-black/30 text-white backdrop-blur-sm transition hover:bg-black/50 hover:border-white/40 focus:outline-none"
                    aria-label="Next slide">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            {{-- Dot navigation --}}
            <div class="absolute bottom-[5.5rem] left-1/2 -translate-x-1/2 z-20 flex items-center gap-2">
                @foreach($slides as $i => $slide)
                <button @click="goTo({{ $i }})"
                        class="transition-all duration-300 rounded-full focus:outline-none"
                        :class="current === {{ $i }}
                            ? 'w-6 h-2 bg-orange-400'
                            : 'w-2 h-2 bg-white/30 hover:bg-white/60'"
                        aria-label="Go to slide {{ $i + 1 }}">
                </button>
                @endforeach
            </div>

            {{-- Progress bar --}}
            <div class="absolute bottom-[5.25rem] left-0 right-0 h-px bg-white/10 z-20">
                <div class="h-full bg-orange-500/60 origin-left"
                     style="animation: slideProgress 6s linear infinite;"
                     :style="paused ? 'animation-play-state: paused' : ''"></div>
            </div>

            {{-- Stats bar --}}
            <div class="absolute bottom-0 left-0 right-0 z-20 border-t border-white/10 bg-black/40 backdrop-blur-md">
                <div class="mx-auto max-w-[96rem] px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-white/10">
                        @foreach([
                            [$totalCases ?? 0,     __('home.hero.stats.cases')],
                            [$resolvedCases ?? 0,  __('home.hero.stats.resolution_rate')],
                            [$pendingCases ?? 0,   __('home.hero.stats.applicants')],
                            [$upcomingHearings??0, __('home.hero.stats.judges')],
                        ] as [$val, $label])
                        <div class="px-4 py-4 sm:px-6 text-center sm:text-left">
                            <p class="text-xl sm:text-2xl font-bold text-white">{{ number_format($val) }}</p>
                            <p class="mt-0.5 text-[11px] text-slate-400 leading-snug">{{ $label }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <style>
            @keyframes slideProgress {
                from { width: 0%; }
                to   { width: 100%; }
            }
        </style>

        {{-- =====================================================
             METRICS
        ===================================================== --}}
        @if($secVisible('metrics'))
        <section id="statistics" class="py-20 sm:py-24">
            <div class="mx-auto max-w-[96rem] px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <span class="inline-flex items-center gap-2 rounded-full border border-orange-400/30 bg-orange-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-orange-300 mb-4">
                        {{ __('home.metrics.section_badge') }}
                    </span>
                    <h2 class="text-3xl font-bold text-white sm:text-4xl">{{ $sec('metrics', 'title', __('home.metrics.section_title')) }}</h2>
                    <p class="mt-3 text-slate-400 text-base max-w-2xl mx-auto">{{ $sec('metrics', 'subtitle', __('home.metrics.section_description')) }}</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($metrics as $metric)
                    <div class="group relative rounded-3xl border border-white/8 bg-white/4 p-6 hover:border-white/15 hover:bg-white/6 transition backdrop-blur-sm">
                        <div class="flex items-start justify-between gap-3 mb-5">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br {{ $metric['accent'] }} ring-2 {{ $metric['ring'] }} shadow-lg">
                                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $metric['icon'] }}"/>
                                </svg>
                            </div>
                            <span class="rounded-full bg-white/8 px-2.5 py-0.5 text-xs font-medium text-slate-400">{{ \App\Support\EthiopianDate::smartFormat(now(), false, '', 'h:i A', 'M Y') }}</span>
                        </div>
                        <p class="text-3xl font-bold text-white">{{ $metric['value'] }}</p>
                        <p class="mt-1 text-sm font-semibold text-slate-200">{{ $metric['label'] }}</p>
                        <p class="mt-1 text-xs text-slate-500 leading-relaxed">{{ $metric['description'] }}</p>
                    </div>
                    @endforeach
                </div>

                <p class="mt-6 text-center text-xs text-slate-600">
                    {{ __('home.metrics.last_updated') }}: {{ \App\Support\EthiopianDate::smartFormat(now(), true, '', 'h:i A', 'd M Y') }}
                </p>
            </div>
        </section>
        @endif

        {{-- =====================================================
             PROCESS TIMELINE
        ===================================================== --}}
        @if($secVisible('process'))
        <section id="process" class="py-20 sm:py-24 bg-blue-950/30">
            <div class="mx-auto max-w-[96rem] px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-14">
                    <span class="inline-flex items-center gap-2 rounded-full border border-blue-400/30 bg-blue-700/15 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-blue-300 mb-4">
                        {{ __('home.timeline.section_badge') }}
                    </span>
                    <h2 class="text-3xl font-bold text-white sm:text-4xl">{{ $sec('process', 'title', __('home.timeline.section_title')) }}</h2>
                    <p class="mt-3 text-slate-400 text-base max-w-2xl mx-auto">{{ $sec('process', 'subtitle', __('home.timeline.section_description')) }}</p>
                </div>

                @php
                $colorBg = ['blue'=>'bg-blue-500','orange'=>'bg-orange-500','emerald'=>'bg-emerald-600','violet'=>'bg-violet-500','amber'=>'bg-amber-500'];
                @endphp
                <div class="relative">
                    {{-- connector line --}}
                    <div class="hidden lg:block absolute top-[2.75rem] left-[calc(10%+1.5rem)] right-[calc(10%+1.5rem)] h-px bg-gradient-to-r from-blue-800/0 via-blue-700/60 to-blue-800/0"></div>

                    @if($dbTimeline->isNotEmpty())
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-{{ min($dbTimeline->count(), 5) }}">
                        @foreach($dbTimeline as $i => $step)
                        <div class="relative flex flex-col items-center text-center group">
                            <div class="relative flex h-14 w-14 items-center justify-center rounded-2xl {{ $colorBg[$step->color] ?? 'bg-blue-500' }} text-white font-bold text-lg shadow-lg ring-4 ring-slate-950 z-10 mb-4 group-hover:scale-110 transition">
                                {{ $i + 1 }}
                            </div>
                            <div class="rounded-2xl border border-white/8 bg-white/4 p-4 w-full hover:border-white/15 hover:bg-white/6 transition">
                                <p class="text-sm font-semibold text-white leading-snug mb-1">{{ $step->title }}</p>
                                @if($step->description)
                                <p class="text-xs text-slate-400 leading-relaxed mb-3">{{ $step->description }}</p>
                                @endif
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    @if($step->meta)
                                    <span class="inline-flex items-center rounded-full bg-blue-900/50 px-2 py-0.5 text-[10px] font-medium text-blue-300">{{ $step->meta }}</span>
                                    @endif
                                    @if($step->duration)
                                    <span class="inline-flex items-center rounded-full bg-orange-900/40 px-2 py-0.5 text-[10px] font-medium text-orange-300">{{ $step->duration }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-5">
                        @foreach($timelineSteps as $step)
                        @php $key = $step['key']; @endphp
                        <div class="relative flex flex-col items-center text-center group">
                            <div class="relative flex h-14 w-14 items-center justify-center rounded-2xl {{ $step['color'] }} text-white font-bold text-lg shadow-lg ring-4 ring-slate-950 z-10 mb-4 group-hover:scale-110 transition">
                                {{ $step['num'] }}
                            </div>
                            <div class="rounded-2xl border border-white/8 bg-white/4 p-4 w-full hover:border-white/15 hover:bg-white/6 transition">
                                <p class="text-sm font-semibold text-white leading-snug mb-1">{{ __("home.timeline.steps.{$key}.title") }}</p>
                                <p class="text-xs text-slate-400 leading-relaxed mb-3">{{ __("home.timeline.steps.{$key}.description") }}</p>
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <span class="inline-flex items-center rounded-full bg-blue-900/50 px-2 py-0.5 text-[10px] font-medium text-blue-300">
                                        {{ __("home.timeline.steps.{$key}.meta") }}
                                    </span>
                                    <span class="inline-flex items-center rounded-full bg-orange-900/40 px-2 py-0.5 text-[10px] font-medium text-orange-300">
                                        {{ __("home.timeline.steps.{$key}.duration") }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </section>
        @endif

        {{-- =====================================================
             SERVICES
        ===================================================== --}}
        @if($secVisible('services'))
        <section id="services" class="py-20 sm:py-24">
            <div class="mx-auto max-w-[96rem] px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <span class="inline-flex items-center gap-2 rounded-full border border-orange-400/30 bg-orange-500/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-orange-300 mb-4">
                        {{ __('home.services.section_badge') }}
                    </span>
                    <h2 class="text-3xl font-bold text-white sm:text-4xl">{{ $sec('services', 'title', __('home.services.section_title')) }}</h2>
                    <p class="mt-3 text-slate-400 text-base max-w-2xl mx-auto">{{ $sec('services', 'subtitle', __('home.services.section_description')) }}</p>
                </div>

                @php
                $iconPaths = [
                    'document' => 'M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12',
                    'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                    'lock'     => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
                    'chart'    => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                    'database' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
                    'chat'     => 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z',
                ];
                $accentMap = [
                    'blue'    => 'text-blue-400',
                    'orange'  => 'text-orange-400',
                    'emerald' => 'text-emerald-400',
                    'violet'  => 'text-violet-400',
                    'amber'   => 'text-amber-400',
                    'pink'    => 'text-pink-400',
                ];
                $renderCards = $dbServices->isNotEmpty()
                    ? $dbServices->map(fn($s) => [
                        'title'       => $s->title,
                        'description' => $s->description ?? '',
                        'meta'        => $s->meta ?? '',
                        'features'    => $s->features ?? [],
                        'icon'        => $iconPaths[$s->icon_type] ?? $iconPaths['document'],
                        'accent'      => $accentMap[$s->accent]   ?? 'text-blue-400',
                    ])->all()
                    : array_map(fn($svc) => [
                        'title'       => __("home.services.cards.{$svc['key']}.title"),
                        'description' => __("home.services.cards.{$svc['key']}.description"),
                        'meta'        => __("home.services.cards.{$svc['key']}.meta"),
                        'features'    => (array) __("home.services.cards.{$svc['key']}.features"),
                        'icon'        => $svc['icon'],
                        'accent'      => $svc['accent'],
                    ], $serviceCards);
                @endphp
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($renderCards as $card)
                    <div class="group rounded-3xl border border-white/8 bg-white/4 p-6 hover:border-white/15 hover:bg-white/6 transition flex flex-col">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-800 ring-1 ring-white/10">
                                <svg class="h-5 w-5 {{ $card['accent'] }}" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/>
                                </svg>
                            </div>
                            @if($card['meta'])
                            <span class="rounded-full bg-white/6 px-2.5 py-0.5 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">{{ $card['meta'] }}</span>
                            @endif
                        </div>
                        <h3 class="text-base font-semibold text-white mb-2">{{ $card['title'] }}</h3>
                        <p class="text-sm text-slate-400 leading-relaxed flex-1 mb-4">{{ $card['description'] }}</p>
                        @if($card['features'])
                        <ul class="space-y-1.5">
                            @foreach($card['features'] as $feat)
                            <li class="flex items-center gap-2 text-xs text-slate-400">
                                <svg class="h-3.5 w-3.5 {{ $card['accent'] }} flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ $feat }}
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        {{-- =====================================================
             RECENT CASES
        ===================================================== --}}
        @if($secVisible('cases'))
        <section id="cases" class="py-20 sm:py-24 bg-blue-950/30">
            <div class="mx-auto max-w-[96rem] px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-10">
                    <div>
                        <span class="inline-flex items-center gap-2 rounded-full border border-blue-400/30 bg-blue-700/15 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-blue-300 mb-3">
                            {{ __('home.cases.section_badge') }}
                        </span>
                        <h2 class="text-3xl font-bold text-white sm:text-4xl">{{ __('home.cases.section_title') }}</h2>
                        <p class="mt-2 text-slate-400 text-sm">{{ __('home.cases.section_description') }}</p>
                    </div>
                    <a href="{{ route('applicant.register') }}"
                       class="flex-shrink-0 inline-flex items-center gap-2 rounded-xl border border-blue-700/50 bg-blue-900/30 px-4 py-2.5 text-sm font-semibold text-blue-100 hover:bg-blue-800/50 hover:text-white transition">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        {{ __('home.cases.file_new_case') }}
                    </a>
                </div>

                @if($recentCases->isNotEmpty())
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($recentCases as $case)
                    @php
                        $status = strtolower((string)($case->status ?? ''));
                        $sClass = $statusStyles[$status] ?? $statusStyles['default'];
                        $statusLabel = match($status) {
                            'pending'   => __('home.cases.status_labels.filed'),
                            'closed'    => __('home.cases.status_labels.resolved'),
                            'dismissed' => __('home.cases.status_labels.dismissed'),
                            default     => ucfirst($status ?: __('home.landing.recorded')),
                        };
                    @endphp
                    <article class="group rounded-3xl border border-blue-900/50 bg-blue-950/50 p-5 hover:border-blue-700/60 hover:bg-blue-950/70 transition flex flex-col gap-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <p class="text-[10px] font-semibold uppercase tracking-[0.25em] text-blue-300 mb-1">{{ $case->case_number }}</p>
                                <h3 class="text-sm font-semibold text-white leading-snug line-clamp-2">{{ $case->title }}</h3>
                            </div>
                            <span class="flex-shrink-0 inline-flex rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $sClass }}">{{ $statusLabel }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-auto pt-2 border-t border-white/5">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ __('home.cases.filed_prefix') }} {{ \App\Support\EthiopianDate::smartRelative($case->created_at, '') }}
                        </div>
                    </article>
                    @endforeach
                </div>

                {{-- Stats footer --}}
                <div class="mt-8 grid grid-cols-2 sm:grid-cols-4 gap-4 rounded-3xl border border-white/8 bg-white/4 p-6">
                    @foreach([
                        [__('home.cases.stats_footer.total_cases'),         $totalCases ?? 0],
                        [__('home.cases.stats_footer.resolved_cases'),      $resolvedCases ?? 0],
                        [__('home.cases.stats_footer.pending_cases'),       $pendingCases ?? 0],
                        [__('home.cases.stats_footer.resolution_efficiency'), ($totalCases ?? 0) > 0 ? round(($resolvedCases / $totalCases) * 100, 1).'%' : '—'],
                    ] as [$lbl, $v])
                    <div class="text-center">
                        <p class="text-xl sm:text-2xl font-bold text-white">{{ is_numeric($v) ? number_format($v) : $v }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $lbl }}</p>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="rounded-3xl border border-dashed border-blue-700/40 bg-blue-950/30 p-14 text-center">
                    <svg class="mx-auto h-12 w-12 text-blue-800 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-slate-400 font-medium mb-4">{{ __('home.cases.empty.headline') }}</p>
                    <a href="{{ route('applicant.register') }}" class="inline-flex items-center gap-2 rounded-xl bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-orange-400 transition">
                        {{ __('home.cases.empty.cta') }}
                    </a>
                </div>
                @endif
            </div>
        </section>
        @endif

        {{-- =====================================================
             RESOURCES & POSTS
        ===================================================== --}}
        @if($secVisible('resources') && $dbResources->isNotEmpty())
        <section id="resources" class="py-20 sm:py-24">
            <div class="mx-auto max-w-[96rem] px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <span class="inline-flex items-center gap-2 rounded-full border border-violet-400/30 bg-violet-700/15 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-violet-300 mb-4">
                        {{ __('home.resources.section_badge', [], null, 'Resources') }}
                    </span>
                    <h2 class="text-3xl font-bold text-white sm:text-4xl">{{ $sec('resources', 'title', __('home.resources.section_title', [], null, 'Resources & Updates')) }}</h2>
                    <p class="mt-3 text-slate-400 text-base max-w-2xl mx-auto">{{ $sec('resources', 'subtitle', __('home.resources.section_description', [], null, 'Posts, forms, and documents published by the court.')) }}</p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($dbResources->take(6) as $res)
                    @php
                    $typeIcon = match($res->type) {
                        'form'     => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
                        'document' => 'M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z',
                        'link'     => 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1',
                        default    => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                    };
                    $typeColor = match($res->type) {
                        'form'     => 'text-emerald-400',
                        'document' => 'text-blue-400',
                        'link'     => 'text-orange-400',
                        default    => 'text-violet-400',
                    };
                    @endphp
                    <article class="group rounded-3xl border border-white/8 bg-white/4 overflow-hidden hover:border-white/15 hover:bg-white/6 transition flex flex-col">
                        @if($res->cover_image)
                        <div class="h-40 overflow-hidden">
                            <img src="{{ asset('storage/' . $res->cover_image) }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300" alt="{{ $res->title }}">
                        </div>
                        @endif
                        <div class="p-5 flex flex-col flex-1">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/8 {{ $typeColor }}">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $typeIcon }}"/>
                                    </svg>
                                </div>
                                <span class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">{{ ucfirst($res->type) }}</span>
                                @if($res->is_featured)
                                <span class="ml-auto rounded-full bg-amber-500/20 text-amber-300 px-2 py-0.5 text-[10px] font-semibold">Featured</span>
                                @endif
                            </div>
                            <h3 class="text-sm font-semibold text-white leading-snug mb-2 flex-1">{{ $res->title }}</h3>
                            @if($res->description)
                            <p class="text-xs text-slate-400 leading-relaxed line-clamp-3 mb-4">{{ $res->description }}</p>
                            @endif
                            <div class="mt-auto pt-3 border-t border-white/5 flex items-center justify-between">
                                @if($res->published_at)
                                <span class="text-[10px] text-slate-500">{{ \App\Support\EthiopianDate::smartFormat($res->published_at, false, '') }}</span>
                                @endif
                                @if($res->isDownloadable() && $res->file_path)
                                <a href="{{ asset('storage/' . $res->file_path) }}" target="_blank" download
                                   class="inline-flex items-center gap-1 text-xs font-semibold text-blue-300 hover:text-blue-200 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Download
                                </a>
                                @elseif($res->external_url)
                                <a href="{{ $res->external_url }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1 text-xs font-semibold text-orange-300 hover:text-orange-200 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    Visit
                                </a>
                                @endif
                            </div>
                        </div>
                    </article>
                    @endforeach
                </div>

                @if($dbResources->count() > 6)
                <div class="mt-10 text-center">
                    <a href="{{ route('public.resources') }}"
                       class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/5 px-6 py-3 text-sm font-semibold text-white hover:bg-white/10 transition">
                        {{ __('home.resources.view_all', [], null, 'View all resources') }}
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
                @endif
            </div>
        </section>
        @endif

        {{-- =====================================================
             CTA BANNER
        ===================================================== --}}
        @if($secVisible('cta'))
        <section class="py-20 sm:py-24">
            <div class="mx-auto max-w-[96rem] px-4 sm:px-6 lg:px-8">
                <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-blue-900 via-blue-800 to-orange-700 p-10 sm:p-14 text-center">
                    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_rgba(255,255,255,0.07),_transparent_70%)]"></div>
                    <div class="relative">
                        <h2 class="text-3xl font-bold text-white sm:text-4xl mb-3">{{ $sec('cta', 'title', __('home.cta.title')) }}</h2>
                        <p class="text-blue-100 text-base max-w-xl mx-auto mb-8">{{ $sec('cta', 'description', __('home.cta.description')) }}</p>
                        <div class="flex flex-col sm:flex-row justify-center gap-3">
                            <a href="{{ $sec('cta', 'primary_href', route('applicant.register')) }}"
                               class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-6 py-3 text-sm font-semibold text-blue-900 shadow-lg hover:bg-blue-50 transition">
                                {{ $sec('cta', 'primary_label', __('home.cta.primary_button')) }}
                            </a>
                            <a href="{{ $sec('cta', 'secondary_href', route('applicant.login')) }}"
                               class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/30 bg-white/10 px-6 py-3 text-sm font-semibold text-white hover:bg-white/20 transition backdrop-blur-sm">
                                {{ $sec('cta', 'secondary_label', __('home.cta.secondary_button')) }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif

        {{-- =====================================================
             FAQ
        ===================================================== --}}
        <section id="faq" class="py-20 sm:py-24 bg-blue-950/30">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <span class="inline-flex items-center gap-2 rounded-full border border-blue-400/30 bg-blue-700/15 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-blue-300 mb-4">
                        {{ __('home.faq.section_badge') }}
                    </span>
                    <h2 class="text-3xl font-bold text-white sm:text-4xl">{{ __('home.faq.section_title') }}</h2>
                    <p class="mt-3 text-slate-400 text-base">{{ __('home.faq.section_description') }}</p>
                </div>

                <div class="space-y-3">
                    @foreach($faqItems as $i => $faq)
                    <div x-data="{ open: false }"
                         class="rounded-2xl border border-white/8 bg-white/4 overflow-hidden">
                        <button @click="open = !open"
                                class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left hover:bg-white/4 transition"
                                :aria-expanded="open">
                            <span class="text-sm font-semibold text-white">{{ $faq['question'] }}</span>
                            <svg class="flex-shrink-0 h-4 w-4 text-orange-400 transition-transform duration-200"
                                 :class="{ 'rotate-180': open }"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-1"
                             class="px-5 pb-4">
                            <p class="text-sm text-slate-400 leading-relaxed border-t border-white/6 pt-4">{{ $faq['answer'] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>

    </main>

    {{-- =====================================================
         FOOTER
    ===================================================== --}}
    <footer class="border-t border-blue-900/60 bg-blue-950/60">
        <div class="mx-auto max-w-[96rem] px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">

                {{-- Brand --}}
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        @if($logoPath)
                            <img src="{{ asset('storage/'.$logoPath) }}" alt="{{ $brandName }}"
                                 class="h-10 w-auto max-w-[2.5rem] object-contain rounded-xl">
                        @else
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-800/50 ring-1 ring-blue-600/50">
                                <svg class="h-5 w-5 text-orange-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m-7-4h14M6.5 7.5h11M9 3.75h6m-8.5 3L4 19.25A1.5 1.5 0 005.46 21h13.08A1.5 1.5 0 0020 19.25L17.5 6.75"/>
                                </svg>
                            </div>
                        @endif
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-[0.3em] text-blue-400">{{ __('home.footer.agency_label') }}</p>
                            <p class="text-sm font-semibold text-white">{{ $brandName }}</p>
                        </div>
                    </div>
                    <p class="text-sm text-slate-400 max-w-sm leading-relaxed">
                        {{ !empty($dbFooter['description']) ? $dbFooter['description'] : __('home.footer.description') }}
                    </p>
                    {{-- Contact info from DB --}}
                    @if(!empty($dbFooter['contact_phone']) || !empty($dbFooter['contact_email']) || !empty($dbFooter['contact_address']))
                    <div class="mt-4 space-y-1.5 text-xs text-slate-500">
                        @if(!empty($dbFooter['contact_phone']))
                        <div class="flex items-center gap-2">
                            <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <a href="tel:{{ $dbFooter['contact_phone'] }}" class="hover:text-orange-300 transition">{{ $dbFooter['contact_phone'] }}</a>
                        </div>
                        @endif
                        @if(!empty($dbFooter['contact_email']))
                        <div class="flex items-center gap-2">
                            <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <a href="mailto:{{ $dbFooter['contact_email'] }}" class="hover:text-orange-300 transition">{{ $dbFooter['contact_email'] }}</a>
                        </div>
                        @endif
                        @if(!empty($dbFooter['contact_address']))
                        <div class="flex items-center gap-2">
                            <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $dbFooter['contact_address'] }}</span>
                        </div>
                        @endif
                    </div>
                    @endif
                    {{-- Social icons --}}
                    @php
                    $socialIcons = [
                        'social_facebook'  => 'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z',
                        'social_twitter'   => 'M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z',
                        'social_linkedin'  => 'M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z',
                        'social_youtube'   => 'M22.54 6.42a2.78 2.78 0 00-1.94-1.96C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 00-1.94 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.4 19.54C5.12 20 12 20 12 20s6.88 0 8.6-.46a2.78 2.78 0 001.94-1.96A29 29 0 0023 12a29 29 0 00-.46-5.58z',
                        'social_telegram'  => 'M22 2L11 13M22 2L15 22l-4-9-9-4 20-7z',
                        'social_instagram' => 'M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zM17.5 6.5h.01M7.8 2h8.4C19.4 2 22 4.6 22 7.8v8.4a5.8 5.8 0 01-5.8 5.8H7.8C4.6 22 2 19.4 2 16.2V7.8A5.8 5.8 0 017.8 2z',
                    ];
                    $hasSocial = collect($socialIcons)->keys()->some(fn($k) => !empty($dbFooter[$k]));
                    @endphp
                    @if($hasSocial)
                    <div class="mt-4 flex items-center gap-3">
                        @foreach($socialIcons as $field => $icon)
                        @if(!empty($dbFooter[$field]))
                        <a href="{{ $dbFooter[$field] }}" target="_blank" rel="noopener"
                           class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/8 text-slate-400 hover:text-orange-300 hover:bg-white/12 transition">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                            </svg>
                        </a>
                        @endif
                        @endforeach
                    </div>
                    @endif
                    <div class="mt-4 flex items-center gap-4 text-sm text-slate-500">
                        <a href="{{ route('public.terms') }}" class="hover:text-orange-300 transition">{{ __('home.footer.terms_of_service') }}</a>
                        <span class="text-slate-700">•</span>
                        <a href="{{ route('public.signage') }}" class="hover:text-orange-300 transition">{{ __('home.landing.public_signage') }}</a>
                    </div>
                </div>

                {{-- Quick links --}}
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-4">{{ __('home.footer.quick_links') }}</h3>
                    <ul class="space-y-2">
                        @foreach($navLinks as [$href, $label, $icon])
                        <li>
                            <a href="{{ $href }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-orange-300 transition">
                                <svg class="h-3.5 w-3.5 opacity-50 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                                </svg>
                                {{ $label }}
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Portals --}}
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-widest text-slate-400 mb-4">{{ __('home.footer.portals') }}</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="{{ route('applicant.login') }}" class="text-sm text-slate-400 hover:text-orange-300 transition">
                                {{ __('home.nav.applicant_portal') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('applicant.register') }}" class="text-sm text-slate-400 hover:text-orange-300 transition">
                                {{ __('respondent.register_action') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('applicant.login', ['login_as' => 'respondent']) }}" class="text-sm text-slate-400 hover:text-orange-300 transition">
                                {{ __('home.nav.respondent_portal') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('public.signage') }}" class="text-sm text-slate-400 hover:text-orange-300 transition">
                                {{ __('home.landing.public_signage') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 border-t border-blue-900/50 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-600">
                <p>© {{ date('Y') }} {{ $brandName }}. {{ __('home.footer.rights') }}</p>
                <div class="flex items-center gap-3">
                    <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}"
                       class="rounded-full px-3 py-1 text-xs font-semibold transition {{ app()->getLocale() === 'en' ? 'bg-orange-500 text-white' : 'text-slate-400 hover:text-white' }}">EN</a>
                    <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}"
                       class="rounded-full px-3 py-1 text-xs font-semibold transition {{ app()->getLocale() === 'am' ? 'bg-orange-500 text-white' : 'text-slate-400 hover:text-white' }}">AM</a>
                    <a href="#home" aria-label="{{ __('home.accessibility.back_to_top') }}"
                       class="flex h-7 w-7 items-center justify-center rounded-full border border-blue-700/50 bg-blue-900/30 text-slate-400 hover:text-white hover:bg-blue-800/50 transition">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>


    {{-- ── Theme switcher ── --}}
    <div class="fixed bottom-6 right-6 z-[200]" @click.outside="themePanel = false">

        {{-- Panel --}}
        <div x-show="themePanel" x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
             class="absolute bottom-16 right-0 w-52 rounded-2xl border border-white/15 bg-slate-900/95 backdrop-blur-xl p-4 shadow-2xl origin-bottom-right">

            {{-- Dark / Light --}}
            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-2">Mode</p>
            <div class="flex gap-2 mb-4">
                <button @click="theme = 'dark'"
                        class="flex-1 flex items-center justify-center gap-1.5 rounded-xl px-3 py-2 text-xs font-semibold transition"
                        :class="theme === 'dark' ? 'bg-white/15 text-white ring-1 ring-white/25' : 'text-slate-400 hover:bg-white/8 hover:text-slate-200'">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    Dark
                </button>
                <button @click="theme = 'light'"
                        class="flex-1 flex items-center justify-center gap-1.5 rounded-xl px-3 py-2 text-xs font-semibold transition"
                        :class="theme === 'light' ? 'bg-white/15 text-white ring-1 ring-white/25' : 'text-slate-400 hover:bg-white/8 hover:text-slate-200'">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    Light
                </button>
            </div>

            {{-- Accent swatches --}}
            <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400 mb-2.5">Accent</p>
            <div class="grid grid-cols-5 gap-2">
                @foreach([
                    ['blue',    '#3b82f6', 'Blue'],
                    ['orange',  '#f97316', 'Orange'],
                    ['emerald', '#10b981', 'Emerald'],
                    ['violet',  '#8b5cf6', 'Violet'],
                    ['rose',    '#f43f5e', 'Rose'],
                ] as [$key, $hex, $name])
                <button @click="accent = '{{ $key }}'"
                        title="{{ $name }}"
                        class="relative h-8 w-8 rounded-full ring-2 ring-offset-2 ring-offset-slate-900 transition-all hover:scale-110 focus:outline-none"
                        :class="accent === '{{ $key }}' ? 'ring-white scale-110' : 'ring-transparent'"
                        style="background-color: {{ $hex }}">
                    <span class="sr-only">{{ $name }}</span>
                    <svg x-show="accent === '{{ $key }}'" class="absolute inset-0 m-auto h-3 w-3 text-white drop-shadow" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                    </svg>
                </button>
                @endforeach
            </div>
        </div>

        {{-- Toggle button --}}
        <button @click="themePanel = !themePanel"
                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-800/95 ring-1 ring-white/20 text-white shadow-2xl hover:bg-slate-700 transition-all backdrop-blur-sm"
                :class="{ 'ring-white/40 bg-slate-700': themePanel }"
                title="Theme settings">
            <svg x-show="theme === 'dark'" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            <svg x-show="theme === 'light'" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </button>
    </div>

</body>
</html>
