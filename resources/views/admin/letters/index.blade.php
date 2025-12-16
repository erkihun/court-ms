{{-- resources/views/letters/index.blade.php --}}

@php
use Illuminate\Support\Str;
$latestLetter = $letters->first();
$approvedCount = $letters->where('approval_status', 'approved')->count();
$rejectedCount = $letters->where('approval_status', 'rejected')->count();
$categoryCounts = $letters->map(function ($letter) {
    return optional(optional($letter)->template)->category;
})->filter()->countBy()->sortDesc();
$topCategory = $categoryCounts->keys()->first();
$canCreateLetter = function_exists('userHasPermission') ? userHasPermission('letters.create') : (auth()->user()?->hasPermission('letters.create') ?? false);
$canUpdateLetter = function_exists('userHasPermission') ? userHasPermission('letters.update') : (auth()->user()?->hasPermission('letters.update') ?? false);
$canDeleteLetter = function_exists('userHasPermission') ? userHasPermission('letters.delete') : (auth()->user()?->hasPermission('letters.delete') ?? false);
$canApproveLetter = function_exists('userHasPermission') ? userHasPermission('letters.approve') : (auth()->user()?->hasPermission('letters.approve') ?? false);
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
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ __('letters.titles.index') }}</h1>
                        <p class="text-sm text-gray-600">{{ __('letters.description.index') }}</p>
                    </div>
                </div>
            </div>
            @if($canCreateLetter)
            <a href="{{ route('letters.compose') }}"
                class="group inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-orange-500 text-white font-semibold shadow-lg hover:shadow-xl hover:from-blue-700 hover:to-orange-600 transition-all duration-300 transform hover:-translate-y-0.5 focus:outline-none focus:ring-3 focus:ring-blue-500/30 focus:ring-offset-2">
                <svg class="w-5 h-5 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('letters.actions.new_letter') }}
            </a>
            @endif
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="group relative bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 p-6 overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-full -translate-y-8 translate-x-8 group-hover:scale-110 transition-transform duration-300"></div>
                <div class="relative flex items-center gap-4 mb-4">
                    <div class="p-3 rounded-xl bg-blue-100">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">{{ __('letters.cards.total_letters') }}</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">
                            {{ $letters instanceof \Illuminate\Contracts\Pagination\Paginator ? $letters->total() : $letters->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="group relative bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 p-6 overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-orange-50 rounded-full -translate-y-8 translate-x-8 group-hover:scale-110 transition-transform duration-300"></div>
                <div class="relative flex items-center gap-4 mb-4">
                    <div class="p-3 rounded-xl bg-orange-100">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Approved Letters</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">
                            {{ $approvedCount }}
                        </p>
                        <p class="text-xs text-gray-500 mt-2">Approved in current view</p>
                    </div>
                </div>
            </div>

            <div class="group relative bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 p-6 overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-50 rounded-full -translate-y-8 translate-x-8 group-hover:scale-110 transition-transform duration-300"></div>
                <div class="relative flex items-center gap-4 mb-4">
                    <div class="p-3 rounded-xl bg-blue-100">
                        <svg class="w-6 h-6 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Rejected Letters</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">
                            {{ $rejectedCount }}
                        </p>
                        <p class="text-xs text-gray-500 mt-2">Rejected in current view</p>
                    </div>
                </div>
            </div>

            <div class="group relative bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-lg transition-all duration-300 p-6 overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-orange-50 rounded-full -translate-y-8 translate-x-8 group-hover:scale-110 transition-transform duration-300"></div>
                <div class="relative flex items-center gap-4 mb-4">
                    <div class="p-3 rounded-xl bg-orange-100">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h10m-10 4h6" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Letter Categories</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">
                            {{ $categoryCounts->count() }}
                        </p>
                        <p class="text-xs text-gray-500 mt-2">
                            {{ $categoryCounts->sum() }} letters @if($topCategory) | Top: {{ $topCategory }} @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Letters Table -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">All Letters</h2>
                        <p class="text-sm text-gray-600">Manage and review your correspondence</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" placeholder="Search letters..."
                                class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none transition-all duration-200">
                        </div>
                        <button class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors duration-200">
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-3">{{ __('letters.cards.no_letters_yet') }}</h3>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">You haven't created any letters yet. Start by composing your first letter to begin correspondence.</p>
                <a href="{{ route('letters.compose') }}"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white font-semibold rounded-xl hover:from-emerald-700 hover:to-emerald-600 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    {{ __('letters.actions.new_letter') }}
                </a>
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
                                {{ __('letters.table.recipient') }}
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
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
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

                            <!-- Recipient Column -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100 border border-emerald-200 flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
                                            <svg class="w-6 h-6 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.121 17.804A9 9 0 0112 15c2.174 0 4.164.774 5.879 2.06M15 11a3 3 0 10-6 0 3 3 0 006 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.739 14.74A7 7 0 0012 11m0 0a7 7 0 00-7.74 3.74" />
                                            </svg>
                                        </div>
                                        @if($status)
                                        <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full border-2 border-white {{ $statusClasses[$status] }} flex items-center justify-center">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusIcons[$status] }}" />
                                            </svg>
                                        </div>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $letter->recipient_name ?? __('letters.cards.missing') }}</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ $letter->recipient_title ?? __('letters.cards.recipient_fallback') }}</p>
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
                                        <span>{{ ucfirst($status) }}</span>
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
                                        onclick="window.location='{{ route('letters.show', $letter) }}'"
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

                                    <!-- Edit Button -->
                                    <button type="button"
                                        onclick="window.location='{{ route('letters.edit', $letter) }}'"
                                        class="p-2.5 rounded-xl border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400 hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 group/tooltip relative"
                                        title="{{ $isApproved ? 'Approved letters cannot be edited' : __('letters.actions.edit') }}"
                                        {{ $isApproved ? 'disabled' : '' }}>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                            {{ __('letters.actions.edit') }}
                                        </div>
                                    </button>

                                    <!-- Approval Dropdown -->
                                    @if(!$isApproved && $canApproveLetter)
                                    @if($canApproveLetter)
                                    <div x-data="{ open: false }" class="relative">
                                        <button type="button"
                                            @click="open = !open"
                                            class="p-2.5 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 hover:border-emerald-300 hover:scale-105 transition-all duration-200 group/tooltip relative"
                                            title="Approval Actions">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                                Approval
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
                                                    <span>Approved</span>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('letters.approve', [$letter, 'status' => 'returned']) }}" class="mb-0.5">
                                                @csrf
                                                <button type="submit"
                                                    class="flex items-center gap-3 w-full px-4 py-3 text-sm text-gray-700 hover:bg-amber-50 transition-colors group/item">
                                                    <div class="w-2 h-2 rounded-full bg-amber-500 group-hover/item:scale-125 transition-transform"></div>
                                                    <span>Returned</span>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('letters.approve', [$letter, 'status' => 'rejected']) }}">
                                                @csrf
                                                <button type="submit"
                                                    class="flex items-center gap-3 w-full px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors group/item">
                                                    <div class="w-2 h-2 rounded-full bg-red-500 group-hover/item:scale-125 transition-transform"></div>
                                                    <span>Rejected</span>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    @endif
                                    @endif

                                    <!-- Delete Button -->
                                    @if($canDeleteLetter)
                                    <form method="POST" action="{{ route('letters.destroy', $letter) }}"
                                        onsubmit="return confirm('{{ __('letters.confirm.delete_letter') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="p-2.5 rounded-xl border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 hover:border-red-300 hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100 group/tooltip relative"
                                            title="{{ $isApproved ? 'Approved letters cannot be deleted' : __('letters.actions.delete') }}"
                                            {{ $isApproved ? 'disabled' : '' }}>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 whitespace-nowrap">
                                                {{ __('letters.actions.delete') }}
                                            </div>
                                        </button>
                                    </form>
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
                        Showing <span class="font-semibold">{{ $letters->firstItem() }}</span> to
                        <span class="font-semibold">{{ $letters->lastItem() }}</span> of
                        <span class="font-semibold">{{ $letters->total() }}</span> results
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
