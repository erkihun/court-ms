<x-admin-layout title="New Appeal">
    @section('page_header','New Appeal')

    <form method="POST" action="{{ route('appeals.store') }}"
        class="rounded-md border border-gray-200 bg-white p-5 space-y-5 shadow-sm">
        @csrf

        <div>
            <label class="block text-sm text-gray-700 mb-1 font-medium">Case</label>
            <select name="court_case_id"
                class="w-full rounded-md border border-gray-300 bg-white text-gray-900 px-3 py-2
                           focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-500">
                @foreach($cases as $c)
                <option value="{{ $c->id }}" @selected(old('court_case_id')==$c->id)>
                    {{ $c->case_number }} — {{ $c->title }}
                </option>
                @endforeach
            </select>
            @error('court_case_id')
            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div>
            <label class="block text-sm text-gray-700 mb-1 font-medium">Title</label>
            <input name="title" value="{{ old('title') }}"
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
                             focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-blue-500">{{ old('grounds') }}</textarea>
        </div>

        <div class="flex flex-wrap gap-2 pt-2">
            <button class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white
                           hover:bg-blue-500 border border-blue-600 transition-colors duration-200">
                Create
            </button>
            <a href="{{ route('appeals.index') }}"
                class="inline-flex items-center justify-center rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-800
                      hover:bg-gray-300 border border-gray-300 transition-colors duration-200">
                Cancel
            </a>
        </div>
    </form>
</x-admin-layout>