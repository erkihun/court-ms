<x-admin-layout :title="'Edit ' . $appeal->appeal_number">
    @section('page_header','Edit Appeal')

    <form method="POST"
        action="{{ route('appeals.update',$appeal->id) }}"
        class="rounded border border-gray-200 bg-white p-4 md:p-6 space-y-4 shadow-sm">
        @csrf
        @method('PATCH')

        <div>
            <label class="block text-sm text-gray-700 mb-1 font-medium">Title</label>
            <input name="title"
                value="{{ old('title',$appeal->title) }}"
                class="w-full rounded-md border border-gray-300 bg-white text-gray-900 px-3 py-2
                          focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-500">
            @error('title')
            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm text-gray-700 mb-1 font-medium">Grounds</label>
            <textarea name="grounds" rows="6"
                class="w-full rounded-md border border-gray-300 bg-white text-gray-900 px-3 py-2
                             focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-500">{{ old('grounds',$appeal->grounds) }}</textarea>
        </div>

        <div class="flex flex-wrap gap-2">
            <button
                class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white
                       hover:bg-blue-500 border border-blue-600/70 transition-colors duration-200">
                Save
            </button>
            <a href="{{ route('appeals.show',$appeal->id) }}"
                class="inline-flex items-center rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-800
                      hover:bg-gray-300 border border-gray-300 transition-colors duration-200">
                Cancel
            </a>
        </div>
    </form>
</x-admin-layout>