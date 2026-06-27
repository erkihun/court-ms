@props([
    'title' => __('app.court_portal'),
    'subtitle' => null,
    'portal' => 'applicant',
    'accent' => null,
    'loginRoute' => null,
])

@php
$layout = $publicLayout ?? [];
if (is_object($layout)) {
    $layout = method_exists($layout, 'toArray') ? $layout->toArray() : (array) $layout;
}
$systemSettings = $layout['systemSettings'] ?? null;
$brandName = $layout['brandName'] ?? config('app.name', __('app.court_ms'));
$shortName = $layout['shortName'] ?? $brandName;
$logoPath = $layout['logoPath'] ?? null;
$accent = $accent ?: ($portal === 'admin' ? 'blue' : 'orange');
if ($loginRoute === null) {
    $loginRoute = $portal === 'admin' ? 'login' : 'applicant.login';
}
$loginUrl = $loginRoute && \Illuminate\Support\Facades\Route::has($loginRoute) ? route($loginRoute) : null;
$accentColors = match ($accent) {
    'blue', 'admin' => [
        'base' => '#2563eb',
        'hover' => '#1d4ed8',
        'soft' => '#eff6ff',
        'softText' => '#1d4ed8',
        'ring' => 'rgba(37, 99, 235, .22)',
    ],
    default => [
        'base' => '#f97316',
        'hover' => '#ea580c',
        'soft' => '#fff7ed',
        'softText' => '#c2410c',
        'ring' => 'rgba(249, 115, 22, .24)',
    ],
};
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ar']) ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <title>{{ $title }} | {{ $brandName }}</title>

    <script>
        (() => {
            const theme = localStorage.getItem('theme') || 'system';
            const dark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
            document.documentElement.dataset.theme = theme;
        })();
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .auth-page {
            --auth-accent: {{ $accentColors['base'] }};
            --auth-accent-hover: {{ $accentColors['hover'] }};
            --auth-accent-soft: {{ $accentColors['soft'] }};
            --auth-accent-soft-text: {{ $accentColors['softText'] }};
            --auth-ring: {{ $accentColors['ring'] }};
            background:
                radial-gradient(circle at top, color-mix(in srgb, var(--auth-accent) 10%, transparent), transparent 36rem),
                #f8fafc;
        }
        .auth-card {
            border: 1px solid #e2e8f0;
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 20px 45px rgba(15, 23, 42, .08);
        }
        .auth-logo-fallback { background: var(--auth-accent); }
        .auth-label {
            display: block;
            margin-bottom: .375rem;
            font-size: .875rem;
            font-weight: 600;
            color: #334155;
        }
        .auth-input {
            width: 100%;
            border-radius: .625rem;
            border: 1px solid #cbd5e1;
            padding: .7rem .85rem;
            font-size: .875rem;
            color: #0f172a;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .auth-input:focus {
            border-color: var(--auth-accent);
            box-shadow: 0 0 0 3px var(--auth-ring);
            outline: none;
        }
        .auth-primary-btn {
            display: inline-flex;
            width: 100%;
            align-items: center;
            justify-content: center;
            gap: .375rem;
            border-radius: .625rem;
            background: var(--auth-accent);
            padding: .7rem 1rem;
            font-size: .875rem;
            font-weight: 700;
            color: #fff;
            transition: background-color .15s ease, box-shadow .15s ease;
        }
        .auth-primary-btn:hover { background: var(--auth-accent-hover); }
        .auth-primary-btn:focus {
            box-shadow: 0 0 0 3px var(--auth-ring);
            outline: none;
        }
        .auth-secondary-btn {
            display: inline-flex;
            width: 100%;
            align-items: center;
            justify-content: center;
            border-radius: .625rem;
            border: 1px solid #cbd5e1;
            padding: .65rem 1rem;
            font-size: .875rem;
            font-weight: 700;
            color: #334155;
            transition: border-color .15s ease, color .15s ease, box-shadow .15s ease;
        }
        .auth-secondary-btn:hover {
            border-color: var(--auth-accent);
            color: var(--auth-accent-hover);
        }
        .auth-link {
            color: #64748b;
            font-size: .875rem;
            font-weight: 600;
        }
        .auth-link:hover { color: var(--auth-accent-hover); }
        .auth-accent-link {
            color: var(--auth-accent);
            font-size: .875rem;
            font-weight: 700;
        }
        .auth-accent-link:hover { color: var(--auth-accent-hover); }
        .auth-alert {
            border-radius: .625rem;
            border: 1px solid #e2e8f0;
            padding: .65rem .8rem;
            font-size: .875rem;
            line-height: 1.45;
        }
        .auth-alert-success {
            border-color: #a7f3d0;
            background: #ecfdf5;
            color: #065f46;
        }
        .auth-alert-info {
            border-color: #bfdbfe;
            background: #eff6ff;
            color: #1e40af;
        }
        .auth-alert-error {
            border-color: #fecaca;
            background: #fef2f2;
            color: #991b1b;
        }
    </style>

    @if(!empty($systemSettings?->favicon_path))
    <link rel="icon" href="{{ asset('storage/'.$systemSettings->favicon_path) }}">
    @endif

    @vite(['resources/css/app.css','resources/js/app.js'])
    @stack('head')
</head>

<body class="auth-page ui-shell min-h-screen font-ui text-[var(--text)]">
    <main class="min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="auth-card rounded-2xl p-6 md:p-7">

                {{-- Card header: system logo + brand + page title --}}
                <div class="flex flex-col items-center text-center mb-6">
                    @if($logoPath)
                    <img src="{{ asset('storage/'.$logoPath) }}" alt="{{ $brandName }}" class="h-14 w-auto object-contain mb-3">
                    @else
                    <span class="auth-logo-fallback inline-flex h-14 w-14 items-center justify-center rounded-2xl text-base font-bold uppercase tracking-wide text-white mb-3">
                        {{ \Illuminate\Support\Str::of($shortName)->substr(0, 2) }}
                    </span>
                    @endif
                    <div class="text-sm font-semibold text-slate-500">{{ $brandName }}</div>
                    <h1 class="mt-1 text-lg md:text-xl font-semibold text-slate-900">{{ $title }}</h1>
                    @if($subtitle)
                    <p class="mt-1 text-xs md:text-sm text-slate-500">{{ $subtitle }}</p>
                    @endif
                </div>

                {{ $slot }}

                @isset($footer)
                {{ $footer }}
                @endisset
            </div>

            @if($loginUrl)
            <div class="mt-4 text-center">
                <a href="{{ $loginUrl }}" class="auth-link">{{ __('auth.back_to_login') }}</a>
            </div>
            @endif
        </div>
    </main>

    @stack('scripts')
</body>

</html>
