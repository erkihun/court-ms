<x-admin-layout title="{{ __('announcements.create_heading') }}">
    @section('page_header', __('announcements.create_heading'))

    <div class="p-6 bg-white rounded-xl border border-gray-200 space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('announcements.create_heading') }}</h1>
            <p class="text-sm text-gray-600">{{ __('announcements.create_description') }}</p>
        </div>

        <form action="{{ route('announcements.store') }}" method="POST" class="space-y-6">
            @csrf
            @include('admin.announcements._form')

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('announcements.index') }}" class="text-sm text-gray-600 hover:underline">
                    {{ __('announcements.cancel') }}
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                    {{ __('announcements.form_submit') }}
                </button>
            </div>
        </form>
    </div>
    @include('admin.announcements._tinymce')
</x-admin-layout>
