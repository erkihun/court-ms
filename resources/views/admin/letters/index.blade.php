{{-- resources/views/letters/index.blade.php --}}
@php
use Illuminate\Support\Str;
$latestLetter = $letters->first();
@endphp
<x-admin-layout title="{{ __('letters.titles.index') }}">
    @section('page_header', __('letters.titles.index'))

    <div class="space-y-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ __('letters.titles.index') }}</h2>
                <p class="text-sm text-gray-500">{{ __('letters.description.index') }}</p>
            </div>
            <a href="{{ route('letters.compose') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('letters.actions.new_letter') }}
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('letters.cards.total_letters') }}</p>
                <p class="text-3xl font-semibold text-gray-900">
                    {{ $letters instanceof \Illuminate\Contracts\Pagination\Paginator ? $letters->total() : $letters->count() }}
                </p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('letters.cards.latest_created') }}</p>
                <p class="text-sm text-gray-900">
                    {{ optional($latestLetter)->subject ?? __('letters.cards.no_letters_short') }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ optional(optional($latestLetter)->created_at)->diffForHumans() ?? __('letters.cards.missing') }}
                </p>
            </div>
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4">
                <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('letters.cards.template_preview') }}</p>
                <p class="text-sm text-gray-900">
                    {{ optional(optional($latestLetter)->template)->title ?? __('letters.cards.add_first') }}
                </p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
            @if($letters->isEmpty())
            <div class="p-8 text-center text-gray-500 text-sm">{{ __('letters.cards.no_letters_yet') }}</div>
            @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left text-gray-600">
                            <th class="px-4 py-3 font-medium">{{ __('letters.table.subject') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('letters.table.reference') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('letters.table.recipient') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('letters.table.template') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('letters.table.cc') }}</th>
                            <th class="px-4 py-3 font-medium">{{ __('letters.table.created') }}</th>
                            <th class="px-4 py-3 font-medium text-right">{{ __('letters.table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($letters as $letter)
                        @php
                            $subjectLabel = Str::of($letter->subject ?: __('letters.cards.untitled'))->limit(60, '...');
                            $recipientLabel = Str::of($letter->recipient_name ?: __('letters.cards.missing'))->limit(40, '...');
                            $ccLabel = $letter->cc ? Str::of($letter->cc)->limit(40, '...') : __('letters.cards.cc_none');
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900" title="{{ $letter->subject ?? __('letters.cards.untitled') }}">
                                {{ $subjectLabel }}
                                <p class="text-xs text-gray-500 mt-0.5">{{ __('letters.cards.subject_line') }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                <span class="font-semibold text-xs text-gray-600">
                                    {{ $letter->reference_number ?? __('letters.cards.missing') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600" title="{{ $letter->recipient_name }}">
                                <strong>{{ $recipientLabel }}</strong>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $letter->recipient_title ?? __('letters.cards.recipient_fallback') }}</p>
                            </td>
                            <td class="px-4 py-3 text-gray-600" title="{{ optional($letter->template)->title }}">
                                {{ optional($letter->template)->title ?? __('letters.cards.missing') }}
                            </td>
                            <td class="px-4 py-3 text-gray-600" title="{{ $letter->cc ?? __('letters.cards.cc_none') }}">
                                {{ $ccLabel }}
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ optional($letter->created_at)->diffForHumans() }}</td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <button type="button"
                                        onclick="window.location='{{ route('letters.show', $letter) }}'"
                                        class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100"
                                        title="{{ __('letters.actions.view') }}">
                                        {{ __('letters.actions.view') }}
                                    </button>
                                    <button type="button"
                                        onclick="window.location='{{ route('letters.edit', $letter) }}'"
                                        class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100"
                                        title="{{ __('letters.actions.edit') }}">
                                        {{ __('letters.actions.edit') }}
                                    </button>
                                    <form method="POST" action="{{ route('letters.destroy', $letter) }}"
                                        onsubmit="return confirm('{{ __('letters.confirm.delete_letter') }}');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="px-3 py-1.5 text-xs font-medium rounded-lg border border-red-200 text-red-700 hover:bg-red-50">
                                            {{ __('letters.actions.delete') }}
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
