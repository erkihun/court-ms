<x-admin-layout title="{{ __('announcements.show_heading') }}">
    @section('page_header', __('announcements.show_heading'))

    <div class="p-6 bg-white rounded-xl border border-gray-200 space-y-6">
        <div class="flex flex-col gap-2">
            <h1 class="text-2xl font-bold text-gray-900">{{ $announcement->title }}</h1>
            <p class="text-sm text-gray-500">{{ $announcement->created_at?->format('Y-m-d H:i') }}</p>
        </div>

        <div class="prose max-w-none text-gray-800">
            @php
                $allowedTags = '<p><br><strong><em><u><ol><ul><li><a><span><div><h1><h2><h3><blockquote>';
                $safeContent = strip_tags($announcement->content ?? '', $allowedTags);
            @endphp
            {!! $safeContent !!}
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('announcements.index') }}"
                class="text-sm font-semibold text-gray-600 hover:underline">{{ __('announcements.back_to_list') }}</a>
        </div>
    </div>
</x-admin-layout>
