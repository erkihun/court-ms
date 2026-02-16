<x-admin-layout title="{{ __('announcements.title') }}">
    @section('page_header', __('announcements.title'))

    <div class="p-6 bg-white rounded-xl border border-gray-200 space-y-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ __('announcements.title') }}</h1>
                <p class="text-sm text-gray-600 max-w-2xl">{{ __('announcements.manage_description') }}</p>
            </div>
            <a href="{{ route('announcements.create') }}"
                class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                {{ __('announcements.create_cta') }}
            </a>
        </div>

        @if(session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($announcements->isEmpty())
            <div class="text-center border border-dashed border-gray-300 rounded-lg p-10 text-gray-600">
                <p class="text-lg font-semibold text-gray-900">{{ __('announcements.empty_state') }}</p>
                <p class="mt-2 text-sm">{{ __('announcements.empty_state_help') }}</p>
                <div class="mt-4">
                    <a href="{{ route('announcements.create') }}"
                        class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        {{ __('announcements.create_cta') }}
                    </a>
                </div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-600">
                        <tr>
                            <th class="px-4 py-3">{{ __('announcements.table_title') }}</th>
                            <th class="px-4 py-3">{{ __('announcements.table_created') }}</th>
                            <th class="px-4 py-3">{{ __('announcements.table_updated') }}</th>
                            <th class="px-4 py-3">{{ __('announcements.form_status_label') }}</th>
                            <th class="px-4 py-3 text-right">{{ __('announcements.table_actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($announcements as $announcement)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-900">{{ $announcement->title }}</div>
                                    <div class="text-xs text-gray-500">{{ $announcement->created_at?->format('Y-m-d H:i') }}</div>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $announcement->created_at?->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $announcement->updated_at?->format('Y-m-d') }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusKey = $announcement->status ?? 'active';
                                        $statusLabelKey = 'announcements.status_' . $statusKey;
                                        $badgeClasses = $statusKey === 'active'
                                            ? 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100'
                                            : 'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-slate-50 text-slate-600 border border-slate-200';
                                    @endphp
                                    <span class="{{ $badgeClasses }}">
                                        {{ __($statusLabelKey) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-3">
                                        <a href="{{ route('announcements.show', $announcement) }}"
                                            class="text-blue-600 hover:text-blue-700">{{ __('announcements.action_view') }}</a>
                                        <a href="{{ route('announcements.edit', $announcement) }}"
                                            class="text-amber-600 hover:text-amber-700">{{ __('announcements.action_edit') }}</a>
                                        <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" class="inline"
                                            onsubmit="return confirm('{{ __('announcements.confirm_delete') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-700">
                                                {{ __('announcements.action_delete') }}
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $announcements->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
