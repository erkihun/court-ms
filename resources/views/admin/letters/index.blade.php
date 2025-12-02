{{-- resources/views/letters/index.blade.php --}}
@php
use Illuminate\Support\Str;
$latestLetter = $letters->first();
@endphp
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

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">Total letters</p>
                <p class="text-3xl font-semibold text-gray-900">
                    {{ $letters instanceof \Illuminate\Contracts\Pagination\Paginator ? $letters->total() : $letters->count() }}
                </p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">Latest created</p>
                <p class="text-sm text-gray-900">
                    {{ optional($latestLetter)->subject ?? 'No letters yet' }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ optional(optional($latestLetter)->created_at)->diffForHumans() ?? '—' }}
                </p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">Template preview</p>
                <p class="text-sm text-gray-900">
                    {{ optional(optional($latestLetter)->template)->title ?? 'Add your first letter' }}
                </p>
            </div>
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
                            <th class="px-4 py-3 font-medium">Reference</th>
                            <th class="px-4 py-3 font-medium">Recipient</th>
                            <th class="px-4 py-3 font-medium">Template</th>
                            <th class="px-4 py-3 font-medium">CC</th>
                            <th class="px-4 py-3 font-medium">Created</th>
                            <th class="px-4 py-3 font-medium text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($letters as $letter)
                        @php
                            $subjectLabel = Str::of($letter->subject ?: 'Untitled Letter')->limit(60, '…');
                            $recipientLabel = Str::of($letter->recipient_name ?: '—')->limit(40, '…');
                            $ccLabel = $letter->cc ? Str::of($letter->cc)->limit(40, '…') : '—';
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900" title="{{ $letter->subject ?? 'Untitled Letter' }}">
                                {{ $subjectLabel }}
                                <p class="text-xs text-gray-500 mt-0.5">Subject line</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                <span class="font-semibold text-xs text-gray-600">
                                    {{ $letter->reference_number ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600" title="{{ $letter->recipient_name }}">
                                <strong>{{ $recipientLabel }}</strong>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $letter->recipient_title ?? 'Recipient' }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600" title="{{ optional($letter->template)->title }}">
                                {{ optional($letter->template)->title ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600" title="{{ $letter->cc ?? 'None' }}">
                                {{ $ccLabel }}
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ optional($letter->created_at)->diffForHumans() }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <button type="button"
                                        onclick="window.location='{{ route('letters.show', $letter) }}'"
                                        class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100"
                                        title="Open this letter">
                                        View
                                    </button>
                                    <button type="button"
                                        onclick="window.location='{{ route('letters.edit', $letter) }}'"
                                        class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100"
                                        title="Edit letter">
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('letters.destroy', $letter) }}"
                                        onsubmit="return confirm('Delete this letter permanently?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
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
            <div class="px-4 py-3 border-t border-gray-200">{!! $letters->links() !!}</div>
            @endif
        </div>
    </div>
</x-admin-layout>
