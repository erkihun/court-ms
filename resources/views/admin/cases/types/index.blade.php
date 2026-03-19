<x-admin-layout title="{{ __('Case Types') }}">
    @section('page_header', __('Case Types'))

    {{-- Session Alerts (Improved styling) --}}
    @if (session('success'))
    <x-ui.alert type="success" class="mb-4" role="alert">
        {{ session('success') }}
    </x-ui.alert>
    @endif
    @if (session('error'))
    <x-ui.alert type="error" class="mb-4" role="alert">
        {{ session('error') }}
    </x-ui.alert>
    @endif

    {{-- Search and Action Bar (Modern Card) --}}
    <x-ui.filter-bar class="mb-6">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
            {{-- Search Form --}}
            <form method="GET" class="flex items-center gap-3">
                <div class="flex-grow">
                    <label for="search-q" class="sr-only">Search</label>
                    <x-ui.input id="search-q" name="q" value="{{ $q }}" placeholder="{{ __('Search name…') }}" />
                </div>
                {{-- Search Button --}}
                <x-ui.button type="submit" variant="secondary">{{ __('Search') }}</x-ui.button>
            </form>

            {{-- Add Button --}}
            <x-ui.button :href="route('case-types.create')">{{ __('Add Case Type') }}</x-ui.button>
        </div>
    </x-ui.filter-bar>

    {{-- Table Container (Modern Card) --}}
    <x-ui.table>
        <x-slot name="head">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Name') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Prefix') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Cases Using') }}</th>
                    <th class="px-6 py-3"></th> {{-- Actions column header --}}
                </tr>
        </x-slot>
        <x-slot name="body">
                @forelse($types as $t)
                {{-- Added hover effect for rows --}}
                <tr class="hover:bg-gray-50 transition duration-100">
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $t->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-mono">{{ $t->prefix ?? '—' }}</td>
                    {{-- Cases Using count as a chip for clarity --}}
                    <td class="px-6 py-4 whitespace-nowrap">
                        <x-ui.badge type="info">
                            {{ $t->cases_count }}
                        </x-ui.badge>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <x-ui.actions class="justify-end">
                            {{-- Edit Button (Secondary style) --}}
                            <x-ui.button :href="route('case-types.edit', $t->id)" variant="secondary" size="sm">{{ __('Edit') }}</x-ui.button>
                            {{-- Delete Button (Danger style) --}}
                            <form method="POST" action="{{ route('case-types.delete', $t->id) }}"
                                onsubmit="return confirm('{{ __('Delete this case type?') }}')">
                                @csrf @method('DELETE')
                                <x-ui.button type="submit" variant="danger" size="sm">{{ __('Delete') }}</x-ui.button>
                            </form>
                        </x-ui.actions>
                    </td>
                </tr>
                @empty
                <tr>
                    {{-- Updated colspan for columns and better no results state --}}
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500 text-base">
                        <x-ui.empty :title="__('cases.types.empty')" :description="__('cases.types.adjust_search_hint')">
                            <x-slot name="icon">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9.25 10m.5 7h4.5M12 21a9 9 0 100-18 9 9 0 000 18z" />
                                </svg>
                            </x-slot>
                        </x-ui.empty>
                    </td>
                </tr>
                @endforelse
        </x-slot>
        <x-slot name="footer">
            {{ $types->links() }}
        </x-slot>
    </x-ui.table>
</x-admin-layout>

