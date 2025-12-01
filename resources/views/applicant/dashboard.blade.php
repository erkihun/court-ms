<x-applicant-layout title="{{ __('dashboard.my_dashboard') }}">
    @php
    $total = $total ?? 0;
    $pending = $pending ?? 0;
    $active = $active ?? 0;
    $closed = $closed ?? 0;
    $recent = $recent ?? collect();
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Stats Cards --}}
        <div class="lg:col-span-3 grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- Total --}}
            <div class="p-4 rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="text-[16px] uppercase tracking-wide text-slate-500">
                        {{ __('dashboard.total_cases') }}
                    </div>
                    <div class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-blue-50">
                        {{-- Archive / stack icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-10 w-10 text-blue-600"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                d="M4 7h16M5 7v11a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7M9 11h6" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2 text-3xl font-semibold text-slate-900">
                    {{ $total }}
                </div>
            </div>

            {{-- Pending --}}
            <div class="p-4 rounded-xl border border-orange-200 bg-orange-50 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="text-[16px] uppercase tracking-wide text-orange-700">
                        {{ __('dashboard.pending') }}
                    </div>
                    <div class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-white/80">
                        {{-- Clock icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-10 w-10 text-orange-600"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                d="M12 6v6l3 3m4-3a7 7 0 1 1-14 0 7 7 0 0 1 14 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2 text-3xl font-semibold text-orange-800">
                    {{ $pending }}
                </div>
            </div>

            {{-- Active --}}
            <div class="p-4 rounded-xl border border-blue-200 bg-blue-50 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="text-[16px] uppercase tracking-wide text-blue-700">
                        {{ __('dashboard.active') }}
                    </div>
                    <div class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-white/80">
                        {{-- Play / progress icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-10 w-10 text-blue-600"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                d="M5 5v14l6-4 8 4V5l-8 4-6-4z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2 text-3xl font-semibold text-blue-800">
                    {{ $active }}
                </div>
            </div>

            {{-- Closed --}}
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="text-[16px] uppercase tracking-wide text-slate-700">
                        {{ __('dashboard.closed') }}
                    </div>
                    <div class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-white/80">
                        {{-- Check / shield icon --}}
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-10 w-10 text-slate-600"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                d="M12 4.5l6 2.25v5.25c0 3.5-2.5 6.7-6 7.5-3.5-.8-6-4-6-7.5V6.75L12 4.5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                d="M9.5 12.5l1.8 1.8 3.2-3.3" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2 text-3xl font-semibold text-slate-800">
                    {{ $closed }}
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="p-5 rounded-xl border border-slate-200 bg-white shadow-sm">
            <h3 class="text-sm font-semibold text-slate-800 mb-3">
                {{ __('dashboard.quick_actions') }}
            </h3>
            <div class="flex flex-col gap-2 text-md">
                <a href="{{ route('applicant.cases.create') }}"
                    class="px-4 py-2.5 rounded-lg bg-orange-500 text-white font-medium text-center
                          hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-1">
                    {{ __('dashboard.submit_new_case') }}
                </a>
                <a href="{{ route('applicant.cases.index') }}"
                    class="px-4 py-2.5 rounded-lg bg-blue-600 text-white font-medium text-center
                          hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1">
                    {{ __('dashboard.my_cases') }}
                </a>
                <a href="{{ route('applicant.profile.edit') }}"
                    class="px-4 py-2.5 rounded-lg bg-slate-100 text-slate-700 font-medium text-center
                          hover:bg-slate-200 focus:outline-none focus:ring-1 focus:ring-slate-400">
                    {{ __('dashboard.edit_profile') }}
                </a>
            </div>
        </div>

        {{-- Recent Cases --}}
        <div class="lg:col-span-2 p-5 rounded-xl border border-slate-200 bg-white shadow-sm">
            <h3 class="text-sm font-semibold text-slate-800 mb-3">
                {{ __('dashboard.recent_cases') }}
            </h3>

            @if($recent->isEmpty())
            <div class="text-slate-500 text-sm">
                {{ __('dashboard.no_cases_yet') }}
            </div>
            @else
            <div class="overflow-x-auto rounded-lg border border-slate-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="p-3 text-left text-[12px] font-semibold uppercase tracking-wide">
                                {{ __('dashboard.case_number') }}
                            </th>
                            <th class="p-3 text-left text-[12px] font-semibold uppercase tracking-wide">
                                {{ __('dashboard.case_title') }}
                            </th>
                            <th class="p-3 text-left text-[12px] font-semibold uppercase tracking-wide">
                                {{ __('dashboard.type') }}
                            </th>
                            <th class="p-3 text-left text-[12px] font-semibold uppercase tracking-wide">
                                {{ __('dashboard.status') }}
                            </th>
                            <th class="p-3 text-left text-[12px] font-semibold uppercase tracking-wide">
                                {{ __('dashboard.created') }}
                            </th>
                            <th class="p-3 text-left text-[12px] font-semibold uppercase tracking-wide">
                                {{ __('dashboard.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recent as $c)
                        <tr class="hover:bg-slate-50">
                            <td class="p-3 font-mono text-xs text-slate-800">
                                {{ $c->case_number }}
                            </td>
                            <td class="p-3 text-slate-900">
                                <div class="line-clamp-2">
                                    {{ $c->title }}
                                </div>
                            </td>
                            <td class="p-3 text-slate-700">
                                {{ $c->case_type }}
                            </td>
                            <td class="p-3 capitalize">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium border
                                            @if($c->status==='pending')
                                                bg-orange-50 text-orange-700 border-orange-200
                                            @elseif($c->status==='active')
                                                bg-blue-50 text-blue-700 border-blue-200
                                            @elseif(in_array($c->status,['closed','dismissed']))
                                                bg-slate-100 text-slate-700 border-slate-200
                                            @else
                                                bg-slate-50 text-slate-700 border-slate-200
                                            @endif">
                                    {{ __('cases.status.' . $c->status) }}
                                </span>
                            </td>
                            <td class="p-3 text-slate-700 whitespace-nowrap">
                                {{ \Illuminate\Support\Carbon::parse($c->created_at)->format('M d, Y') }}
                            </td>
                            <td class="p-3">
                                <a href="{{ route('applicant.cases.show', $c->id) }}"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md bg-blue-600 text-white text-xs font-medium
                                                  hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1">
                                    {{ __('dashboard.view') }}
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</x-applicant-layout>