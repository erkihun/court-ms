{{-- resources/views/letter-templates/index.blade.php --}}
@php use Illuminate\Support\Str; @endphp

<x-admin-layout title="Letter Templates">
    @section('page_header','Letter Templates')

    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Letter Templates</h2>
                <p class="text-sm text-gray-500">Create reusable letters with placeholder tags.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('letter-templates.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Template
                </a>
                <a href="{{ route('letters.compose') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16h8M8 12h8m-5-8h5l3 3v11a2 2 0 01-2 2H8a2 2 0 01-2-2V5a2 2 0 012-2h3z" />
                    </svg>
                    Compose Letter
                </a>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            @if($templates->isEmpty())
            <div class="p-8 text-center text-gray-500 text-sm">
                No letter templates yet. Click "New Template" to create one.
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-gray-600">
                            <th class="px-4 py-3 font-medium">Title</th>
                            <th class="px-4 py-3 font-medium">Category</th>
                            <th class="px-4 py-3 font-medium">Placeholders</th>
                            <th class="px-4 py-3 font-medium">Updated</th>
                            <th class="px-4 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($templates as $template)
                        @php
                            $placeholderList = $template->placeholders ? implode(', ', $template->placeholders) : '';
                            $placeholderDisplay = $placeholderList ? Str::limit($placeholderList, 60, '…') : 'None';
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $template->title }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $template->category ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                <span class="text-xs text-gray-500"
                                    title="{{ $placeholderList ?: 'None' }}">
                                    {{ $placeholderDisplay }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ optional($template->updated_at)->diffForHumans() }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('letter-templates.edit', $template) }}"
                                        class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('letter-templates.destroy', $template) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="return confirm('Delete this template?')"
                                            class="px-3 py-1.5 text-xs font-medium rounded-lg border border-red-200 text-red-700 hover:bg-red-50">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200">{!! $templates->links() !!}</div>
            @endif
        </div>
    </div>
</x-admin-layout>
