<x-admin-layout title="System Audit">
    @section('page_header', 'System Audit')

    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-orange-50">
            <h1 class="text-2xl font-bold text-gray-900">System Audit</h1>
            <p class="text-sm text-gray-600">Recent system actions and audit notes.</p>
        </div>
        <div class="px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">Recent Activity (All Modules)</h2>
            @if(!empty($systemAuditsRecent) && count($systemAuditsRecent) > 0)
            <div class="mb-4 rounded-lg border border-blue-100 bg-blue-50/60 px-4 py-3 text-sm text-blue-900">
                <p class="font-semibold mb-2">Debug: Last 5 system audit rows</p>
                <ul class="space-y-1">
                    @foreach($systemAuditsRecent as $entry)
                    <li class="flex items-start gap-2">
                        <span class="text-[11px] text-blue-700">{{ \Carbon\Carbon::parse($entry->created_at)->format('Y-m-d H:i') }}</span>
                        <span class="font-semibold">{{ $entry->method }}</span>
                        <span class="text-gray-800">{{ $entry->route ?? '—' }}</span>
                        <span class="text-gray-600">{{ $entry->actor_name ?? ($entry->user_id ? 'User #'.$entry->user_id : '—') }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
            @if(!empty($combinedAudits) && count($combinedAudits) > 0)
            <div class="overflow-x-auto mb-4">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Time</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Route / Module</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Note</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($combinedAudits as $entry)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ \Carbon\Carbon::parse($entry->created_at)->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $entry->target ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 font-semibold">
                                {{ ucfirst($entry->action ?? 'event') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $entry->actor ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 max-w-xl break-words">
                                {{ $entry->note ?? '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-sm text-gray-600 mb-6">No audit activity logged yet.</p>
            @endif
        </div>

    </div>
</x-admin-layout>
