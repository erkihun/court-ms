<x-admin-layout title="{{ __('about.title') }}">
    @section('page_header', __('about.title'))

    <div class="p-6 bg-white rounded-xl border border-gray-200 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $about->title }}</h1>
                <p class="text-sm text-gray-500">/{{ $about->slug }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('about.edit', $about) }}"
                    class="px-3 py-2 rounded-md bg-amber-600 text-white text-sm hover:bg-amber-700">
                    {{ __('about.action_edit') }}
                </a>
                <a href="{{ route('about.index') }}"
                    class="px-3 py-2 rounded-md border border-gray-300 text-gray-700 text-sm">
                    {{ __('about.form_cancel') }}
                </a>
            </div>
        </div>

        <div class="text-sm">
            @if($about->is_published)
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                {{ __('about.status_published') }}
            </span>
            @else
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                {{ __('about.status_draft') }}
            </span>
            @endif
        </div>

        <div class="prose max-w-none">
            {!! nl2br(e($about->body)) !!}
        </div>
    </div>
</x-admin-layout>
