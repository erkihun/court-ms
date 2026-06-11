<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Resources – {{ config('app.name', 'Court MS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-slate-950 text-slate-100 antialiased">

    {{-- Header --}}
    <header class="sticky top-0 z-50 border-b border-blue-900/60 bg-slate-950/80 backdrop-blur-xl">
        <div class="mx-auto flex max-w-[96rem] items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('landing.home') }}" class="flex items-center gap-3 flex-shrink-0">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-800/50 ring-1 ring-blue-600/50">
                    <svg class="h-5 w-5 text-orange-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m-7-4h14M6.5 7.5h11M9 3.75h6m-8.5 3L4 19.25A1.5 1.5 0 005.46 21h13.08A1.5 1.5 0 0020 19.25L17.5 6.75"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-white">{{ config('app.name', 'Court MS') }}</p>
            </a>
            <a href="{{ route('landing.home') }}"
               class="inline-flex items-center gap-1.5 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-300 hover:text-white hover:bg-white/10 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Home
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-[96rem] px-4 sm:px-6 lg:px-8 py-16">

        {{-- Page header --}}
        <div class="mb-12">
            <span class="inline-flex items-center gap-2 rounded-full border border-violet-400/30 bg-violet-700/15 px-4 py-1.5 text-xs font-semibold uppercase tracking-widest text-violet-300 mb-4">
                Court Resources
            </span>
            <h1 class="text-3xl font-bold text-white sm:text-4xl">Resources &amp; Publications</h1>
            <p class="mt-3 text-slate-400 text-base max-w-2xl">
                Official publications, downloadable forms, court documents, and updates.
            </p>
        </div>

        {{-- Filter tabs --}}
        <div x-data="{ filter: 'all' }" class="space-y-8">
            <div class="flex flex-wrap gap-2">
                @foreach(['all' => 'All', 'post' => 'Posts', 'form' => 'Forms', 'document' => 'Documents', 'link' => 'Links'] as $val => $lbl)
                <button @click="filter = '{{ $val }}'"
                        :class="filter === '{{ $val }}' ? 'bg-blue-600 text-white' : 'bg-white/5 text-slate-400 hover:text-white hover:bg-white/10'"
                        class="rounded-xl px-4 py-2 text-sm font-semibold border border-white/8 transition">
                    {{ $lbl }}
                </button>
                @endforeach
            </div>

            @if($resources->isEmpty())
            <div class="rounded-3xl border border-dashed border-blue-700/40 bg-blue-950/30 p-20 text-center">
                <p class="text-slate-400 font-medium">No resources published yet.</p>
            </div>
            @else
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($resources as $res)
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
                <article x-show="filter === 'all' || filter === '{{ $res->type }}'"
                         class="group rounded-3xl border border-white/8 bg-white/4 overflow-hidden hover:border-white/15 hover:bg-white/6 transition flex flex-col">

                    @if($res->cover_image)
                    <div class="h-44 overflow-hidden">
                        <img src="{{ asset('storage/' . $res->cover_image) }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-300" alt="{{ $res->title }}">
                    </div>
                    @endif

                    <div class="p-6 flex flex-col flex-1">
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

                        <h2 class="text-base font-semibold text-white leading-snug mb-2">{{ $res->title }}</h2>

                        @if($res->description)
                        <p class="text-sm text-slate-400 leading-relaxed line-clamp-4 flex-1 mb-4">{{ $res->description }}</p>
                        @else
                        <div class="flex-1"></div>
                        @endif

                        <div class="pt-3 border-t border-white/5 flex items-center justify-between gap-2">
                            @if($res->published_at)
                            <span class="text-xs text-slate-500">{{ \App\Support\EthiopianDate::smartFormat($res->published_at, false, '') }}</span>
                            @else
                            <span></span>
                            @endif

                            <div class="flex gap-2">
                                @if($res->isDownloadable() && $res->file_path)
                                <a href="{{ asset('storage/' . $res->file_path) }}" target="_blank" download
                                   class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-blue-500 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    Download
                                </a>
                                @endif
                                @if($res->external_url)
                                <a href="{{ $res->external_url }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1.5 rounded-xl border border-white/15 bg-white/5 px-3 py-1.5 text-xs font-semibold text-slate-300 hover:text-white hover:bg-white/10 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    Visit
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
                @endforeach
            </div>
            @endif
        </div>
    </main>

    <footer class="mt-20 border-t border-blue-900/60 bg-blue-950/60 py-8">
        <div class="mx-auto max-w-[96rem] px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-600">
            <p>© {{ date('Y') }} {{ config('app.name', 'Court MS') }}. All rights reserved.</p>
            <div class="flex items-center gap-3">
                <a href="{{ route('language.switch', ['locale' => 'en', 'return' => url()->current()]) }}"
                   class="rounded-full px-3 py-1 text-xs font-semibold transition {{ app()->getLocale() === 'en' ? 'bg-orange-500 text-white' : 'text-slate-400 hover:text-white' }}">EN</a>
                <a href="{{ route('language.switch', ['locale' => 'am', 'return' => url()->current()]) }}"
                   class="rounded-full px-3 py-1 text-xs font-semibold transition {{ app()->getLocale() === 'am' ? 'bg-orange-500 text-white' : 'text-slate-400 hover:text-white' }}">AM</a>
            </div>
        </div>
    </footer>

</body>
</html>
