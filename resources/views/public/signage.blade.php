<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0">
    <meta http-equiv="refresh" content="120">
    <title>{{ __('signage.title') }}</title>
    @vite('resources/css/app.css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Manrope', 'Inter', 'Segoe UI', sans-serif; 
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 50%, #e2e8f0 100%);
            overflow-x: hidden;
        }
        
        .animate-gradient {
            animation: gradient-shift 15s ease infinite;
            background-size: 400% 400%;
        }
        
        @keyframes gradient-shift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .scrollbar-thin {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f1f5f9;
        }
        
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 3px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
        
        .table-sticky thead th {
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .shadow-soft {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        }
        
        .border-gradient {
            border-image: linear-gradient(135deg, #6366f1, #8b5cf6) 1;
        }
    </style>
</head>
<body class="min-h-screen text-gray-900">
    <!-- Live Status Indicator -->
    <div class="fixed top-4 right-4 z-20 flex items-center gap-2 rounded-full bg-white px-4 py-2 shadow-lg ring-1 ring-slate-900/5">
        <div class="flex items-center gap-2">
            <div class="relative">
                <div class="h-3 w-3 rounded-full bg-emerald-500 animate-pulse"></div>
                <div class="absolute inset-0 h-3 w-3 rounded-full bg-emerald-500 animate-ping opacity-75"></div>
            </div>
            <span class="text-sm font-semibold text-slate-700">{{ __('signage.live_updates') }}</span>
        </div>
    </div>

    <!-- Main Container -->
    <div class="max-w-6xl mx-auto min-h-screen p-4 sm:p-6 lg:p-8">
        <div class="grid gap-6 xl:grid-cols-2 items-start">
            <!-- Left Column - Main Content -->
            <div class="flex flex-col gap-6">
                @php
                    $displayNow = $now;
                @endphp

                <!-- Header Card -->
                <div class="rounded-2xl bg-gradient-to-br from-indigo-900 via-indigo-800 to-blue-900 text-white shadow-2xl overflow-hidden border border-indigo-700/30">
                    <div class="relative px-6 py-6 md:px-8 md:py-8">
                        <!-- Animated background pattern -->
                        <div class="absolute inset-0 opacity-10">
                            <div class="absolute inset-0" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 20px 20px;"></div>
                        </div>
                        
                        <div class="relative z-10 flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                            <!-- Logo and Title -->
                            <div class="flex items-center gap-5">
                                <div class="h-20 w-20 rounded-2xl border-2 border-white/20 bg-white/10 p-3 shadow-xl backdrop-blur-sm flex items-center justify-center">
                                    @if($settings?->logo_path)
                                        <img class="h-full w-full object-contain drop-shadow-lg" src="{{ \Illuminate\Support\Facades\Storage::url($settings->logo_path) }}" alt="logo">
                                    @else
                                        <img class="h-full w-full object-contain drop-shadow-lg" src="{{ asset('favicon.ico') }}" alt="logo">
                                    @endif
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs uppercase tracking-[0.3em] text-indigo-200/90 font-semibold">COURT MANAGEMENT</span>
                                        <span class="h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    </div>
                                    <h1 class="text-2xl md:text-3xl font-black tracking-tight drop-shadow-lg">{{ $settings->app_name ?? config('app.name', 'Justice Portal') }}</h1>
                                    <div class="mt-3 flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-sm font-medium text-indigo-100 backdrop-blur-sm">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ \App\Support\EthiopianDate::format($displayNow, withTime: true) }}
                                        </span>
                                        <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-sm font-medium text-indigo-100 backdrop-blur-sm">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            {{ __('signage.public_display') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Cases -->
                            <div class="text-right mt-4 md:mt-0">
                                <div class="inline-block rounded-xl bg-white/10 backdrop-blur-sm p-4 border border-white/20">
                                    <p class="text-xs uppercase tracking-[0.25em] text-indigo-200/80 font-semibold mb-2">{{ __('signage.active_cases') }}</p>
                                    <div class="flex items-baseline justify-end gap-2">
                                        <p class="text-5xl md:text-6xl font-black leading-none drop-shadow-xl tracking-tight">{{ number_format($totalCases) }}</p>
                                        <span class="text-lg font-bold text-indigo-200">cases</span>
                                    </div>
                                    <p class="mt-2 text-xs text-indigo-200/70 font-medium">{{ __('signage.across_departments') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid gap-6 md:grid-cols-2">
                    <!-- {{ __('signage.case_distribution') }} -->
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-soft overflow-hidden">
                        <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-6 py-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50">
                                        <svg class="h-6 w-6 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-bold text-slate-900">{{ __('signage.case_distribution') }}</h2>
                                        <p class="text-sm text-slate-500 mt-0.5">{{ __('signage.status_breakdown') }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded-full border border-orange-200 bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-700">
                                    <span class="h-1.5 w-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                                    {{ __('signage.live_status') }}
                                </span>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid gap-4">
                                @foreach($statusCounts as $stat)
                                    @php
                                        $pct = $totalCases > 0 ? round(($stat->total / $totalCases) * 100) : 0;
                                        $colors = [
                                            'active' => ['bg' => 'bg-indigo-600', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200', 'light' => 'bg-indigo-50'],
                                            'pending' => ['bg' => 'bg-amber-500', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'light' => 'bg-amber-50'],
                                            'closed' => ['bg' => 'bg-slate-400', 'text' => 'text-slate-700', 'border' => 'border-slate-200', 'light' => 'bg-slate-50'],
                                            'hearing' => ['bg' => 'bg-blue-600', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'light' => 'bg-blue-50'],
                                        ];
                                        $statusKey = strtolower($stat->label);
                                        $color = $colors[$statusKey] ?? $colors['active'];
                                    @endphp
                                    <div class="flex items-center gap-4 p-3 rounded-xl border {{ $color['border'] }} bg-white hover:bg-slate-50 transition-colors">
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="font-semibold text-slate-900">{{ $stat->label }}</span>
                                                <span class="text-lg font-bold text-slate-900">{{ number_format($stat->total) }}</span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div class="flex-1 h-2.5 rounded-full bg-slate-200 overflow-hidden">
                                                    <div class="h-full rounded-full {{ $color['bg'] }} transition-all duration-700" style="width: {{ $pct }}%;"></div>
                                                </div>
                                                <span class="text-sm font-bold {{ $color['text'] }} min-w-[3rem] text-right">{{ $pct }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- {{ __('signage.case_categories') }} -->
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-soft overflow-hidden">
                        <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-6 py-5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50">
                                        <svg class="h-6 w-6 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="text-lg font-bold text-slate-900">{{ __('signage.case_categories') }}</h2>
                                        <p class="text-sm text-slate-500 mt-0.5">{{ __('signage.top_categories') }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                                    <span class="text-indigo-600">📊</span>
                                    Top {{ count($categoryCounts) }}
                                </span>
                            </div>
                        </div>
                        <div class="p-6">
                            @php
                                $maxCategory = max(collect($categoryCounts ?? [])->pluck('total')->all() ?: [1]);
                            @endphp
                            <div class="grid gap-4">
                                @foreach($categoryCounts as $cat)
                                    <div class="group flex items-center justify-between p-4 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition-colors">
                                        <div class="flex items-center gap-4">
                                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-50 to-indigo-100 group-hover:from-indigo-100 group-hover:to-indigo-200 transition-all">
                                                <span class="text-lg font-bold text-indigo-700">{{ $cat->total }}</span>
                                            </div>
                                            <div>
                                                <h3 class="font-semibold text-slate-900">{{ $cat->name }}</h3>
                                                <div class="mt-2 flex items-center gap-2">
                                                    <div class="h-2 w-24 rounded-full bg-slate-200 overflow-hidden">
                                                        <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-indigo-600 transition-all duration-700" style="width: {{ $maxCategory > 0 ? round(($cat->total / $maxCategory) * 100) : 0 }}%;"></div>
                                                    </div>
                                                    <span class="text-sm text-slate-500">{{ round(($cat->total / $maxCategory) * 100) }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="text-sm font-medium text-slate-500">cases</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- {{ __('signage.todays_activity') }} -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-soft overflow-hidden">
                    <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-6 py-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50">
                                    <svg class="h-6 w-6 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-lg font-bold text-slate-900">{{ __('signage.todays_activity') }}</h2>
                                    <p class="text-sm text-slate-500 mt-0.5">{{ __('signage.live_status') }}</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700">
                                    <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                                    {{ $todayCases->count() }} {{ __('signage.new_submissions') }}
                                </span>
                                <span class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700">
                                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                    {{ $todayHearings->count() }} {{ __('signage.hearings') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid gap-6 lg:grid-cols-2">
                            
                            <!-- {{ __('signage.new_submissions') }} -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-base font-bold text-slate-900">{{ __('signage.new_submissions') }}</h3>
                                        <p class="text-sm text-slate-500 mt-0.5">{{ __('signage.live_status') }}</p>
                                    </div>
                                    <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full">{{ __('signage.today') }}</span>
                                </div>
                                <div class="overflow-hidden rounded-xl border border-slate-200 max-h-[300px] overflow-y-auto scrollbar-thin">
                                    <table class="w-full text-sm table-sticky">
                                        <thead class="bg-slate-50">
                                            <tr class="border-b border-slate-200">
                                                <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('signage.case_number') }}</th>
                                                <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('applicants.name') ?? 'Applicant' }}</th>
                                                <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('cases.case_type') ?? 'Category' }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @forelse($todayCases as $case)
                                                <tr class="hover:bg-slate-50 transition-colors">
                                                    @php
                                                        $applicantFull = trim((string) ($case->applicant?->full_name ?? ''));
                                                        if ($applicantFull === '') {
                                                            $applicantFull = $case->applicant_name ?? $case->applicant?->name ?? 'Unknown';
                                                        }
                                                    @endphp
                                                    <td class="py-3 px-4">
                                                        <span class="font-bold text-slate-800 font-mono text-sm">{{ $case->case_number ?? __('signage.case_number').' '.$case->id }}</span>
                                                    </td>
                                                    <td class="py-3 px-4 text-slate-700">{{ Str::limit($applicantFull, 18) }}</td>
                                                    <td class="py-3 px-4">
                                                        <span class="inline-flex items-center rounded-md bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700 border border-indigo-100">
                                                            {{ $case->caseType?->name ?? 'General' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="py-8 px-4 text-center text-slate-400">
                                                        <div class="flex flex-col items-center gap-2">
                                                            <span class="text-2xl">📄</span>
                                                            <span>{{ __('signage.no_new_submissions') }}</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

<!-- {{ __('signage.hearings') }} -->
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-base font-bold text-slate-900">{{ __('signage.scheduled_hearings') }}</h3>
                                        <p class="text-sm text-slate-500 mt-0.5">{{ __('signage.live_status') }}</p>
                                    </div>
                                    <span class="text-xs font-semibold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full">{{ __('signage.today') }}</span>
                                </div>
                                <div class="overflow-hidden rounded-xl border border-slate-200 max-h-[300px] overflow-y-auto scrollbar-thin">
                                    <table class="w-full text-sm table-sticky">
                                        <thead class="bg-slate-50">
                                            <tr class="border-b border-slate-200">
                                                <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('signage.case_number') }}</th>
                                                <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('signage.time') }}</th>
                                                <th class="py-3 px-4 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('signage.type') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @forelse($todayHearings as $hearing)
                                                <tr class="hover:bg-slate-50 transition-colors">
                                                    <td class="py-3 px-4">
                                                        <span class="font-bold text-slate-800 font-mono text-sm">{{ $hearing->courtCase?->case_number ?? __('signage.case_number').' '.$hearing->case_id }}</span>
                                                    </td>
                                                    <td class="py-3 px-4 font-medium text-slate-800">
                                                        {{ \App\Support\EthiopianDate::format($hearing->hearing_at, withTime: true) }}
                                                    </td>
                                                    <td class="py-3 px-4">
                                                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 border border-blue-100">
                                                            {{ $hearing->courtCase?->caseType?->name ?? __('signage.hearings') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="py-8 px-4 text-center text-slate-400">
                                                        <div class="flex flex-col items-center gap-2">
                                                            <span class="text-2xl">📅</span>
                                                            <span>{{ __('signage.no_hearings') }}</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Staff -->
            <div class="rounded-2xl border border-slate-200 bg-white shadow-soft overflow-hidden">
                <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-6 py-5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50">
                                <svg class="h-6 w-6 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">{{ __('signage.on_duty_staff') }}</h2>
                                <p class="text-sm text-slate-500 mt-0.5">{{ __('signage.currently_active_personnel') }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            {{ $activeStaff->count() }} Active
                        </span>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse($activeStaff as $person)
                            <div class="group flex items-center gap-4 p-4 rounded-xl border border-slate-200 bg-gradient-to-r from-white to-slate-50 hover:bg-slate-50 hover:border-indigo-200 transition-all">
                                <div class="relative">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-600 text-white font-bold text-xl shadow-md overflow-hidden">
                                        @if($person->avatar_path)
                                            <img class="h-full w-full object-cover" src="{{ \Illuminate\Support\Facades\Storage::url($person->avatar_path) }}" alt="{{ $person->name }}">
                                        @else
                                            {{ mb_strtoupper(mb_substr($person->name ?? 'N/A', 0, 1)) }}
                                        @endif
                                    </div>
                                    <div class="absolute -bottom-1 -right-1 h-4 w-4 rounded-full border-2 border-white bg-emerald-500"></div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-slate-900 truncate">{{ $person->name }}</h3>
                                    <p class="text-sm text-slate-600 truncate">{{ $person->position ?? 'Court Staff' }}</p>
                                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                        <span class="inline-flex items-center gap-1.5 rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            {{ __('signage.on_duty') }}
                                        </span>
                                        <span class="text-slate-500 font-medium">Since {{ \App\Support\EthiopianDate::format(now(), withTime: false) }}</span>
                                    </div>
                                </div>
                            </div>
                        
                        @empty
                            <div class="rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 p-10 text-center">
                                <div class="flex flex-col items-center gap-3 text-slate-400">
                                    <span class="text-3xl">&#9878;</span>
                                    <p class="font-medium">{{ __('signage.no_staff') }}</p>
                                    <p class="text-sm">{{ __('signage.offline_staff') }}</p>
                                </div>
                            </div>
                        @endforelse

                    </div>
                    
                    <!-- Display Info Footer -->
                    <div class="mt-8 pt-6 border-t border-slate-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-slate-100 to-slate-50">
                                    <span class="text-slate-600 text-lg">⏱</span>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ __('signage.public_display') }}</p>
                                    <p class="text-xs text-slate-500">{{ __('signage.auto_refresh') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-medium text-slate-500">{{ __('signage.updated') }}</p>
                                <p class="text-sm font-bold text-slate-900">{{ $displayNow->format('H:i:s') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>








