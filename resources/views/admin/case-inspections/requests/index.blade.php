<x-admin-layout title="{{ __('case_inspections.requests.index_title') }}">
    @section('page_header', __('case_inspections.requests.index_title'))

    <div class="p-6 bg-white rounded-xl border border-gray-200 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ __('case_inspections.requests.index_title') }}</h1>
                <p class="text-sm text-gray-600">{{ __('case_inspections.requests.index_description') }}</p>
            </div>
        </div>

        <form method="GET" class="grid md:grid-cols-3 gap-3">
            <select name="case_id" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">{{ __('case_inspections.requests.all_cases') }}</option>
                @foreach($cases as $case)
                <option value="{{ $case->id }}" @selected((string) $caseId === (string) $case->id)>{{ $case->case_number }} - {{ $case->title }}</option>
                @endforeach
            </select>
            <select name="status" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">{{ __('case_inspections.requests.all_statuses') }}</option>
                @foreach(['pending', 'in_progress', 'completed', 'cancelled'] as $k)
                <option value="{{ $k }}" @selected($status === $k)>{{ __('case_inspections.status.' . $k) }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 text-sm hover:bg-gray-50">{{ __('case_inspections.common.filter') }}</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('case_inspections.requests.table_case') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('case_inspections.requests.table_subject') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('case_inspections.requests.table_date') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('case_inspections.requests.table_status') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('case_inspections.requests.table_inspector') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('case_inspections.requests.table_created_by') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">{{ __('case_inspections.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($requests as $req)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $req->case?->case_number ?? __('case_inspections.common.no_data') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $req->subject }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ \App\Support\EthiopianDate::format($req->request_date) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ __('case_inspections.status.' . $req->status) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $req->assignedInspector?->name ?? __('case_inspections.common.no_data') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $req->createdBy?->name ?? __('case_inspections.common.no_data') }}</td>
                        <td class="px-4 py-3 text-sm text-right space-x-2">
                            @if($req->status !== 'completed')
                            <a href="{{ route('case-inspection-requests.edit', $req) }}" class="text-amber-700 hover:text-amber-800">{{ __('case_inspections.common.edit') }}</a>
                            @endif
                            <a href="{{ route('case-inspection-requests.show', $req) }}" class="text-blue-700 hover:text-blue-800">{{ __('case_inspections.common.view') }}</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">{{ __('case_inspections.requests.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $requests->links() }}
    </div>
</x-admin-layout>
