<x-applicant-layout :title="__('app.home')">
    <section class="relative overflow-hidden rounded-[2rem] bg-slate-950 text-white shadow-2xl ring-1 ring-slate-900/10">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(59,130,246,0.35),_transparent_32%),radial-gradient(circle_at_left,_rgba(14,165,233,0.18),_transparent_28%)]"></div>
        <div class="relative grid gap-10 px-6 py-10 sm:px-10 lg:grid-cols-[minmax(0,1.2fr)_minmax(320px,420px)] lg:px-12 lg:py-14">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-1.5 text-xs font-semibold uppercase tracking-[0.24em] text-blue-100 backdrop-blur">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    {{ __('home.public_portal.badge') }}
                </div>

                <h1 class="mt-6 text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                    {{ __('home.public_portal.title') }}
                </h1>

                <p class="mt-5 max-w-2xl text-base leading-7 text-slate-200 sm:text-lg">
                    {{ __('home.public_portal.description') }}
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('public.cases') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-white px-5 py-3 text-sm font-semibold text-slate-900 shadow-lg shadow-blue-950/20 transition hover:-translate-y-0.5 hover:bg-blue-50">
                        {{ __('home.public_portal.browse_public_cases') }}
                    </a>
                    <a href="{{ route('applicant.login') }}"
                        class="inline-flex items-center justify-center rounded-xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/15">
                        {{ __('home.public_portal.applicant_sign_in') }}
                    </a>
                </div>

                <dl class="mt-10 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                        <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-100">{{ __('home.public_portal.case_lookup') }}</dt>
                        <dd class="mt-2 text-sm text-slate-200">{{ __('home.public_portal.case_lookup_text') }}</dd>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                        <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-100">{{ __('home.public_portal.status_visibility') }}</dt>
                        <dd class="mt-2 text-sm text-slate-200">{{ __('home.public_portal.status_visibility_text') }}</dd>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                        <dt class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-100">{{ __('home.public_portal.responsive_design') }}</dt>
                        <dd class="mt-2 text-sm text-slate-200">{{ __('home.public_portal.responsive_design_text') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="relative">
                <div class="rounded-[1.75rem] border border-white/10 bg-white/95 p-5 text-slate-900 shadow-2xl shadow-blue-950/20 ring-1 ring-white/30 sm:p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-700">{{ __('home.public_portal.quick_search') }}</p>
                            <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-950">{{ __('home.public_portal.find_public_case') }}</h2>
                        </div>
                        <div class="rounded-2xl bg-blue-50 px-3 py-2 text-right">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-blue-700">{{ __('home.public_portal.live_portal') }}</p>
                            <p class="mt-1 text-sm font-medium text-slate-700">{{ __('home.public_portal.search_ready') }}</p>
                        </div>
                    </div>

                    <p class="mt-4 text-sm leading-6 text-slate-600">
                        {{ __('home.public_portal.search_hint') }}
                    </p>

                    <form method="GET" action="{{ route('public.cases') }}" class="mt-6 space-y-4">
                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-slate-700">{{ __('home.public_portal.search_public_records') }}</span>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.473 9.765l2.63 2.63a.75.75 0 1 0 1.06-1.06l-2.63-2.63A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <input
                                    name="q"
                                    placeholder="{{ __('home.public_portal.search_placeholder') }}"
                                    class="w-full rounded-2xl border border-slate-200 bg-white py-3.5 pl-11 pr-4 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                            </div>
                        </label>

                        <button
                            class="inline-flex w-full items-center justify-center rounded-2xl bg-blue-600 px-5 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100">
                            {{ __('home.public_portal.search_public_cases') }}
                        </button>
                    </form>

                    <div class="mt-6 rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ __('home.public_portal.helpful_tips') }}</p>
                        <ul class="mt-3 space-y-2 text-sm text-slate-600">
                            <li class="flex gap-2"><span class="mt-1 h-2 w-2 rounded-full bg-blue-500"></span><span>{{ __('home.public_portal.tip_1') }}</span></li>
                            <li class="flex gap-2"><span class="mt-1 h-2 w-2 rounded-full bg-emerald-500"></span><span>{{ __('home.public_portal.tip_2') }}</span></li>
                            <li class="flex gap-2"><span class="mt-1 h-2 w-2 rounded-full bg-amber-500"></span><span>{{ __('home.public_portal.tip_3') }}</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mt-8 grid gap-4 lg:grid-cols-3">
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ __('home.public_portal.card_1_title') }}</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600">{{ __('home.public_portal.card_1_text') }}</p>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ __('home.public_portal.card_2_title') }}</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600">{{ __('home.public_portal.card_2_text') }}</p>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2.25M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ __('home.public_portal.card_3_title') }}</h3>
            <p class="mt-2 text-sm leading-6 text-slate-600">{{ __('home.public_portal.card_3_text') }}</p>
        </article>
    </section>
</x-applicant-layout>
