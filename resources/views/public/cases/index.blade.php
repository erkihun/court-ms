<x-applicant-layout :title="__('app.Cases')">
    @php
        $hasQuery = filled($q ?? '');
    @endphp

    <section class="space-y-6">
        <div class="flex flex-col gap-4 rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm lg:flex-row lg:items-end lg:justify-between lg:p-8">
            <div class="max-w-2xl">
                <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-blue-700">
                    {{ __('home.public_cases.public_records') }}
                </span>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">{{ __('home.public_cases.title') }}</h1>
                <p class="mt-3 text-sm leading-6 text-slate-600 sm:text-base">
                    {{ __('home.public_cases.description') }}
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:min-w-[280px] lg:max-w-sm">
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ __('home.public_cases.results_shown') }}</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">{{ $cases->count() }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ __('home.public_cases.on_this_page') }}</p>
                </div>
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">{{ __('home.public_cases.search_state') }}</p>
                    <p class="mt-2 text-sm font-semibold text-slate-900">{{ $hasQuery ? __('home.public_cases.filtered_results') : __('home.public_cases.all_public_cases') }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $hasQuery ? __('home.public_cases.keyword_applied') : __('home.public_cases.no_filters_applied') }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-[2rem] border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <form method="GET" class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_auto_auto] lg:items-end">
                <label class="block">
                    <span class="mb-2 block text-sm font-medium text-slate-700">{{ __('home.public_cases.search_public_cases') }}</span>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.473 9.765l2.63 2.63a.75.75 0 1 0 1.06-1.06l-2.63-2.63A5.5 5.5 0 0 0 9 3.5ZM5 9a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        <input
                            name="q"
                            value="{{ $q ?? '' }}"
                            placeholder="{{ __('home.public_portal.search_placeholder') }}"
                            class="w-full rounded-2xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    </div>
                </label>

                <button class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700">
                    {{ __('home.public_cases.filter_results') }}
                </button>

                @if($hasQuery)
                    <a href="{{ route('public.cases') }}"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        {{ __('app.Reset') }}
                    </a>
                @endif
            </form>
        </div>

        <div class="hidden overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm lg:block">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-slate-600">
                        <th class="px-5 py-4 text-left font-semibold">{{ __('app.case_number') }}</th>
                        <th class="px-5 py-4 text-left font-semibold">{{ __('app.Title') }}</th>
                        <th class="px-5 py-4 text-left font-semibold">{{ __('app.Type') }}</th>
                        <th class="px-5 py-4 text-left font-semibold">{{ __('home.public_cases.court') }}</th>
                        <th class="px-5 py-4 text-left font-semibold">{{ __('app.Status') }}</th>
                        <th class="px-5 py-4 text-left font-semibold">{{ __('home.public_cases.filed') }}</th>
                        <th class="px-5 py-4 text-left font-semibold">{{ __('app.Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($cases as $c)
                        <tr class="transition hover:bg-slate-50/90">
                            <td class="px-5 py-4 align-top font-mono text-sm font-semibold text-slate-800">{{ $c->case_number }}</td>
                            <td class="px-5 py-4 align-top">
                                <div class="font-semibold text-slate-900">{{ $c->title }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ __('home.public_cases.public_summary_available') }}</div>
                            </td>
                            <td class="px-5 py-4 align-top text-slate-600">{{ $c->case_type }}</td>
                            <td class="px-5 py-4 align-top text-slate-600">{{ $c->court_name }}</td>
                            <td class="px-5 py-4 align-top">
                                <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold capitalize text-emerald-700 ring-1 ring-emerald-100">
                                    {{ $c->status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 align-top text-slate-600">{{ \App\Support\EthiopianDate::format($c->filing_date) }}</td>
                            <td class="px-5 py-4 align-top">
                                <a href="{{ route('public.cases.show', $c->case_number) }}"
                                    class="inline-flex items-center rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white transition hover:bg-slate-700">
                                    {{ __('home.public_cases.view_case') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-14 text-center">
                                <div class="mx-auto max-w-md">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                        </svg>
                                    </div>
                                    <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ __('home.public_cases.no_public_cases_found') }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-slate-500">{{ __('home.public_cases.no_public_cases_text') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="grid gap-4 lg:hidden">
            @forelse($cases as $c)
                <article class="rounded-[1.75rem] border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-mono text-xs font-semibold uppercase tracking-[0.18em] text-blue-700">{{ $c->case_number }}</p>
                            <h2 class="mt-2 text-lg font-semibold text-slate-900">{{ $c->title }}</h2>
                        </div>
                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold capitalize text-emerald-700 ring-1 ring-emerald-100">
                            {{ $c->status }}
                        </span>
                    </div>

                    <dl class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ __('app.Type') }}</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $c->case_type }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ __('home.public_cases.court') }}</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $c->court_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ __('home.public_cases.filed') }}</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ \App\Support\EthiopianDate::format($c->filing_date) }}</dd>
                        </div>
                    </dl>

                    <a href="{{ route('public.cases.show', $c->case_number) }}"
                        class="mt-5 inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-700">
                        {{ __('home.public_cases.view_case') }}
                    </a>
                </article>
            @empty
                <div class="rounded-[1.75rem] border border-dashed border-slate-300 bg-white p-8 text-center shadow-sm">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ __('home.public_cases.no_public_cases_found') }}</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">{{ __('home.public_cases.no_public_cases_text_mobile') }}</p>
                </div>
            @endforelse
        </div>

        <div class="pt-2">{{ $cases->links() }}</div>
    </section>
</x-applicant-layout>
