<x-applicant-layout title="{{ __('dashboard.my_dashboard') }}">
    @php
    $total = $total ?? 0;
    $pending = $pending ?? 0;
    $active = $active ?? 0;
    $closed = $closed ?? 0;
    $recent = $recent ?? collect();
    $lettersCount = $lettersCount ?? 0;
    $responsesCount = $responsesCount ?? 0;
    $decisionsCount = $decisionsCount ?? 0;
    $caseLetters = $caseLetters ?? collect();
    $caseResponses = $caseResponses ?? collect();
    $caseDecisions = $caseDecisions ?? collect();
    @endphp

    <div class="space-y-6">
        {{-- Welcome Header --}}
        <div class="rounded-2xl p-6 text-white shadow-xl bg-gradient-to-r from-[#0d3b8f] to-[#1b63c3]">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    @php
                        $applicantUser = auth('applicant')->user();
                        $applicantName = $applicantUser?->full_name ?: $applicantUser?->name ?: $applicantUser?->email ?: __('applicant.user');
                    @endphp
                    <h4 class="text-2xl  font-bold mb-2">{{ __('dashboard.welcome_back') }}, {{ $applicantName }}</h4>
                    
                </div>
                <div class=" backdrop-blur-sm ">
                    <div >{{ __('dashboard.today') }}</div>
                    <div>{{ now()->format('F j, Y') }}</div>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            {{-- Total Cases --}}
            <div class="group relative p-4 rounded-2xl border border-[#0c1b4a] bg-white shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                <div class="absolute top-0 right-0 w-16 h-16 bg-[#0c1b4a]/10 rounded-full -translate-y-4 translate-x-4"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-xs font-semibold uppercase tracking-wider text-[#0c1b4a]">
                            {{ __('dashboard.total_cases') }}
                        </div>
                    <div class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-white shadow-md transition-transform duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-slate-900 mb-2">
                        {{ $total }}
                    </div>
                   
                </div>
            </div>

            {{-- Pending Cases --}}
            <div class="group relative p-4 rounded-2xl border border-orange-100 bg-white shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                <div class="absolute top-0 right-0 w-16 h-16 bg-orange-200/10 rounded-full -translate-y-4 translate-x-4"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-xs font-semibold uppercase tracking-wider text-orange-600">
                            {{ __('dashboard.pending') }}
                        </div>
                        <div class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-white shadow-md group-hover:scale-110 transition-transform duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-orange-800 mb-2">
                        {{ $pending }}
                    </div>
                    <div class="w-full bg-orange-100 rounded-full h-1.5 mb-2">
                        <div class="bg-orange-500 h-1.5 rounded-full" style="width: {{ $total > 0 ? ($pending/$total)*100 : 0 }}%"></div>
                    </div>
                 
                </div>
            </div>

            {{-- Active Cases --}}
            <div class="group relative p-4 rounded-2xl border border-emerald-100 bg-white shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                <div class="absolute top-0 right-0 w-16 h-16 bg-emerald-200/10 rounded-full -translate-y-4 translate-x-4"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-xs font-semibold uppercase tracking-wider text-emerald-600">
                            {{ __('dashboard.active') }}
                        </div>
                        <div class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-white shadow-md group-hover:scale-110 transition-transform duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-emerald-800 mb-2">
                        {{ $active }}
                    </div>
                    <div class="w-full bg-emerald-100 rounded-full h-1.5 mb-2">
                        <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $total > 0 ? ($active/$total)*100 : 0 }}%"></div>
                    </div>
                 
                </div>
            </div>

            {{-- Closed Cases --}}
            <div class="group relative p-4 rounded-2xl border border-slate-100 bg-white shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                <div class="absolute top-0 right-0 w-16 h-16 bg-slate-200/10 rounded-full -translate-y-4 translate-x-4"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-xs font-semibold uppercase tracking-wider text-slate-600">
                            {{ __('dashboard.closed') }}
                        </div>
                        <div class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-white shadow-md group-hover:scale-110 transition-transform duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-slate-800 mb-2">
                        {{ $closed }}
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-1.5 mb-2">
                        <div class="bg-slate-500 h-1.5 rounded-full" style="width: {{ $total > 0 ? ($closed/$total)*100 : 0 }}%"></div>
                    </div>
                 
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[3fr_9fr] gap-6">
            {{-- Quick Actions --}}
            <div class="p-6 rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-md transition-shadow duration-300">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900">
                        {{ __('dashboard.quick_actions') }}
                    </h3>
                </div>
                
                <div class="space-y-3">
                    <a href="{{ route('applicant.cases.create') }}"
                       class="group flex items-center justify-start gap-3 px-4 py-3.5 rounded-xl bg-gradient-to-r from-[#0d3b8f] to-[#1b63c3] text-white font-medium
                              hover:from-[#0b306b] hover:to-[#1550a3] focus:outline-none focus:ring-2 focus:ring-[#0d3b8f] focus:ring-offset-2
                              transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('dashboard.submit_new_case') }}</span>
                    </a>
                    
                    <button type="button" data-panel-target="recent"
                       class="w-full group flex items-center justify-start gap-3 px-4 py-3.5 rounded-xl bg-gradient-to-r from-[#0d3b8f] to-[#1b63c3] text-white font-medium active
                              hover:from-[#0b306b] hover:to-[#1550a3] focus:outline-none focus:ring-2 focus:ring-[#0d3b8f] focus:ring-offset-2
                              transition-all duration-200 hover:shadow-lg panel-toggle active">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h6a2 2 0 012 2v2h2a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2h2V7a2 2 0 012-2z"/>
                        </svg>
                        <span>{{ __('dashboard.recent_cases') }}</span>
                    </button>
                    
                    <button type="button" data-panel-target="letters"
                       class="w-full group flex items-center justify-start gap-3 px-4 py-3.5 rounded-xl bg-gradient-to-r from-[#0d3b8f] to-[#1b63c3] text-white font-medium
                              hover:from-[#0b306b] hover:to-[#1550a3] focus:outline-none focus:ring-2 focus:ring-[#0d3b8f] focus:ring-offset-2
                              transition-all duration-200 hover:shadow-lg panel-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 6 9-6m-18 0l9-6 9 6m-18 0v8a2 2 0 002 2h14a2 2 0 002-2V8"/>
                        </svg>
                        <div class="flex items-center justify-between w-full">
                            <span>{{ __('dashboard.letters') }}</span>
                            <span class="text-[12px] px-2 py-1 rounded-full bg-white/20 border border-white/30">{{ $lettersCount }}</span>
                        </div>
                    </button>
                    
                    <button type="button" data-panel-target="responses"
                       class="w-full group flex items-center justify-start gap-3 px-4 py-3.5 rounded-xl bg-gradient-to-r from-[#0d3b8f] to-[#1b63c3] text-white font-medium
                              hover:from-[#0b306b] hover:to-[#1550a3] focus:outline-none focus:ring-2 focus:ring-[#0d3b8f] focus:ring-offset-2
                              transition-all duration-200 hover:shadow-lg panel-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h5m-5 4h4M5 20l2.586-2.586A2 2 0 018.828 17H19a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v13z"/>
                        </svg>
                        <div class="flex items-center justify-between w-full">
                            <span>{{ __('dashboard.responses') }}</span>
                            <span class="text-[12px] px-2 py-1 rounded-full bg-white/20 border border-white/30">{{ $responsesCount }}</span>
                        </div>
                    </button>
                    
                    <button type="button" data-panel-target="decisions"
                       class="w-full group flex items-center justify-start gap-3 px-4 py-3.5 rounded-xl bg-gradient-to-r from-[#0d3b8f] to-[#1b63c3] text-white font-medium
                              hover:from-[#0b306b] hover:to-[#1550a3] focus:outline-none focus:ring-2 focus:ring-[#0d3b8f] focus:ring-offset-2
                              transition-all duration-200 hover:shadow-lg panel-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7a2 2 0 10-4 0 2 2 0 004 0zm16 0a2 2 0 10-4 0 2 2 0 004 0zM2 7h20M6 7l4 10m-8 0h8m4-10l4 10m-8 0h8M12 4v2"/>
                        </svg>
                        <div class="flex items-center justify-between w-full">
                            <span>{{ __('dashboard.decisions') }}</span>
                            <span class="text-[12px] px-2 py-1 rounded-full bg-white/20 border border-white/30">{{ $decisionsCount }}</span>
                        </div>
                    </button>

                </div>
            </div>

        {{-- Panel Area --}}
            <div class="p-6 rounded-2xl border border-slate-200 bg-white shadow-sm hover:shadow-md transition-shadow duration-300">
                {{-- Recent Cases Panel --}}
                <div data-panel="recent">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-slate-100 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">
                                {{ __('dashboard.recent_cases') }}
                            </h3>
                             
                        </div>
                    </div>
                    <a href="{{ route('applicant.cases.index') }}" 
                       class=" font-medium text-blue-600 hover:text-blue-800 flex items-center gap-1">
                        {{ __('dashboard.view_all') }}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                @if($recent->isEmpty())
                    <div class="text-center py-12 border-2 border-dashed border-slate-200 rounded-2xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h4 class="mt-4 text-lg font-medium text-slate-700">{{ __('dashboard.no_cases_yet') }}</h4>
                        <p class="mt-2  text-slate-500 max-w-sm mx-auto">{{ __('dashboard.no_cases_description') }}</p>
                        <a href="{{ route('applicant.cases.create') }}" 
                           class="mt-6 inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white font-medium rounded-lg hover:from-orange-600 hover:to-orange-700 transition-all duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ __('dashboard.submit_first_case') }}
                        </a>
                    </div>
                @else
                    {{-- Desktop Table --}}
                    <div class="hidden lg:block overflow-hidden rounded-xl border border-slate-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-gradient-to-r from-slate-50 to-slate-100">
                                    <tr>
                                        <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                            <div class="flex items-center gap-2">
                                                <span>{{ __('dashboard.case_number') }}</span>
                                            </div>
                                        </th>
                                        <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                            {{ __('dashboard.type') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                            {{ __('dashboard.status') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                            {{ __('dashboard.created') }}
                                        </th>
                                        <th scope="col" class="px-4 py-3.5 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                            {{ __('dashboard.actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach($recent->take(3) as $c)
                                    <tr class="hover:bg-slate-50/80 transition-colors duration-150 group">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="font-mono  font-semibold text-blue-700">
                                                {{ $c->case_number }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                                </svg>
                                                {{ $c->case_type }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium border
                                                        @if($c->status==='pending')
                                                            bg-orange-50 text-orange-700 border-orange-200
                                                        @elseif($c->status==='active')
                                                            bg-emerald-50 text-emerald-700 border-emerald-200
                                                        @elseif(in_array($c->status,['closed','dismissed','resolved']))
                                                            bg-slate-100 text-slate-700 border-slate-200
                                                        @else
                                                            bg-slate-50 text-slate-700 border-slate-200
                                                        @endif">
                                                @if($c->status==='pending')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                @elseif($c->status==='active')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                                </svg>
                                                @else
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                @endif
                                                {{ __('cases.status.' . $c->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class=" text-slate-600">
                                                {{ \App\Support\EthiopianDate::format($c->created_at) }}
                                            </div>
                                            <div class="text-xs text-slate-400">
                                                {{ optional($c->created_at)->diffForHumans() }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-right  font-medium">
                                            <a href="{{ route('applicant.cases.show', $c->id) }}"
                                               class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-blue-50 text-blue-700  font-medium
                                                      hover:bg-blue-100 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1
                                                      transition-all duration-200 group-hover:scale-105">
                                                {{ __('dashboard.view') }}
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Mobile Cards View --}}
                    <div class="lg:hidden space-y-4">
                        @foreach($recent->take(3) as $c)
                        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm hover:shadow-md transition-shadow duration-300">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <span class="font-mono  font-semibold text-blue-700">{{ $c->case_number }}</span>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    @if($c->status==='pending') bg-orange-50 text-orange-700 
                                                    @elseif($c->status==='active') bg-emerald-50 text-emerald-700 
                                                    @else bg-slate-100 text-slate-700 @endif">
                                            {{ __('cases.status.' . $c->status) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="text-xs text-slate-500">{{ optional($c->created_at)->diffForHumans() }}</div>
                            </div>
                            <h4 class="font-medium text-slate-900 mb-2">{{ $c->title }}</h4>
                            <div class="flex items-center justify-between mt-4">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs bg-blue-50 text-blue-700">
                                    {{ $c->case_type }}
                                </span>
                                <a href="{{ route('applicant.cases.show', $c->id) }}"
                                   class=" font-medium text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                    {{ __('dashboard.view') }}
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Letters Panel --}}
            <div class="hidden" data-panel="letters">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l9 6 9-6M4 6h16a1 1 0 011 1v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a1 1 0 011-1z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">{{ __('dashboard.letters') }}</h3>
                            <p class=" text-slate-500">{{ __('dashboard.letters_description') }}</p>
                        </div>
                    </div>
                    <button type="button" data-panel-target="recent" class=" font-medium text-blue-600 hover:text-blue-800">
                        {{ __('dashboard.recent_cases') }}
                    </button>
                </div>
                <div class="space-y-3">
                    @forelse($caseLetters as $letter)
                    <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/60">
                        <div class="flex items-center justify-between gap-2">
                            <div class="font-semibold text-slate-900 truncate">{{ $letter->subject ?? $letter->template_title ?? __('dashboard.letters') }}</div>
                            <div class="text-xs text-slate-500 whitespace-nowrap">{{ \App\Support\EthiopianDate::format($letter->created_at) }}</div>
                        </div>
                        <div class="text-xs text-slate-500 mt-1 flex flex-wrap gap-3">
                            <span>{{ __('dashboard.case_number') }}: {{ $letter->case_number ?? '--' }}</span>
                            @if(!empty($letter->reference_number))
                            <span>Ref: {{ $letter->reference_number }}</span>
                            @endif
                        </div>
                        <div class="mt-2">
                            <a href="{{ route('letters.case-preview', $letter->id) }}"
                               class="inline-flex items-center gap-1  font-semibold text-blue-700 hover:text-blue-900">
                                {{ __('dashboard.view') }}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7m7-7H3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="p-4  text-slate-500 border border-dashed border-slate-200 rounded-xl">
                        {{ __('dashboard.no_letters') }}
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Responses Panel --}}
            <div class="hidden" data-panel="responses">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-emerald-100 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h5m-5 4h4M5 20l2.586-2.586A2 2 0 018.828 17H19a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v13z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">{{ __('dashboard.responses') }}</h3>
                            <p class=" text-slate-500">{{ __('dashboard.responses_description') }}</p>
                        </div>
                    </div>
                    <button type="button" data-panel-target="recent" class=" font-medium text-blue-600 hover:text-blue-800">
                        {{ __('dashboard.recent_cases') }}
                    </button>
                </div>
                <div class="space-y-3">
                    @forelse($caseResponses as $response)
                    <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/60">
                        <div class="flex items-center justify-between gap-2">
                            <div class="font-semibold text-slate-900 truncate">{{ $response->title }}</div>
                            <div class="text-xs text-slate-500 whitespace-nowrap">
                                {{ $response->created_at ? \Illuminate\Support\Carbon::parse($response->created_at)->diffForHumans() : '' }}
                            </div>
                        </div>
                        <div class="text-xs text-slate-500 mt-1">
                            {{ __('dashboard.case_number') }}: {{ $response->case_number ?? '--' }}
                        </div>
                        <div class="mt-3 flex justify-end">
                            @if(!empty($response->case_id))
                                <a
                                    href="{{ route('applicant.cases.respondentResponses.show', [$response->case_id, $response->id]) }}"
                                    class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-white px-3 py-1.5  font-medium text-emerald-700 hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1 transition"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                    </svg>
                                    {{ __('dashboard.view') }}
                                </a>
                            @else
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5  font-medium text-slate-400 cursor-not-allowed"
                                    disabled
                                >
                                    {{ __('dashboard.view') }}
                                </button>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="p-4  text-slate-500 border border-dashed border-slate-200 rounded-xl">
                        {{ __('dashboard.no_responses') }}
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Decisions Panel --}}
            <div class="hidden" data-panel="decisions">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-slate-100 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7a2 2 0 10-4 0 2 2 0 004 0zm16 0a2 2 0 10-4 0 2 2 0 004 0zM2 7h20M6 7l4 10m-8 0h8m4-10l4 10m-8 0h8M12 4v2"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">{{ __('dashboard.decisions') }}</h3>
                            <p class=" text-slate-500">{{ __('dashboard.decisions_description') }}</p>
                        </div>
                    </div>
                    <button type="button" data-panel-target="recent" class=" font-medium text-blue-600 hover:text-blue-800">
                        {{ __('dashboard.recent_cases') }}
                    </button>
                </div>
                <div class="space-y-3">
                    @forelse($caseDecisions as $decision)
                    <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/60">
                        <div class="flex items-center justify-between gap-2">
                            <div class="font-semibold text-slate-900 truncate">{{ $decision->name ?? __('dashboard.decisions') }}</div>
                            <div class="text-xs text-slate-500 whitespace-nowrap">
                                @php
                                    $decisionDate = $decision->decision_date ?: $decision->created_at;
                                @endphp
                                {{ $decisionDate ? \App\Support\EthiopianDate::format($decisionDate) : '' }}
                            </div>
                        </div>
                        <div class="text-xs text-slate-500 mt-1 flex flex-wrap gap-3">
                                <span>{{ __('dashboard.case_number') }}: {{ $decision->case_number ?? '--' }}</span>
                            @if(!empty($decision->status))
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 font-semibold border border-slate-200">
                                {{ ucfirst($decision->status) }}
                            </span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="p-4  text-slate-500 border border-dashed border-slate-200 rounded-xl">
                        {{ __('dashboard.no_decisions') }}
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
     
        <style>
            .panel-toggle.active {
                background: linear-gradient(135deg, #f97316, #ffb347);
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const toggles = document.querySelectorAll('.panel-toggle');
                toggles.forEach(btn => {
                    btn.addEventListener('click', () => {
                        toggles.forEach(b => b.classList.remove('active'));
                        btn.classList.add('active');
                    });
                });
            });
        </script>
    </div>
   {{-- Quick Tips Section (Optional) --}}
        <div class="p-6 rounded-2xl border border-blue-100 bg-gradient-to-r from-blue-50 to-indigo-50">
            <div class="flex items-center gap-3 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-lg font-semibold text-slate-900">{{ __('dashboard.quick_tips') }}</h3>
            </div>
            <div class="space-y-4">
                <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4">
                    <div class=" font-medium text-slate-900 mb-2">{{ __('dashboard.tip1_title') }}</div>
                    <p class=" text-slate-600">{{ __('dashboard.tip1_description') }}</p>
                </div>
                <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4">
                    <div class=" font-medium text-slate-900 mb-2">{{ __('dashboard.tip2_title') }}</div>
                    <p class=" text-slate-600">{{ __('dashboard.tip2_description') }}</p>
                </div>
                <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4">
                    <div class=" font-medium text-slate-900 mb-2">{{ __('dashboard.tip3_title') }}</div>
                    <p class=" text-slate-600">{{ __('dashboard.tip3_description') }}</p>
                </div>
            </div>
        </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const buttons = document.querySelectorAll('[data-panel-target]');
            const panels = document.querySelectorAll('[data-panel]');

            const setPanel = (name) => {
                panels.forEach(panel => {
                    panel.classList.toggle('hidden', panel.dataset.panel !== name);
                });
                buttons.forEach(btn => {
                    const isActive = btn.dataset.panelTarget === name;
                    btn.classList.toggle('ring-2', isActive);
                    btn.classList.toggle('ring-offset-2', isActive);
                    btn.classList.toggle('ring-blue-400', isActive);
                    btn.classList.toggle('shadow-lg', isActive);
                });
            };

            buttons.forEach(btn => {
                btn.addEventListener('click', () => setPanel(btn.dataset.panelTarget));
            });

            setPanel('recent');
        });
    </script>
</x-applicant-layout>
