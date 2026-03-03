<x-admin-layout title="{{ __('case_inspections.findings.create_title') }}">
    @section('page_header', __('case_inspections.findings.create_title'))

    <div class="p-6 bg-white rounded-xl border border-gray-200">
        <h1 class="text-2xl font-semibold text-gray-900 mb-4">{{ __('case_inspections.findings.create_title') }}</h1>

        <form method="POST" action="{{ route('case-inspection-findings.store') }}" class="space-y-6">
            @include('admin.case-inspections.findings._form', ['finding' => null])

            <div class="flex items-center gap-3">
                <a href="{{ route('case-inspection-findings.index') }}" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 text-sm">{{ __('case_inspections.common.cancel') }}</a>
                <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 text-white text-sm hover:bg-blue-700">{{ __('case_inspections.findings.save') }}</button>
            </div>
        </form>
    </div>
</x-admin-layout>
