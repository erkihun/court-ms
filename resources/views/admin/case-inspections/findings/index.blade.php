<x-admin-layout title="{{ __('case_inspections.findings.index_title') }}">
    @section('page_header', __('case_inspections.findings.index_title'))
    @php $isAdmin = auth()->user()?->hasRole('admin') ?? false; @endphp

    <div class="p-6 bg-white rounded-xl border border-gray-200 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ __('case_inspections.findings.index_title') }}</h1>
                <p class="text-sm text-gray-600">{{ __('case_inspections.findings.index_description') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('case-inspection-findings.create') }}" class="px-4 py-2 rounded-md bg-blue-600 text-white text-sm hover:bg-blue-700">{{ __('case_inspections.findings.new') }}</a>
            </div>
        </div>

        <form method="GET" class="grid md:grid-cols-3 gap-3">
            @if(!empty($caseId))
            <input type="hidden" name="case_id" value="{{ $caseId }}">
            @endif
            <select name="request_id" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">{{ __('case_inspections.findings.all_requests') }}</option>
                @foreach($requests as $req)
                <option value="{{ $req->id }}" @selected((string) $requestId === (string) $req->id)>{{ $req->case?->case_number }} - {{ $req->subject }}</option>
                @endforeach
            </select>
            <select name="severity" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="">{{ __('case_inspections.findings.all_severities') }}</option>
                @foreach(['low', 'medium', 'high', 'critical'] as $k)
                <option value="{{ $k }}" @selected($severity === $k)>{{ __('case_inspections.severity.' . $k) }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 rounded-md border border-gray-300 text-gray-700 text-sm hover:bg-gray-50">{{ __('case_inspections.common.filter') }}</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('case_inspections.findings.table_case') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('case_inspections.findings.table_request') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('case_inspections.findings.table_title') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('case_inspections.findings.table_date') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">{{ __('case_inspections.findings.table_severity') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">{{ __('case_inspections.common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($findings as $finding)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $finding->request?->case?->case_number ?? __('case_inspections.common.no_data') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $finding->request?->subject ?? __('case_inspections.common.no_data') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ $finding->title }}
                            @if($finding->accepted_at)
                            <span class="ml-2 inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">{{ __('case_inspections.findings.accepted') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ \App\Support\EthiopianDate::format($finding->finding_date) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ __('case_inspections.severity.' . $finding->severity) }}</td>
                        <td class="px-4 py-3 text-sm text-right space-x-2">
                            @if(!$finding->accepted_at)
                            <a href="{{ route('case-inspection-findings.edit', $finding) }}" class="text-amber-700 hover:text-amber-800">{{ __('case_inspections.common.edit') }}</a>
                            @endif
                            <a href="{{ route('case-inspection-findings.show', $finding) }}" class="text-blue-700 hover:text-blue-800">{{ __('case_inspections.common.view') }}</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">{{ __('case_inspections.findings.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $findings->links() }}
    </div>
</x-admin-layout>
