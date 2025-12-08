<x-admin-layout title="{{ __('Case Types') }}">
    @section('page_header', __('Case Types'))

    {{-- Session Alerts (Improved styling) --}}
    @if (session('success'))
    <div class="mb-4 rounded-lg border border-emerald-300 bg-emerald-50 p-4 text-sm text-emerald-800 shadow-sm" role="alert">
        {{ session('success') }}
    </div>
    @endif
    @if (session('error'))
    <div class="mb-4 rounded-lg border border-red-300 bg-red-50 p-4 text-sm text-red-800 shadow-sm" role="alert">
        {{ session('error') }}
    </div>
    @endif

    {{-- Search and Action Bar (Modern Card) --}}
    <div class="p-4 rounded-xl border border-gray-200 bg-white shadow-md mb-6">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
            {{-- Search Form --}}
            <form method="GET" class="flex items-center gap-3">
                <div class="flex-grow">
                    <label for="search-q" class="sr-only">Search</label>
                    <input id="search-q" name="q" value="{{ $q }}" placeholder="{{ __('Search name…') }}"
                        {{-- Clean input style --}}
                        class="w-full px-4 py-2 rounded-lg bg-gray-50 border border-gray-300 text-gray-900 focus:ring-blue-500 focus:border-blue-500 transition shadow-inner">
                </div>
                {{-- Search Button --}}
                <button type="submit"
                    class="h-10 px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium transition shadow-sm">
                    {{ __('Search') }}
                </button>
            </form>

            {{-- Add Button --}}
            <a href="{{ route('case-types.create') }}"
                class="h-10 px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium transition shadow-md flex items-center justify-center">
                {{ __('Add Case Type') }}
            </a>
        </div>
    </div>

    {{-- Table Container (Modern Card) --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-md overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            {{-- Table Header (Refined style) --}}
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Name') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Prifix') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Cases Using') }}</th>
                    <th class="px-6 py-3"></th> {{-- Actions column header --}}
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($types as $t)
                {{-- Added hover effect for rows --}}
                <tr class="hover:bg-gray-50 transition duration-100">
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $t->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $t->prifix }}</td>
                    {{-- Cases Using count as a chip for clarity --}}
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-blue-50 text-blue-800">
                            {{ $t->cases_count }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-2 justify-end">
                            {{-- Edit Button (Secondary style) --}}
                            <a href="{{ route('case-types.edit', $t->id) }}"
                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                                {{ __('Edit') }}
                            </a>
                            {{-- Delete Button (Danger style) --}}
                            <form method="POST" action="{{ route('case-types.delete', $t->id) }}"
                                onsubmit="return confirm('{{ __('Delete this case type?') }}')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="inline-flex items-center px-3 py-1.5 border border-transparent shadow-sm text-xs leading-4 font-medium rounded-md text-white bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition">
                                    {{ __('Delete') }}
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    {{-- Updated colspan for 4 columns and better no results state --}}
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500 text-base">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9.25 10m.5 7h4.5M12 21a9 9 0 100-18 9 9 0 000 18z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('No case types found.') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('Try adjusting your search criteria.') }}</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination links --}}
        <div class="p-4 border-t border-gray-200">
            {{ $types->links() }}
        </div>
    </div>
</x-admin-layout>