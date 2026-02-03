<x-admin-layout title="{{ __('case_inspections.title') }}">
    @section('page_header', __('case_inspections.title'))

    <div class="p-6 bg-white rounded-xl border border-gray-200">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ __('case_inspections.title') }}</h1>
                <p class="text-sm text-gray-600 max-w-2xl">{{ __('case_inspections.manage_description') }}</p>
            </div>
            <a href="{{ route('case-inspections.create') }}"
                class="px-4 py-2 rounded-md bg-blue-600 text-white text-sm hover:bg-blue-700">
                {{ __('case_inspections.create_new') }}
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Case Number</th>
                        <th class="px-5 py-3 text-left font-semibold text-gray-700">Note</th>
                        <th class="px-5 py-3 text-right font-semibold text-gray-700">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($cases as $case)
                    <tr>
                        <td class="px-5 py-4 text-sm text-gray-900">
                            {{ $case->case_number }}
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-700">
                            {{ \Illuminate\Support\Str::limit((string) ($case->notes ?? ''), 140) ?: '-' }}
                        </td>
                        <td class="px-5 py-4 text-sm text-right">
                            <a href="{{ route('case-inspections.create', ['case_id' => $case->id]) }}"
                                class="text-emerald-700 hover:text-emerald-800 text-sm">
                                Create inspection
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-5 py-6 text-center text-sm text-gray-500">
                            No assigned cases found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
