<x-admin-layout title="System Audit">
    @section('page_header', 'System Audit')

    <div class="space-y-5">
        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 bg-gradient-to-r from-blue-50 to-orange-50 px-6 py-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">End-to-End Audit Trail</h1>
                        <p class="mt-1 text-sm text-gray-600">
                            Requests, authentication, case activity and model changes in one controlled timeline.
                        </p>
                    </div>
                    <div class="rounded-xl border border-blue-200 bg-white px-4 py-2 text-sm text-gray-700">
                        <span class="font-semibold text-gray-900">{{ number_format($events->total()) }}</span>
                        matching events
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.audit') }}" class="grid gap-3 border-b border-gray-200 p-5 md:grid-cols-2 xl:grid-cols-4">
                <label class="xl:col-span-2">
                    <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Search</span>
                    <input name="search" value="{{ $filters['search'] ?? '' }}" maxlength="150"
                        placeholder="Action, route, target, request ID or details"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </label>

                <label>
                    <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Source</span>
                    <select name="source" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All sources</option>
                        <option value="system" @selected(($filters['source'] ?? '') === 'system')>System activity</option>
                        <option value="case" @selected(($filters['source'] ?? '') === 'case')>Case activity</option>
                        <option value="legacy_model" @selected(($filters['source'] ?? '') === 'legacy_model')>Model history</option>
                    </select>
                </label>

                <label>
                    <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Outcome</span>
                    <select name="outcome" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All outcomes</option>
                        @foreach(['success' => 'Success', 'failure' => 'Failure', 'error' => 'Error'] as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['outcome'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Actor type</span>
                    <select name="actor_type" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All actors</option>
                        @foreach(['user' => 'Staff user', 'applicant' => 'Applicant', 'respondent' => 'Respondent', 'guest' => 'Guest', 'system' => 'System'] as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['actor_type'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">Method</span>
                    <select name="method" class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">All methods</option>
                        @foreach(['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'] as $method)
                            <option value="{{ $method }}" @selected(($filters['method'] ?? '') === $method)>{{ $method }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">From</span>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </label>

                <label>
                    <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600">To</span>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                </label>

                <div class="flex items-end gap-2 md:col-span-2 xl:col-span-4">
                    <button type="submit" class="rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-800">
                        Apply filters
                    </button>
                    <a href="{{ route('admin.audit') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Clear
                    </a>
                </div>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-[1280px] w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Time / Source</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Action / Outcome</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Actor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Target / Route</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Request evidence</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($events as $event)
                            @php
                                $outcomeClass = match($event->outcome) {
                                    'success' => 'bg-emerald-100 text-emerald-800',
                                    'failure' => 'bg-amber-100 text-amber-800',
                                    default => 'bg-red-100 text-red-800',
                                };
                                $sourceClass = match($event->source) {
                                    'case' => 'bg-violet-100 text-violet-800',
                                    'legacy_model' => 'bg-slate-100 text-slate-700',
                                    default => 'bg-blue-100 text-blue-800',
                                };
                            @endphp
                            <tr class="align-top hover:bg-gray-50/70">
                                <td class="px-4 py-4 text-sm text-gray-700">
                                    <div class="whitespace-nowrap font-medium text-gray-900">
                                        {{ \App\Support\EthiopianDate::format($event->occurred_at, withTime: true) }}
                                    </div>
                                    <span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $sourceClass }}">
                                        {{ $event->source_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <div class="font-semibold text-gray-900">{{ $event->action_label }}</div>
                                    <div class="mt-1 max-w-sm text-xs leading-5 text-gray-600">{{ $event->description }}</div>
                                    <div class="mt-1 font-mono text-[11px] text-gray-400">{{ $event->action }}</div>
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        @if($event->method)
                                            <span class="rounded bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-700">{{ $event->method }}</span>
                                        @endif
                                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $outcomeClass }}">{{ ucfirst($event->outcome) }}</span>
                                        @if($event->response_status)
                                            <span class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-700">HTTP {{ $event->response_status }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700">
                                    <div class="font-medium text-gray-900">{{ $event->actor_name }}</div>
                                    <div class="mt-1 text-xs uppercase tracking-wide text-gray-500">{{ $event->actor_type }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700">
                                    <div class="font-medium text-gray-900">{{ $event->target_label }}</div>
                                    <div class="mt-1 max-w-xs break-all text-xs text-gray-500">{{ $event->route ?: 'Not route-bound' }}</div>
                                </td>
                                <td class="px-4 py-4 text-xs text-gray-600">
                                    <dl class="space-y-1.5">
                                        <div><dt class="inline font-semibold text-gray-700">Request:</dt> <dd class="inline break-all">{{ $event->request_id ?: 'Not available' }}</dd></div>
                                        <div><dt class="inline font-semibold text-gray-700">IP:</dt> <dd class="inline">{{ $event->ip ?: 'Not available' }}</dd></div>
                                        <div><dt class="inline font-semibold text-gray-700">Event:</dt> <dd class="inline">{{ $event->source }} #{{ $event->id }}</dd></div>
                                    </dl>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700">
                                    @if(count($event->details) > 0)
                                        <details class="max-w-md">
                                            <summary class="cursor-pointer font-semibold text-blue-700 hover:text-blue-900">View details</summary>
                                            <pre class="mt-2 max-h-72 overflow-auto whitespace-pre-wrap break-words rounded-lg bg-gray-950 p-3 text-xs leading-5 text-gray-100">{{ json_encode($event->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                        </details>
                                    @else
                                        <span class="text-gray-400">No additional details</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-14 text-center text-sm text-gray-600">
                                    No audit events match the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($events->hasPages())
                <div class="border-t border-gray-200 px-5 py-4">
                    {{ $events->links() }}
                </div>
            @endif
        </section>
    </div>
</x-admin-layout>
