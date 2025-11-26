{{-- resources/views/letters/index.blade.php --}}
<x-admin-layout title="Letters">
    @section('page_header','Letters')

    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Letters</h2>
                <p class="text-sm text-gray-500">Recently generated letters using saved templates.</p>
            </div>
            <a href="{{ route('letters.compose') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Letter
            </a>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            @if($letters->isEmpty())
            <div class="p-8 text-center text-gray-500 text-sm">No letters yet.</div>
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-gray-600">
                            <th class="px-4 py-3 font-medium">Subject</th>
                            <th class="px-4 py-3 font-medium">Recipient</th>
                            <th class="px-4 py-3 font-medium">Template</th>
                            <th class="px-4 py-3 font-medium">Created</th>
                            <th class="px-4 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($letters as $letter)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $letter->subject ?? 'Untitled Letter' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $letter->recipient_name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ optional($letter->template)->title ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ optional($letter->created_at)->diffForHumans() }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end">
                                    <a href="{{ route('letters.show', $letter) }}"
                                        class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100">
                                        View
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200">{!! $letters->links() !!}</div>
            @endif
        </div>
    </div>
</x-admin-layout>
