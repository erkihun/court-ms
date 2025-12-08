<x-admin-layout title="{{ __('Add Case Type') }}">
    @section('page_header', __('Add Case Type'))

    <div class="p-6 rounded-xl border border-gray-200 bg-white shadow-sm">
        <form method="POST" action="{{ route('case-types.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('Name') }} <span class="text-rose-600">*</span></label>
                <input name="name" value="{{ old('name') }}" required
                    class="mt-1 w-full px-3 py-2 rounded-lg bg-white border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @error('name') <p class="text-rose-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('Prifix') }} <span class="text-rose-600">*</span></label>
                <input name="prifix" value="{{ old('prifix') }}" required
                    class="mt-1 w-full px-3 py-2 rounded-lg bg-white border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @error('prifix') <p class="text-rose-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-2">
                <a href="{{ route('case-types.index') }}"
                    class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-800">{{ __('Cancel') }}</a>
                <button class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
</x-admin-layout>