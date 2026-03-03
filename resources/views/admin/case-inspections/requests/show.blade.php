<x-admin-layout title="{{ __('case_inspections.requests.show_title') }}">
    @section('page_header', __('case_inspections.requests.show_title'))

    <div class="p-6 bg-white rounded-xl border border-gray-200 space-y-4">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ $requestRecord->subject }}</h1>
                <p class="text-sm text-gray-600">{{ $requestRecord->case?->case_number }} - {{ $requestRecord->case?->title }}</p>
            </div>
            <div class="flex items-center gap-2">
                @if($requestRecord->status !== 'completed')
                <a href="{{ route('case-inspection-requests.edit', $requestRecord) }}" class="px-3 py-2 rounded-md bg-amber-600 text-white text-sm hover:bg-amber-700">{{ __('case_inspections.common.edit') }}</a>
                <form method="POST" action="{{ route('case-inspection-requests.destroy', $requestRecord) }}" class="inline" onsubmit="return confirm('{{ __('case_inspections.requests.confirm_delete') }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-2 rounded-md bg-red-600 text-white text-sm hover:bg-red-700">{{ __('case_inspections.common.delete') }}</button>
                </form>
                @endif
                <a href="{{ route('case-inspection-requests.index') }}" class="px-3 py-2 rounded-md border border-gray-300 text-gray-700 text-sm">{{ __('case_inspections.common.back') }}</a>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4 text-sm">
            <div><span class="text-gray-500">{{ __('case_inspections.requests.labels.request_date') }}:</span> <span class="text-gray-900">{{ optional($requestRecord->request_date)->format('Y-m-d') }}</span></div>
            <div><span class="text-gray-500">{{ __('case_inspections.requests.labels.status') }}:</span> <span class="text-gray-900">{{ __('case_inspections.status.' . $requestRecord->status) }}</span></div>
            <div><span class="text-gray-500">{{ __('case_inspections.requests.labels.inspector') }}:</span> <span class="text-gray-900">{{ $requestRecord->assignedInspector?->name ?? __('case_inspections.common.no_data') }}</span></div>
            <div><span class="text-gray-500">{{ __('case_inspections.requests.labels.created_by') }}:</span> <span class="text-gray-900">{{ $requestRecord->createdBy?->name ?? __('case_inspections.common.no_data') }}</span></div>
        </div>

        <div class="prose max-w-none bg-gray-50 border border-gray-200 rounded-lg p-4">{!! nl2br(e($requestRecord->request_note ?? __('case_inspections.common.no_data'))) !!}</div>

        <div class="border-t border-gray-200 pt-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">{{ __('case_inspections.requests.labels.findings') }}</h2>
            <div class="space-y-2">
                @forelse($requestRecord->findings as $finding)
                <div class="p-3 rounded-lg border border-gray-200 bg-white flex items-center justify-between">
                    <div>
                        <div class="font-medium text-gray-900">{{ $finding->title }}</div>
                        <div class="text-xs text-gray-500">{{ optional($finding->finding_date)->format('Y-m-d') }} | {{ __('case_inspections.severity.' . $finding->severity) }}</div>
                    </div>
                    <a href="{{ route('case-inspection-findings.show', $finding) }}" class="text-blue-700 hover:text-blue-800 text-sm">{{ __('case_inspections.common.view') }}</a>
                </div>
                @empty
                <p class="text-sm text-gray-500">{{ __('case_inspections.requests.labels.no_findings') }}</p>
                @endforelse
            </div>
        </div>
    </div>
</x-admin-layout>
