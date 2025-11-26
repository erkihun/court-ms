<x-admin-layout title="{{ __('Case Types') }}">
    @section('page_header', __('Case Types'))

    @if (session('success'))
    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-emerald-700">
        {{ session('success') }}
    </div>
    @endif
    @if (session('error'))
    <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 p-3 text-rose-700">
        {{ session('error') }}
    </div>
    @endif

    <div class="p-5 rounded-xl border border-gray-200 bg-white shadow-sm mb-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <form method="GET" class="flex items-center gap-2">
                <input name="q" value="{{ $q }}" placeholder="{{ __('Search name…') }}"
                    class="px-3 py-2 rounded-lg bg-white border border-gray-300 text-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <button class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-800">{{ __('Search') }}</button>
            </form>

            <a href="{{ route('case-types.create') }}"
                class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-medium">
                {{ __('Add Case Type') }}
            </a>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">{{ __('Name') }}</th>
                        <th class="px-4 py-3 text-left">{{ __('Cases Using') }}</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($types as $t)
                    <tr>
                        <td class="px-4 py-2 font-medium text-gray-900">{{ $t->name }}</td>
                        <td class="px-4 py-2 text-gray-700">{{ $t->cases_count }}</td>
                        <td class="px-4 py-2">
                            <div class="flex items-center gap-2 justify-end">
                                <a href="{{ route('case-types.edit', $t->id) }}"
                                    class="px-3 py-1.5 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-xs text-gray-700">
                                    {{ __('Edit') }}
                                </a>
                                <form method="POST" action="{{ route('case-types.delete', $t->id) }}"
                                    onsubmit="return confirm('{{ __('Delete this case type?') }}')">
                                    @csrf @method('DELETE')
                                    <button class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs">
                                        {{ __('Delete') }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-gray-500">
                            {{ __('No case types found.') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4">{{ $types->links() }}</div>
    </div>
</x-admin-layout>