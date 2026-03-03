<x-admin-layout title="{{ __('case_inspections.findings.show_title') }}">
    @section('page_header', __('case_inspections.findings.show_title'))
    @php $isAdmin = auth()->user()?->hasRole('admin') ?? false; @endphp

    <div class="p-6 bg-white rounded-xl border border-gray-200 space-y-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ $finding->title }}</h1>
                <p class="text-sm text-gray-600">{{ $finding->request?->case?->case_number }} - {{ $finding->request?->subject }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if($isAdmin && !$finding->accepted_at)
                <form method="POST" action="{{ route('case-inspection-findings.accept', $finding) }}" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-2 rounded-md bg-emerald-600 text-white text-sm hover:bg-emerald-700">{{ __('case_inspections.findings.accept') }}</button>
                </form>
                @endif
                @if(!$finding->accepted_at)
                <a href="{{ route('case-inspection-findings.edit', $finding) }}" class="px-3 py-2 rounded-md bg-amber-600 text-white text-sm hover:bg-amber-700">{{ __('case_inspections.common.edit') }}</a>
                <form method="POST" action="{{ route('case-inspection-findings.destroy', $finding) }}" class="inline" onsubmit="return confirm('{{ __('case_inspections.findings.confirm_delete') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-2 rounded-md bg-red-600 text-white text-sm hover:bg-red-700">{{ __('case_inspections.common.delete') }}</button>
                </form>
                @endif
                <a href="{{ route('case-inspection-findings.index') }}" class="px-3 py-2 rounded-md border border-gray-300 text-gray-700 text-sm">{{ __('case_inspections.common.back') }}</a>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4 text-sm">
            <div><span class="text-gray-500">{{ __('case_inspections.findings.labels.finding_date') }}:</span> <span class="text-gray-900">{{ optional($finding->finding_date)->format('Y-m-d') }}</span></div>
            <div><span class="text-gray-500">{{ __('case_inspections.findings.labels.severity') }}:</span> <span class="text-gray-900">{{ __('case_inspections.severity.' . $finding->severity) }}</span></div>
            <div><span class="text-gray-500">{{ __('case_inspections.findings.labels.recorded_by') }}:</span> <span class="text-gray-900">{{ $finding->recordedBy?->name ?? __('case_inspections.common.no_data') }}</span></div>
            <div>
                <span class="text-gray-500">{{ __('case_inspections.findings.labels.accepted') }}:</span>
                <span class="text-gray-900">
                    @if($finding->accepted_at)
                    {{ __('case_inspections.findings.accepted') }} ({{ $finding->acceptedBy?->name ?? __('case_inspections.common.no_data') }})
                    @else
                    {{ __('case_inspections.findings.not_accepted') }}
                    @endif
                </span>
            </div>
        </div>

        <div>
            <h2 class="font-semibold text-gray-900 mb-2">{{ __('case_inspections.findings.labels.details') }}</h2>
            <div class="prose max-w-none bg-gray-50 border border-gray-200 rounded-lg p-4">{!! nl2br(e($finding->details)) !!}</div>
        </div>

        <div>
            <h2 class="font-semibold text-gray-900 mb-2">{{ __('case_inspections.findings.labels.recommendation') }}</h2>
            <div class="prose max-w-none bg-gray-50 border border-gray-200 rounded-lg p-4">{!! nl2br(e($finding->recommendation ?: __('case_inspections.common.no_data'))) !!}</div>
        </div>
    </div>
</x-admin-layout>
