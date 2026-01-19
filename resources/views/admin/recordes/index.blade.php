<x-admin-layout title="{{ __('recordes.titles.index') }}">
    <div class="bg-white shadow rounded-xl border border-slate-200 p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">{{ __('recordes.titles.index') }}</h1>
                <p class="text-sm text-slate-600">{{ __('recordes.descriptions.index_intro') }}</p>
            </div>
            <a href="{{ route('cases.index') }}" class="text-sm text-blue-600 hover:underline">
                {{ __('recordes.buttons.back_to_cases') }}
            </a>
        </div>

        @if($cases->isEmpty())
            <p class="text-sm text-slate-500">{{ __('recordes.messages.no_cases') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-slate-600">
                            <th class="py-2 pr-3">{{ __('recordes.labels.case_number_short') }}</th>
                            <th class="py-2 pr-3">{{ __('recordes.labels.title') }}</th>
                            <th class="py-2 pr-3">{{ __('recordes.labels.status') }}</th>
                            <th class="py-2 pr-3">{{ __('recordes.labels.filed') }}</th>
                            <th class="py-2 pr-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($cases as $c)
                            <tr>
                                <td class="py-2 pr-3 font-medium text-slate-900">{{ $c->case_number }}</td>
                                <td class="py-2 pr-3 text-slate-800">{{ \Illuminate\Support\Str::limit($c->title, 70) }}</td>
                                <td class="py-2 pr-3 text-slate-600">{{ $c->status }}</td>
                                <td class="py-2 pr-3 text-slate-600">
                                    {{ optional($c->filing_date)->toFormattedDateString() ?? '—' }}
                                </td>
                                <td class="py-2 pr-3 text-right space-x-2">
                                    <a class="inline-flex items-center justify-center rounded-md bg-blue-600 px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700" href="{{ route('recordes.show', $c) }}">
                                        {{ __('recordes.buttons.view') }}
                                    </a>
                                    <a class="inline-flex items-center justify-center rounded-md bg-blue-600 px-3 py-1 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700" href="{{ route('recordes.pdf', $c) }}">
                                        {{ __('recordes.buttons.pdf') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-admin-layout>
