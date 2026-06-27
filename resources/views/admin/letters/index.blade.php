{{-- resources/views/letters/index.blade.php --}}

@php
use Illuminate\Support\Str;
$latestLetter = $letters->first();
$stats = $stats ?? [];
$approvedCount = (int) ($stats['approved'] ?? 0);
$rejectedCount = (int) ($stats['rejected'] ?? 0);
$categoryCounts = collect($stats['category_counts'] ?? [])->sortDesc();
$topCategory = $categoryCounts->keys()->first();
$canCreateLetter = function_exists('userHasPermission') ? userHasPermission('letters.create') : (auth()->user()?->hasPermission('letters.create') ?? false);
$canUpdateLetter = function_exists('userHasPermission') ? userHasPermission('letters.update') : (auth()->user()?->hasPermission('letters.update') ?? false);
$canDeleteLetter = function_exists('userHasPermission') ? userHasPermission('letters.delete') : (auth()->user()?->hasPermission('letters.delete') ?? false);
$canApproveLetter = function_exists('userHasPermission') ? userHasPermission('letters.approve') : (auth()->user()?->hasPermission('letters.approve') ?? false);
$statusFilter = $statusFilter ?? '';
@endphp

<x-admin-layout title="{{ __('letters.titles.index') }}">

    @section('page_header', __('letters.titles.index'))

    <div class="space-y-8">
        <!-- Header Section -->
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <div class="p-3 rounded-2xl bg-gradient-to-br from-blue-50 to-orange-50">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M7 8h10M7 12h10m-6 4h6M8 4h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V20a1 1 0 01-1 1H8a2 2 0 01-2-2V6a2 2 0 012-2z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ __('letters.titles.index') }}</h1>
                        <p class="text-sm text-gray-600">{{ __('letters.description.index') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="group relative bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 p-6 overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-full -translate-y-8 translate-x-8 group-hover:scale-110 transition-transform duration-300"></div>
                <div class="relative flex items-center gap-4 mb-4">
                    <div class="p-3 rounded-xl bg-blue-100">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h8M8 11h8M8 15h5m-5 5h8a2 2 0 002-2V8.414a1 1 0 00-.293-.707l-2.414-2.414A1 1 0 0014.586 5H8a2 2 0 00-2 2v11a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('letters.cards.total_letters') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">
                            {{ (int) ($stats['total'] ?? ($letters instanceof \Illuminate\Contracts\Pagination\Paginator ? $letters->total() : $letters->count())) }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="group relative bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 p-6 overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-orange-50 rounded-full -translate-y-8 translate-x-8 group-hover:scale-110 transition-transform duration-300"></div>
                <div class="relative flex items-center gap-4 mb-4">
                    <div class="p-3 rounded-xl bg-orange-100">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3l7 4v5c0 5-3.5 8-7 9-3.5-1-7-4-7-9V7l7-4z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('letters.cards.approved_letters') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">
                            {{ $approvedCount }}
                        </p>
                        <p class="text-xs text-gray-500 mt-2">{{ __('letters.cards.approved_letters_hint') }}</p>
                    </div>
                </div>
            </div>

            <div class="group relative bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 p-6 overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-full -translate-y-8 translate-x-8 group-hover:scale-110 transition-transform duration-300"></div>
                <div class="relative flex items-center gap-4 mb-4">
                    <div class="p-3 rounded-xl bg-blue-100">
                        <svg class="w-6 h-6 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 9l-6 6m0-6l6 6" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3l7 4v5c0 5-3.5 8-7 9-3.5-1-7-4-7-9V7l7-4z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('letters.cards.rejected_letters') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">
                            {{ $rejectedCount }}
                        </p>
                        <p class="text-xs text-gray-500 mt-2">{{ __('letters.cards.rejected_letters_hint') }}</p>
                    </div>
                </div>
            </div>

            <div class="group relative bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 p-6 overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-orange-50 rounded-full -translate-y-8 translate-x-8 group-hover:scale-110 transition-transform duration-300"></div>
                <div class="relative flex items-center gap-4 mb-4">
                    <div class="p-3 rounded-xl bg-orange-100">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h8M8 12h8M8 17h5" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 5h14a1 1 0 011 1v12a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 9l4-3m12 3l-4-3" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('letters.cards.letter_categories') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">
                            {{ $categoryCounts->count() }}
                        </p>
                        <p class="text-xs text-gray-500 mt-2">
                            {{ $categoryCounts->sum() }} {{ __('letters.cards.letters_count_suffix') }}@if($topCategory) | {{ __('letters.cards.top_category', ['category' => $topCategory]) }} @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Letters Table -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="px-6 pt-5">
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('letters.index', ['status' => 'pending']) }}"
                        class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition-colors {{ $statusFilter === 'pending' ? 'border-amber-300 bg-amber-50 text-amber-800' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}">
                        <span class="h-2.5 w-2.5 rounded-full {{ $statusFilter === 'pending' ? 'bg-amber-500' : 'bg-amber-400' }}"></span>
                        {{ __('letters.table.status_pending') }}
                    </a>
                    <a href="{{ route('letters.index', ['status' => 'approved']) }}"
                        class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition-colors {{ $statusFilter === 'approved' ? 'border-emerald-300 bg-emerald-50 text-emerald-800' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}">
                        <span class="h-2.5 w-2.5 rounded-full {{ $statusFilter === 'approved' ? 'bg-emerald-500' : 'bg-emerald-400' }}"></span>
                        {{ __('letters.table.status_approved') }}
                    </a>
                    <a href="{{ route('letters.index', ['status' => 'rejected']) }}"
                        class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition-colors {{ $statusFilter === 'rejected' ? 'border-red-300 bg-red-50 text-red-800' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}">
                        <span class="h-2.5 w-2.5 rounded-full {{ $statusFilter === 'rejected' ? 'bg-red-500' : 'bg-red-400' }}"></span>
                        {{ __('letters.table.status_rejected') }}
                    </a>
                </div>
            </div>

            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('letters.table.all_letters') }}</h2>
                        <p class="text-sm text-gray-600">{{ __('letters.table.manage_correspondence') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" placeholder="{{ __('letters.table.search_placeholder') }}"
                                class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition-all duration-200">
                        </div>
                        <button class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200"
                            title="{{ __('letters.table.filter') }}">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            @if($letters->isEmpty())
            <div class="p-16 text-center">
                <div class="mx-auto w-20 h-20 bg-gradient-to-br from-emerald-50 to-blue-50 rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 8h10M7 12h10m-6 4h6M8 4h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V20a1 1 0 01-1 1H8a2 2 0 01-2-2V6a2 2 0 012-2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">{{ __('letters.cards.no_letters_yet') }}</h3>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">{{ __('letters.cards.empty_description') }}</p>
            </div>
            @else
            <div class="overflow-x-auto relative">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="sticky top-0 z-10 bg-white/90 backdrop-blur-sm border-b border-gray-200">
                        <tr class="bg-gray-50/70">
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <span>{{ __('letters.table.reference') }}</span>
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                    </svg>
                                </div>
                            </th>
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                {{ __('letters.table.applicant') }}
                            </th>
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                {{ __('letters.table.respondent') }}
                            </th>
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                {{ __('letters.table.created') }}
                            </th>
                            <th scope="col" class="px-5 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                {{ __('letters.table.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($letters as $letter)
                        @php
                        $isApproved = $letter->approval_status === 'approved';
                        $status = $letter->approval_status ?? null;
                        $statusClasses = [
                        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'returned' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'rejected' => 'bg-red-50 text-red-700 border-red-200'
                        ];
                        $statusIcons = [
                        'approved' => 'M5 13l4 4L19 7',
                        'returned' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
                        'rejected' => 'M6 18L18 6M6 6l12 12'
                        ];
                        @endphp

                        <tr class="group hover:bg-gray-50/50 transition-all duration-200">
                            <!-- Reference Column -->
                            <td class="px-5 py-4">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h8M8 11h8M8 15h4" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 4h5.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V20a1 1 0 01-1 1H8a2 2 0 01-2-2V6a2 2 0 012-2z" />
                                                </svg>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="inline-block px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                                {{ $letter->reference_number ?? __('letters.cards.missing') }}
                                            </span>
                                            @if($letter->subject)
                                            <p class="text-xs text-gray-500 mt-1 truncate max-w-xs">{{ Str::limit($letter->subject, 50) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Applicant Column -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 border border-emerald-200 flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
                                            <svg class="w-6 h-6 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 9.5a3 3 0 116 0 3 3 0 01-6 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.5 18a5.5 5.5 0 0111 0" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 8h5m-2.5-2.5V10.5" />
                                            </svg>
                                        </div>
                                        @if($letter->send_to_applicant)
                                        <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full border-2 border-white bg-emerald-50 text-emerald-700 border-emerald-200 flex items-center justify-center">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        @endif
                                    </div>
                                    <div>
                                        @if($letter->send_to_applicant)
                                        <p class="text-sm font-semibold text-gray-900">{{ $letter->applicant_name ?? __('letters.cards.missing') }}</p>
                                        @if(!empty($letter->applicant_is_lawyer) && !empty($letter->applicant_lawyer_name))
                                        <p class="text-xs text-indigo-700 mt-0.5">{{ __('dashboard.submitted_by_lawyer', ['name' => $letter->applicant_lawyer_name]) }}</p>
                                        @else
                                        <p class="text-xs text-gray-500 mt-0.5">{{ __('letters.form.deliver_applicant') }}</p>
                                        @endif
                                        @else
                                        <p class="text-sm font-semibold text-gray-900">{{ __('letters.cards.missing') }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ __('letters.form.deliver_applicant') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Respondent Column -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
                                            <svg class="w-6 h-6 text-orange-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 9.5a3 3 0 116 0 3 3 0 01-6 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.5 18a6.5 6.5 0 0113 0" />
                                            </svg>
                                        </div>
                                        @if($letter->send_to_respondent)
                                        <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full border-2 border-white bg-emerald-50 text-emerald-700 border-emerald-200 flex items-center justify-center">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $letter->send_to_respondent ? ($letter->respondent_name ?? __('letters.cards.missing')) : __('letters.cards.missing') }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ __('letters.form.deliver_respondent') }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Created/Status Column -->
                            <td class="px-5 py-4">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span class="text-sm text-gray-900">
                                            {{ \App\Support\EthiopianDate::format($letter->created_at) }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="text-xs text-gray-500">
                                            {{ \App\Support\EthiopianDate::formatTime($letter->created_at, timeFormat: 'h:i A') }}
                                        </span>
                                    </div>
                                    @if($status)
                                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border text-xs font-semibold
                                        {{ $statusClasses[$status] ?? 'border-gray-200 bg-gray-50 text-gray-600' }}">
                                        <span class="w-2 h-2 rounded-full bg-current"></span>
                                        <span>{{ __('letters.table.status_' . $status) }}</span>
                                        @if($letter->approved_by_name)
                                        <span class="text-[11px] font-normal text-gray-500">| {{ $letter->approved_by_name }}</span>
                                        @endif
                                    </div>
                                    @endif
                                </div>
                            </td>

                            <!-- Actions Column -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-1">
                                    <!-- View Button -->
                                    <button type="button"
                                        onclick="window.open('{{ route('letters.show', $letter) }}', '_blank', 'noopener')"
                                        class="p-2.5 rounded-xl border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 hover:scale-105 transition-all duration-200 group/tooltip relative"
                                        title="{{ __('letters.actions.view') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                            {{ __('letters.actions.view') }}
                                        </div>
                                    </button>

                                    @if(!$isApproved)
                                    <!-- Edit Button -->
                                    <button type="button"
                                        onclick="window.location='{{ route('letters.edit', $letter) }}'"
                                        class="p-2.5 rounded-xl border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 hover:scale-105 transition-all duration-200 group/tooltip relative"
                                        title="{{ __('letters.actions.edit') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                            {{ __('letters.actions.edit') }}
                                            </div>
                                        </button>

                                    @if($canApproveLetter)
                                    <div x-data="{ open: false }" class="relative">
                                        <button type="button"
                                            @click="open = !open"
                                            class="p-2.5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 hover:border-emerald-300 hover:scale-105 transition-all duration-200 group/tooltip relative"
                                            title="{{ __('letters.actions.approve') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                                {{ __('letters.actions.approve') }}
                                            </div>
                                        </button>

                                        <div x-cloak x-show="open" @click.outside="open = false"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="transform opacity-0 scale-95"
                                            x-transition:enter-end="transform opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="transform opacity-100 scale-100"
                                            x-transition:leave-end="transform opacity-0 scale-95"
                                            class="absolute right-0 mt-1 w-48 rounded-xl border border-gray-200 bg-white shadow-2xl z-50 overflow-hidden">
                                            <form method="POST" action="{{ route('letters.approve', [$letter, 'status' => 'approved']) }}" class="mb-0.5">
                                                @csrf
                                                <button type="submit"
                                                    class="flex items-center gap-3 w-full px-4 py-3 text-sm text-gray-700 hover:bg-emerald-50 transition-colors group/item">
                                                    <div class="w-2 h-2 rounded-full bg-emerald-500 group-hover/item:scale-125 transition-transform"></div>
                                                    <span>{{ __('letters.table.status_approved') }}</span>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('letters.approve', [$letter, 'status' => 'returned']) }}" class="mb-0.5">
                                                @csrf
                                                <button type="submit"
                                                    class="flex items-center gap-3 w-full px-4 py-3 text-sm text-gray-700 hover:bg-amber-50 transition-colors group/item">
                                                    <div class="w-2 h-2 rounded-full bg-amber-500 group-hover/item:scale-125 transition-transform"></div>
                                                    <span>{{ __('letters.table.status_returned') }}</span>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('letters.approve', [$letter, 'status' => 'rejected']) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="flex items-center gap-3 w-full px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors group/item">
                                                    <div class="w-2 h-2 rounded-full bg-red-500 group-hover/item:scale-125 transition-transform"></div>
                                                    <span>{{ __('letters.table.status_rejected') }}</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    @endif

                                    <!-- Delete Button -->
                                    @if($canDeleteLetter)
                                    <form method="POST" action="{{ route('letters.destroy', $letter) }}"
                                        onsubmit="return confirm('{{ __('letters.confirm.delete_letter') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="p-2.5 rounded-xl border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 hover:border-red-300 hover:scale-105 transition-all duration-200 group/tooltip relative"
                                            title="{{ __('letters.actions.delete') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                                {{ __('letters.actions.delete') }}
                                            </div>
                                        </button>
                                    </form>
                                    @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($letters instanceof \Illuminate\Contracts\Pagination\Paginator && $letters->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="text-sm text-gray-600">
                        {{ __('letters.table.showing_results', [
                            'first' => $letters->firstItem(),
                            'last' => $letters->lastItem(),
                            'total' => $letters->total(),
                        ]) }}
                    </div>
                    <div class="flex items-center space-x-2">
                        {!! $letters->onEachSide(1)->links() !!}
                    </div>
                </div>
            </div>
            @endif
            @endif
        </div>
    </div>

    <style>
        [x-cloak] {
            display: none !important;
        }

        .shadow-lg {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        }

        .shadow-xl {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .shadow-2xl {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
    </style>
</x-admin-layout>
