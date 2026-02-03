<x-admin-layout title="{{ __('case_inspections.title') }}">
    @section('page_header', __('case_inspections.title'))

    <div class="p-6 bg-white rounded-xl border border-gray-200 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $caseInspection->summary }}</h1>
                <p class="text-sm text-gray-500">
                    {{ $caseInspection->case?->case_number }} — {{ $caseInspection->case?->title }}
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('case-inspections.edit', $caseInspection) }}"
                    class="px-3 py-2 rounded-md bg-amber-600 text-white text-sm hover:bg-amber-700">
                    {{ __('case_inspections.action_edit') }}
                </a>
                <a href="{{ route('case-inspections.index') }}"
                    class="px-3 py-2 rounded-md border border-gray-300 text-gray-700 text-sm">
                    {{ __('case_inspections.form_cancel') }}
                </a>
            </div>
        </div>

        <div class="text-sm text-gray-600">
            {{ __('case_inspections.table_date') }}:
            <span class="text-gray-900">{{ optional($caseInspection->inspection_date)->format('Y-m-d') }}</span>
        </div>
        <div class="text-sm text-gray-600">
            {{ __('case_inspections.table_inspector') }}:
            <span class="text-gray-900">{{ $caseInspection->inspectedBy?->name ?? '-' }}</span>
        </div>

        <div class="prose max-w-none">
            {!! nl2br(e($caseInspection->details)) !!}
        </div>
    </div>
</x-admin-layout>
