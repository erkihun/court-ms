<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .manual-content { color:#334155; font-size:1rem; line-height:1.75; overflow-wrap:anywhere; }
        .manual-content > :first-child { margin-top:0; }
        .manual-content > :last-child { margin-bottom:0; }
        .manual-content h1,.manual-content h2,.manual-content h3,.manual-content h4,.manual-content h5,.manual-content h6 {
            color:#0f172a; font-weight:700; line-height:1.25; margin:1.75em 0 .7em;
        }
        .manual-content h1 { font-size:2rem; }
        .manual-content h2 { font-size:1.6rem; padding-bottom:.35rem; border-bottom:1px solid #e2e8f0; }
        .manual-content h3 { font-size:1.3rem; }
        .manual-content h4 { font-size:1.1rem; }
        .manual-content p { margin:.9em 0; }
        .manual-content ul,.manual-content ol { margin:1em 0; padding-inline-start:1.75rem; }
        .manual-content ul { list-style:disc; }
        .manual-content ol { list-style:decimal; }
        .manual-content li { margin:.35em 0; padding-inline-start:.25rem; }
        .manual-content li > ul,.manual-content li > ol { margin:.35em 0; }
        .manual-content blockquote { margin:1.25em 0; border-inline-start:4px solid #3b82f6; background:#eff6ff; padding:1rem 1.25rem; color:#334155; }
        .manual-content a { color:#1d4ed8; text-decoration:underline; text-underline-offset:2px; }
        .manual-content strong,.manual-content b { color:#0f172a; font-weight:700; }
        .manual-content table { width:100%; margin:1.5em 0; border-collapse:collapse; display:block; overflow-x:auto; }
        .manual-content th,.manual-content td { min-width:8rem; border:1px solid #cbd5e1; padding:.7rem .8rem; text-align:start; vertical-align:top; }
        .manual-content th { background:#f1f5f9; color:#0f172a; font-weight:700; }
        .manual-content tr:nth-child(even) td { background:#f8fafc; }
        .manual-content hr { margin:2rem 0; border:0; border-top:1px solid #cbd5e1; }
        .manual-content img { max-width:100%; height:auto; margin:1.25rem auto; border-radius:.75rem; }
        .manual-content [dir="rtl"] { direction:rtl; text-align:right; }
        @media print {
            header { display:none; }
            body { background:white; }
            .manual-card { border:0 !important; box-shadow:none !important; }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur-xl">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4 sm:px-6">
            <a href="{{ route('landing.home') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900">{{ __('home.nav.home') }}</a>
            <div class="flex rounded-full border border-slate-200 bg-slate-50 p-1 text-xs font-semibold">
                <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}" class="rounded-full px-3 py-1 {{ app()->getLocale() === 'en' ? 'bg-blue-600 text-white' : 'text-slate-600' }}">EN</a>
                <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}" class="rounded-full px-3 py-1 {{ app()->getLocale() === 'am' ? 'bg-blue-600 text-white' : 'text-slate-600' }}">AM</a>
            </div>
        </div>
    </header>
    <main class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-16">
        <article class="manual-card overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 bg-gradient-to-r from-slate-950 to-blue-950 px-6 py-10 text-white sm:px-10">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-300">{{ __('home.nav.user_manual') }}</p>
                <h1 class="mt-3 text-3xl font-bold sm:text-4xl">{{ $title }}</h1>
            </div>
            <div class="px-6 py-8 sm:px-10 sm:py-12">
                <div class="manual-content">{!! $content !!}</div>
            </div>
        </article>
    </main>
</body>
</html>
