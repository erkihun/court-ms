@php
$settings = null;
try {
    $settings = \App\Models\SystemSetting::query()->first();
} catch (\Throwable $e) {
    $settings = null;
}

$brandName = $settings?->app_name ?? config('app.name', 'Court MS');
$logoPath = $settings?->logo_path ?? null;
$faviconPath = $settings?->favicon_path ?? null;
$bannerPath = $settings?->banner_path ?? null;
@endphp

<x-guest-layout variant="split-auth" :show-toasts="false">
    @push('head')
        @if($faviconPath)
            <link rel="icon" href="{{ asset('storage/'.$faviconPath) }}">
        @endif

        <style>
            .admin-login-shell {
                background:
                    linear-gradient(115deg, rgb(15 23 42 / 0.98), rgb(30 41 59 / 0.94) 42%, rgb(8 47 73 / 0.9)),
                    @if($bannerPath)
                    url("{{ asset('storage/'.$bannerPath) }}")
                    @else
                    linear-gradient(135deg, #0f172a, #1e293b)
                    @endif;
                background-position: center;
                background-size: cover;
                background-repeat: no-repeat;
            }

            .admin-login-shell::before {
                background-image:
                    linear-gradient(rgb(255 255 255 / 0.07) 1px, transparent 1px),
                    linear-gradient(90deg, rgb(255 255 255 / 0.07) 1px, transparent 1px);
                background-size: 44px 44px;
                content: "";
                inset: 0;
                mask-image: linear-gradient(90deg, rgb(0 0 0 / 0.9), transparent 78%);
                pointer-events: none;
                position: absolute;
            }

            .admin-login-beam {
                animation: admin-login-beam 7s ease-in-out infinite alternate;
                background: linear-gradient(90deg, transparent, rgb(96 165 250 / 0.45), rgb(249 115 22 / 0.28), transparent);
                height: 1px;
                left: -20%;
                position: absolute;
                top: 26%;
                width: 140%;
            }

            /* ── Advanced ambient animation layer ─────────────────── */

            /* Drifting aurora mesh behind everything */
            .admin-aurora {
                position: absolute;
                inset: -25%;
                pointer-events: none;
                background:
                    radial-gradient(40% 38% at 22% 28%, rgb(59 130 246 / 0.40), transparent 60%),
                    radial-gradient(36% 34% at 78% 26%, rgb(14 165 233 / 0.30), transparent 62%),
                    radial-gradient(44% 40% at 64% 80%, rgb(249 115 22 / 0.24), transparent 60%),
                    radial-gradient(40% 38% at 30% 76%, rgb(56 189 248 / 0.22), transparent 62%);
                filter: blur(36px);
                animation: admin-aurora-drift 22s ease-in-out infinite alternate;
            }

            @keyframes admin-aurora-drift {
                0%   { transform: translate3d(0, 0, 0) scale(1)    rotate(0deg); }
                50%  { transform: translate3d(3%, -2%, 0) scale(1.08) rotate(4deg); }
                100% { transform: translate3d(-3%, 2%, 0) scale(1.04) rotate(-3deg); }
            }

            /* ── Galaxy starfield: parallax layers drifting left → right ── */
            .admin-galaxy {
                position: absolute;
                inset: 0;
                pointer-events: none;
                overflow: hidden;
                z-index: 0;
            }
            .admin-galaxy .layer {
                position: absolute;
                top: 0;
                left: 0;
                height: 100%;
                width: 200%;
                background-repeat: repeat;
                will-change: transform;
            }
            /* far layer – tiny, faint, slow */
            .admin-galaxy .stars-far {
                background-image:
                    radial-gradient(1px 1px at 20% 30%,  rgb(255 255 255 / 0.6), transparent),
                    radial-gradient(1px 1px at 60% 70%,  rgb(191 219 254 / 0.5), transparent),
                    radial-gradient(1px 1px at 80% 20%,  rgb(255 255 255 / 0.5), transparent),
                    radial-gradient(1px 1px at 40% 85%,  rgb(199 210 254 / 0.45), transparent),
                    radial-gradient(1px 1px at 10% 60%,  rgb(255 255 255 / 0.4), transparent);
                background-size: 320px 320px;
                animation: admin-galaxy-drift 90s linear infinite;
            }
            /* mid layer – small, medium speed */
            .admin-galaxy .stars-mid {
                background-image:
                    radial-gradient(1.6px 1.6px at 15% 50%, rgb(255 255 255 / 0.8), transparent),
                    radial-gradient(1.6px 1.6px at 55% 25%, rgb(147 197 253 / 0.7), transparent),
                    radial-gradient(1.6px 1.6px at 75% 65%, rgb(255 255 255 / 0.7), transparent),
                    radial-gradient(1.6px 1.6px at 35% 80%, rgb(253 186 116 / 0.55), transparent);
                background-size: 420px 420px;
                animation: admin-galaxy-drift 55s linear infinite;
            }
            /* near layer – bigger, brighter, fast, twinkling */
            .admin-galaxy .stars-near {
                background-image:
                    radial-gradient(2.4px 2.4px at 25% 35%, rgb(255 255 255 / 0.95), transparent),
                    radial-gradient(2.2px 2.2px at 70% 55%, rgb(191 219 254 / 0.85), transparent),
                    radial-gradient(2px 2px   at 88% 30%, rgb(253 186 116 / 0.7), transparent);
                background-size: 560px 560px;
                animation: admin-galaxy-drift 34s linear infinite, admin-galaxy-twinkle 4s ease-in-out infinite;
            }

            @keyframes admin-galaxy-drift {
                from { transform: translateX(-50%); }
                to   { transform: translateX(0); }
            }
            @keyframes admin-galaxy-twinkle {
                0%, 100% { opacity: 0.65; }
                50%      { opacity: 1; }
            }

            /* Self-drawing scales of justice */
            .admin-scale-emblem {
                animation: admin-scale-balance 6s ease-in-out infinite;
                transform-origin: 100px 30px;
            }
            .admin-scale-emblem path,
            .admin-scale-emblem line {
                stroke-dasharray: 600;
                stroke-dashoffset: 600;
                animation: admin-scale-draw 2.4s ease-out forwards;
            }
            .admin-scale-emblem .pan { animation-delay: 1.1s; }

            @keyframes admin-scale-draw {
                to { stroke-dashoffset: 0; }
            }
            @keyframes admin-scale-balance {
                0%, 100% { transform: rotate(-2.5deg); }
                50%      { transform: rotate(2.5deg); }
            }

            /* Staggered fade-up for content blocks */
            .admin-stagger > * {
                opacity: 0;
                animation: admin-stagger-up 700ms cubic-bezier(0.16, 1, 0.3, 1) both;
            }
            .admin-stagger > *:nth-child(1) { animation-delay: 120ms; }
            .admin-stagger > *:nth-child(2) { animation-delay: 260ms; }
            .admin-stagger > *:nth-child(3) { animation-delay: 400ms; }

            @keyframes admin-stagger-up {
                from { opacity: 0; transform: translateY(16px); }
                to   { opacity: 1; transform: translateY(0); }
            }

            /* Soft pulsing glow ring around the emblem */
            .admin-emblem-glow {
                animation: admin-emblem-glow 4s ease-in-out infinite;
            }
            @keyframes admin-emblem-glow {
                0%, 100% { opacity: 0.35; transform: scale(1); }
                50%      { opacity: 0.7;  transform: scale(1.06); }
            }

            /* Entrance handled by Alpine x-transition (fade-in + slide-up).
               Keep [x-cloak] hidden until Alpine initialises to avoid a flash. */
            [x-cloak] {
                display: none !important;
            }

            .admin-login-signal {
                transition: transform 220ms ease, border-color 220ms ease, background-color 220ms ease;
            }

            .admin-login-signal:hover {
                border-color: rgb(147 197 253 / 0.36);
                background-color: rgb(255 255 255 / 0.14);
                transform: translateY(-3px);
            }

            /* Glass inputs: semi-transparent over the frosted card. */
            .admin-login-form-card .admin-login-input {
                background-color: rgb(255 255 255 / 0.08) !important;
                border-color: rgb(255 255 255 / 0.22) !important;
                color: #f8fafc !important;
            }
            .admin-login-form-card .admin-login-input::placeholder {
                color: rgb(226 232 240 / 0.6) !important;
            }
            .admin-login-form-card .admin-login-input:focus {
                background-color: rgb(255 255 255 / 0.12) !important;
                border-color: rgb(147 197 253 / 0.7) !important;
            }
            /* Keep browser autofill from forcing an opaque background. */
            .admin-login-form-card .admin-login-input:-webkit-autofill,
            .admin-login-form-card .admin-login-input:-webkit-autofill:hover,
            .admin-login-form-card .admin-login-input:-webkit-autofill:focus {
                -webkit-text-fill-color: #f8fafc !important;
                caret-color: #f8fafc !important;
                transition: background-color 9999s ease-in-out 0s;
            }

            .admin-login-input:focus {
                transform: translateY(-1px);
            }

            .admin-login-submit {
                position: relative;
                isolation: isolate;
                overflow: hidden;
            }

            .admin-login-submit::before {
                background: linear-gradient(90deg, transparent, rgb(255 255 255 / 0.38), transparent);
                content: "";
                inset: 0 auto 0 -55%;
                position: absolute;
                transform: skewX(-18deg);
                transition: left 520ms ease;
                width: 45%;
                z-index: -1;
            }

            .admin-login-submit:hover::before {
                left: 118%;
            }

            /* ── Login card hover animation ──────────────────────────── */
            .admin-login-form-card {
                /* Fully transparent surface — the background shows through. */
                background-color: transparent !important;
                -webkit-backdrop-filter: none;
                backdrop-filter: none;
                transition:
                    transform 420ms cubic-bezier(0.16, 1, 0.3, 1),
                    box-shadow 420ms ease,
                    border-color 420ms ease;
                will-change: transform;
            }
            .admin-login-form-card:hover {
                transform: translateY(-6px) scale(1.012);
                border-color: rgb(59 130 246 / 0.45);
                box-shadow:
                    0 30px 60px -18px rgb(2 6 23 / 0.30),
                    0 0 0 1px rgb(59 130 246 / 0.18),
                    0 0 36px -6px rgb(59 130 246 / 0.30);
            }
            /* Animated sheen that sweeps across on hover */
            .admin-login-form-card::after {
                content: "";
                position: absolute;
                inset: 0;
                border-radius: inherit;
                pointer-events: none;
                background: linear-gradient(115deg, transparent 30%, rgb(255 255 255 / 0.18) 48%, transparent 66%);
                background-size: 280% 100%;
                background-position: 120% 0;
                opacity: 0;
                transition: opacity 200ms ease;
            }
            .admin-login-form-card:hover::after {
                opacity: 1;
                animation: admin-card-sheen 900ms ease forwards;
            }
            @keyframes admin-card-sheen {
                from { background-position: 120% 0; }
                to   { background-position: -40% 0; }
            }

            @keyframes admin-login-beam {
                from {
                    opacity: 0.32;
                    transform: translateX(-8%) rotate(-4deg);
                }
                to {
                    opacity: 0.72;
                    transform: translateX(8%) rotate(-4deg);
                }
            }

            @media (prefers-reduced-motion: reduce) {
                .admin-login-beam,
                .admin-login-panel,
                .admin-login-form-card,
                .admin-aurora,
                .admin-galaxy .layer,
                .admin-scale-emblem,
                .admin-scale-emblem path,
                .admin-scale-emblem line,
                .admin-emblem-glow,
                .admin-stagger > * {
                    animation: none !important;
                }

                /* Ensure self-drawing strokes and staggered content are fully visible */
                .admin-scale-emblem path,
                .admin-scale-emblem line {
                    stroke-dashoffset: 0 !important;
                }
                .admin-stagger > * {
                    opacity: 1 !important;
                    transform: none !important;
                }

                .admin-login-input:focus {
                    transform: none;
                }

                /* Neutralise the Alpine x-transition + hover effects for
                   motion-sensitive users: the card appears instantly. */
                .admin-login-form-card,
                .admin-login-form-card:hover {
                    transition: none !important;
                    opacity: 1 !important;
                    transform: none !important;
                }
                .admin-login-form-card:hover::after {
                    animation: none !important;
                    opacity: 0 !important;
                }
            }
        </style>
    @endpush

    <div class="relative min-h-screen overflow-hidden bg-slate-100 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
        <div class="relative min-h-screen">
            <section class="admin-login-shell absolute inset-0 hidden min-h-screen overflow-hidden lg:flex">
                <div class="admin-aurora"></div>

                {{-- Galaxy starfield drifting left → right --}}
                <div class="admin-galaxy" aria-hidden="true">
                    <div class="layer stars-far"></div>
                    <div class="layer stars-mid"></div>
                    <div class="layer stars-near"></div>
                </div>

                <div class="admin-login-beam"></div>
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_18%,rgba(96,165,250,0.24),transparent_30%),radial-gradient(circle_at_76%_76%,rgba(249,115,22,0.18),transparent_28%)]"></div>

                {{-- Large ambient scales-of-justice emblem (decorative) --}}
                <div class="pointer-events-none absolute -right-10 top-1/2 hidden -translate-y-1/2 xl:block" aria-hidden="true">
                    <div class="relative h-[360px] w-[360px]">
                        <div class="admin-emblem-glow absolute inset-8 rounded-full bg-[radial-gradient(circle,rgba(96,165,250,0.28),transparent_68%)]"></div>
                        <svg class="admin-scale-emblem relative h-full w-full text-blue-100/25" viewBox="0 0 200 200" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="100" y1="30" x2="100" y2="150"/>
                            <line x1="40" y1="60" x2="160" y2="60"/>
                            <circle cx="100" cy="30" r="6" stroke="none" fill="currentColor"/>
                            <line x1="70" y1="150" x2="130" y2="150"/>
                            <line x1="40" y1="60" x2="22" y2="104"/>
                            <line x1="40" y1="60" x2="58" y2="104"/>
                            <line x1="160" y1="60" x2="142" y2="104"/>
                            <line x1="160" y1="60" x2="178" y2="104"/>
                            <path class="pan" d="M18 104c0 14 10 22 22 22s22-8 22-22Z"/>
                            <path class="pan" d="M138 104c0 14 10 22 22 22s22-8 22-22Z"/>
                        </svg>
                    </div>
                </div>
                <div class="relative z-10 flex w-full max-w-3xl flex-col justify-between px-10 py-12 xl:px-14">
                    <div class="flex items-center gap-4">
                        @if($logoPath)
                            <div class="flex h-16 w-16 items-center justify-center">
                                <img src="{{ asset('storage/'.$logoPath) }}" alt="{{ $brandName }}" class="max-h-full w-auto object-contain">
                            </div>
                        @else
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl border border-white/15 bg-white/10 text-lg font-bold text-white shadow-2xl shadow-slate-950/20 backdrop-blur">
                                {{ \Illuminate\Support\Str::of($brandName)->substr(0, 2) }}
                            </div>
                        @endif

                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-blue-100/80">{{ __('auth.admin_access_label') }}</p>
                            <h1 class="mt-1 text-xl font-semibold text-white">{{ $brandName }}</h1>
                        </div>
                    </div>

                    <div class="admin-login-panel admin-stagger max-w-2xl space-y-6 text-white">
                        <div class="space-y-4">
                            <p class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-3.5 py-1.5 text-xs font-semibold uppercase tracking-[0.2em] text-blue-50/90 shadow-lg shadow-slate-950/10 backdrop-blur">
                                {{ __('auth.admin_system_label') }}
                            </p>
                            <div class="space-y-4">
                                <h2 class="max-w-xl text-2xl font-semibold leading-tight tracking-tight text-justify xl:text-3xl">
                                    {{ __('auth.admin_login_headline') }}
                                </h2>
                                <p class="max-w-xl text-base leading-7 text-justify text-slate-200/88">
                                    {{ __('auth.admin_login_intro') }}
                                </p>
                            </div>
                        </div>

                        <div class="grid max-w-2xl gap-4 sm:grid-cols-2">
                            <div class="admin-login-signal rounded-2xl border border-white/12 bg-white/10 p-3 shadow-xl shadow-slate-950/10 backdrop-blur">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-200">{{ __('auth.trusted_access_title') }}</div>
                                <p class="mt-2 text-sm leading-5 text-justify text-slate-200/82">
                                    {{ __('auth.trusted_access_body') }}
                                </p>
                            </div>
                            <div class="admin-login-signal rounded-2xl border border-white/12 bg-white/10 p-3 shadow-xl shadow-slate-950/10 backdrop-blur">
                                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-200">{{ __('auth.operational_clarity_title') }}</div>
                                <p class="mt-2 text-sm leading-5 text-justify text-slate-200/82">
                                    {{ __('auth.operational_clarity_body') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-4 text-sm text-slate-200/78">
                        <span>{{ now()->format('Y') }} {{ $brandName }}</span>
                        <span class="h-px flex-1 bg-gradient-to-r from-white/20 to-transparent"></span>
                    </div>
                </div>
            </section>

            <section class="relative z-20 flex min-h-screen items-center justify-center px-4 py-8 sm:px-6 lg:ml-auto lg:max-w-xl lg:justify-end lg:px-10 xl:px-14">
                <div class="relative mx-auto w-full max-w-md lg:mx-0 lg:ml-auto">
                    <div class="mb-6 flex flex-col items-center justify-center gap-4 text-center lg:hidden">
                        @if($logoPath)
                            <div class="flex h-16 w-16 items-center justify-center">
                                <img src="{{ asset('storage/'.$logoPath) }}" alt="{{ $brandName }}" class="max-h-full w-auto object-contain">
                            </div>
                        @else
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-slate-200 bg-white text-sm font-bold text-blue-700 shadow-xl shadow-slate-900/8 dark:border-slate-700 dark:bg-slate-900 dark:text-blue-300">
                                {{ \Illuminate\Support\Str::of($brandName)->substr(0, 2) }}
                            </div>
                        @endif

                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-700 dark:text-blue-300">{{ __('auth.admin_portal') }}</div>
                            <div class="text-lg font-semibold text-slate-950 dark:text-slate-100">{{ $brandName }}</div>
                        </div>
                    </div>

                    <div
                        x-data="{ shown: false }"
                        x-init="$nextTick(() => shown = true)"
                        x-show="shown"
                        x-transition:enter="transition ease-out duration-500"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="admin-login-form-card relative overflow-hidden rounded-2xl border border-white/25 shadow-2xl shadow-black/40 ring-1 ring-white/10">
                        <div class="h-1.5 bg-[linear-gradient(90deg,#2563eb_0%,#0f766e_48%,#f97316_100%)]"></div>
                        <div class="dark p-6 sm:p-8 text-slate-100">
                            <div class="space-y-2">
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-700 dark:text-blue-300">{{ __('auth.admin_access_label') }}</p>
                                <h2 class="text-2xl font-semibold tracking-tight text-slate-950 dark:text-slate-100">{{ __('auth.login_title') }}</h2>
                                <p class="text-sm leading-6 text-slate-600 dark:text-slate-400">
                                    {{ __('auth.admin_login_subtitle') }}
                                </p>
                            </div>

                            @if(session('error') || $errors->any())
                            <div role="alert" aria-live="assertive" class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
                                <div class="flex items-start gap-2.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>
                                    </svg>
                                    <div class="min-w-0">
                                        @if(session('error'))
                                        <p class="font-semibold">{{ session('error') }}</p>
                                        @endif
                                        @if($errors->any())
                                        <ul class="space-y-1 {{ session('error') ? 'mt-1.5' : '' }}">
                                            @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5"
                                  x-data="{ submitting: false }"
                                  @submit="submitting = true">
                                @csrf

                                <div class="space-y-2">
                                    <label for="email" class="block text-sm font-medium text-slate-600 dark:text-slate-300">{{ __('auth.email') }}</label>
                                    <input
                                        id="email"
                                        name="email"
                                        type="email"
                                        value="{{ old('email') }}"
                                        required
                                        @empty(old('email')) autofocus @endempty
                                        autocomplete="username"
                                        style="background-color: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.22); color: #f8fafc;"
                                        class="admin-login-input w-full rounded-xl border px-3.5 py-3 text-sm shadow-sm transition-base focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                                    >
                                </div>

                                <div class="space-y-2">
                                    <div class="flex items-center justify-between gap-3">
                                        <label for="password" class="block text-sm font-medium text-slate-600 dark:text-slate-300">{{ __('auth.password') }}</label>
                                        @if (Route::has('password.request'))
                                            <a href="{{ route('password.request') }}" class="text-sm font-medium text-blue-700 transition-fast hover:text-blue-800 dark:text-blue-300 dark:hover:text-blue-200">
                                                {{ __('auth.forgot_password') }}
                                            </a>
                                        @endif
                                    </div>

                                    <div class="relative">
                                        <input
                                            id="password"
                                            name="password"
                                            type="password"
                                            required
                                            @if(old('email')) autofocus @endif
                                            autocomplete="current-password"
                                            style="background-color: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.22); color: #f8fafc;"
                                            class="admin-login-input w-full rounded-xl border px-3.5 py-3 pr-11 text-sm shadow-sm transition-base focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                                        >
                                        <button
                                            type="button"
                                            id="toggle-password"
                                            class="absolute right-3 top-1/2 inline-flex -translate-y-1/2 items-center justify-center rounded-md p-1 text-slate-500 transition-fast hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                            aria-label="{{ __('auth.show_password') }}"
                                            aria-pressed="false"
                                        >
                                            <svg id="eye-open" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <svg id="eye-closed" xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 3l18 18M10.477 10.48a3 3 0 004.243 4.243M9.88 5.08A9.953 9.953 0 0112 5c4.478 0 8.269 2.943 9.543 7a10.047 10.047 0 01-4.132 5.411M6.228 6.228A10.045 10.045 0 002.457 12c.738 2.344 2.327 4.286 4.45 5.41M12 19c-1.043 0-2.054-.147-3.01-.423" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    <label for="remember_me" class="inline-flex items-center gap-3 text-sm text-slate-600 dark:text-slate-300">
                                        <input
                                            id="remember_me"
                                            type="checkbox"
                                            name="remember"
                                            class="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900"
                                        >
                                        <span>{{ __('auth.remember_me') }}</span>
                                    </label>
                                </div>

                                <button
                                    type="submit"
                                    x-bind:disabled="submitting"
                                    class="admin-login-submit inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-700 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-900/18 transition-base hover:-translate-y-0.5 hover:bg-blue-800 hover:shadow-blue-900/28 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:translate-y-0 disabled:cursor-not-allowed disabled:opacity-80 disabled:hover:translate-y-0 dark:focus:ring-offset-slate-950"
                                >
                                    <svg x-show="submitting" x-cloak class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    <span x-text="submitting ? @js(__('auth.sign_in')) + '…' : @js(__('auth.sign_in'))">{{ __('auth.sign_in') }}</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        // Reload when browser restores this page from the back-forward cache (BFCache).
        // BFCache ignores Cache-Control: no-store and serves a frozen snapshot with a
        // stale CSRF token, causing 419 on the next login attempt after logout.
        window.addEventListener('pageshow', function (e) {
            if (e.persisted) window.location.reload();
        });

        (function() {
            const input = document.getElementById('password');
            const toggle = document.getElementById('toggle-password');
            const eyeOpen = document.getElementById('eye-open');
            const eyeClosed = document.getElementById('eye-closed');

            if (!input || !toggle || !eyeOpen || !eyeClosed) return;

            toggle.addEventListener('click', () => {
                const isHidden = input.type === 'password';
                input.type = isHidden ? 'text' : 'password';
                eyeOpen.classList.toggle('hidden', !isHidden);
                eyeClosed.classList.toggle('hidden', isHidden);
                toggle.setAttribute('aria-pressed', String(isHidden));
                const showPasswordLabel = @js(__('auth.show_password'));
                const hidePasswordLabel = @js(__('auth.hide_password'));
                toggle.setAttribute('aria-label', isHidden ? hidePasswordLabel : showPasswordLabel);
            });
        })();
    </script>
</x-guest-layout>
