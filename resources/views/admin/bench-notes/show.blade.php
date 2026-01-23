@php
$safeTitle = clean($benchNote->title ?? '', 'cases');
$safeNote = clean($benchNote->note ?? '', 'default');
@endphp

<x-admin-layout title="{{ __('bench.page_header.show') }}">
    @section('page_header', __('bench.page_header.show'))

    <div class="max-w-5xl mx-auto space-y-10">
        <div>
            <a href="{{ route('bench-notes.index', ['case_id' => $benchNote->case_id]) }}"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </a>
        </div>
        <div class="text-center space-y-2 text-sm text-gray-800">
            <div>{{ $benchNote->judgeOne?->name ?? __('bench.meta.unknown') }}</div>
            <div>{{ $benchNote->judgeTwo?->name ?? __('bench.meta.unknown') }}</div>
            <div>{{ $benchNote->judgeThree?->name ?? __('bench.meta.unknown') }}</div>
        </div>

        <div class="space-y-4">
            <h2 class="text-base font-semibold text-gray-900">Note Content</h2>
            <div class="bg-white rounded-lg border border-gray-200 p-4 text-sm text-gray-800 leading-relaxed">
                @if($benchNote->note)
                {!! $safeNote !!}
                @else
                <p class="text-gray-500 italic">{{ __('bench.helpers.empty_content') }}</p>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-3 gap-6 text-center text-sm text-gray-800">
            <div class="space-y-2">
                <div class="font-medium">{{ $benchNote->judgeOne?->name ?? __('bench.meta.unknown') }}</div>
                @if(!empty($benchNote->judgeOne?->signature_url))
                <img src="{{ $benchNote->judgeOne?->signature_url }}" alt="{{ $benchNote->judgeOne?->name }}" class="mx-auto max-h-16 w-auto">
                @else
                <div class="text-gray-500">Judge 1 signature</div>
                @endif
            </div>
            <div class="space-y-2">
                <div class="font-medium">{{ $benchNote->judgeTwo?->name ?? __('bench.meta.unknown') }}</div>
                @if(!empty($benchNote->judgeTwo?->signature_url))
                <img src="{{ $benchNote->judgeTwo?->signature_url }}" alt="{{ $benchNote->judgeTwo?->name }}" class="mx-auto max-h-16 w-auto">
                @else
                <div class="text-gray-500">Judge 2 signature</div>
                @endif
            </div>
            <div class="space-y-2">
                <div class="font-medium">{{ $benchNote->judgeThree?->name ?? __('bench.meta.unknown') }}</div>
                @if(!empty($benchNote->judgeThree?->signature_url))
                <img src="{{ $benchNote->judgeThree?->signature_url }}" alt="{{ $benchNote->judgeThree?->name }}" class="mx-auto max-h-16 w-auto">
                @else
                <div class="text-gray-500">Judge 3 signature</div>
                @endif
            </div>
        </div>
    </div>
</x-admin-layout>
