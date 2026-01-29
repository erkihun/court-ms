<x-admin-layout title="{{ __('about.title') }}">
    @section('page_header', __('about.title'))

    <div class="p-6 bg-white rounded-xl border border-gray-200">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ __('about.title') }}</h1>
                <p class="text-sm text-gray-600 max-w-2xl">{{ __('about.manage_description') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('about.create') }}"
                    class="px-4 py-2 rounded-md bg-blue-600 text-white text-sm hover:bg-blue-700">
                    {{ __('about.create_new') }}
                </a>
            </div>
        </div>

        @if($aboutPages->isEmpty())
        <div class="text-center py-10 border border-dashed border-gray-300 rounded-lg">
            <div class="text-lg font-semibold text-gray-900">{{ __('about.empty_state') }}</div>
            <p class="text-sm text-gray-600 mt-2">{{ __('about.empty_state_help') }}</p>
            <div class="mt-4">
                <a href="{{ route('about.create') }}"
                    class="inline-flex items-center px-4 py-2 rounded-md bg-blue-600 text-white text-sm hover:bg-blue-700">
                    {{ __('about.create_new') }}
                </a>
            </div>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">{{ __('about.table_title') }}</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">{{ __('about.table_status') }}</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">{{ __('about.table_updated') }}</th>
                        <th class="px-5 py-3 text-right font-semibold text-gray-700">{{ __('about.table_actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($aboutPages as $about)
                    <tr>
                        <td class="px-5 py-4 text-sm text-gray-900">
                            <div class="font-semibold">{{ $about->title }}</div>
                            <div class="text-xs text-gray-500">/{{ $about->slug }}</div>
                        </td>
                        <td class="px-5 py-4 text-sm">
                            @if($about->is_published)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700">
                                {{ __('about.status_published') }}
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                {{ __('about.status_draft') }}
                            </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-600">
                            {{ $about->updated_at?->format('Y-m-d H:i') }}
                        </td>
                        <td class="px-5 py-4 text-sm text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('about.show', $about) }}"
                                    class="text-blue-700 hover:text-blue-800 text-sm">
                                    {{ __('about.action_view') }}
                                </a>
                                <a href="{{ route('about.edit', $about) }}"
                                    class="text-amber-700 hover:text-amber-800 text-sm">
                                    {{ __('about.action_edit') }}
                                </a>
                                <form action="{{ route('about.destroy', $about) }}" method="POST" class="inline"
                                    onsubmit="return confirm('{{ __('about.confirm_delete') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-700 hover:text-red-800 text-sm">
                                        {{ __('about.action_delete') }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $aboutPages->links() }}
        </div>
        @endif
    </div>
</x-admin-layout>
