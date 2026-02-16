<x-admin-layout title="{{ __('announcements.edit_heading') }}">
    @section('page_header', __('announcements.edit_heading'))

    <div class="p-6 bg-white rounded-xl border border-gray-200 space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ __('announcements.edit_heading') }}</h1>
            <p class="text-sm text-gray-600">{{ __('announcements.edit_description') }}</p>
        </div>

        <form action="{{ route('announcements.update', $announcement) }}" method="POST" class="space-y-6">
            @csrf
            @method('PATCH')

            @include('admin.announcements._form', ['announcement' => $announcement])

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('announcements.index') }}" class="text-sm text-gray-600 hover:underline">
                    {{ __('announcements.cancel') }}
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-700">
                    {{ __('announcements.form_update') }}
                </button>
            </div>
        </form>
    </div>
    @include('admin.announcements._tinymce')
</x-admin-layout>
