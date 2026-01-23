{{-- resources/views/admin/hearings/index.blade.php --}}
<x-admin-layout title="Hearings">
    @section('page_header', __('app.Hearings'))

    <div class="space-y-6" x-data="hearingViewer()" x-cloak>
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-gray-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('app.Hearings') }}</h2>
                    <p class="text-sm text-gray-500">Browse every scheduled hearing recorded in Court-MS.</p>
                </div>
                <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                    {{ $hearings->total() }} {{ __('app.Hearings') }}
                </span>
            </div>

            <div class="px-6 py-4">
                <form class="space-y-3 sm:space-y-0 sm:flex sm:gap-3" method="GET">
                    <div class="flex-1 min-w-0">
                        <label class="sr-only" for="case_number">Case number</label>
                        <input id="case_number" name="case_number" value="{{ old('case_number', $caseNumber) }}"
                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200"
                            placeholder="Filter by case number">
                    </div>
                    <div class="flex-1 min-w-0">
                        <label class="sr-only" for="hearing_date">Hearing date</label>
                        <input id="hearing_date" type="date" name="hearing_date" value="{{ old('hearing_date', $hearingDate) }}"
                            class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200">
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit" class="inline-flex items-center rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring focus:ring-blue-200">
                            Apply filters
                        </button>
                        <a href="{{ route('admin.hearings.index') }}"
                            class="text-xs font-semibold uppercase tracking-wide text-gray-500 hover:text-gray-700">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th scope="col" class="px-6 py-3">#</th>
                            <th scope="col" class="px-6 py-3">Case</th>
                            <th scope="col" class="px-6 py-3">Date &amp; time</th>
                            <th scope="col" class="px-6 py-3">Hearing name</th>
                            <th scope="col" class="px-6 py-3">Created by</th>
                            <th scope="col" class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($hearings as $hearing)
                        <tr class="bg-white">
                            <td class="px-6 py-4 text-sm font-medium text-gray-500">
                                {{ $loop->iteration + (($hearings->currentPage() - 1) * $hearings->perPage()) }}
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                <div>{{ $hearing->case_number ?? '—' }}</div>
                                <div class="text-xs text-gray-500">{{ $hearing->title ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                {{ $hearing->hearing_at
                                    ? \App\Support\EthiopianDate::format($hearing->hearing_at, withTime: true)
                                    : '—' }}
                            </td>
                            <td class="px-6 py-4">{{ $hearing->type ?? 'Hearing' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $hearing->creator_name ?? '—' }}</td>
                            <td class="px-6 py-4 text-right">
                                <button
                                    type="button"
                                    class="text-xs font-semibold uppercase tracking-wide px-3 py-1 rounded-full transition focus-ring"
                                    :class="activeId === {{ $hearing->id }} ? 'bg-blue-600 text-white shadow-sm' : 'text-blue-600 hover:text-blue-900 bg-blue-50'"
                                    @click="openDetails({{ json_encode([
                                        'id' => $hearing->id,
                                        'case_number' => $hearing->case_number,
                                        'title' => $hearing->title,
                                        'type' => $hearing->type,
                                        'location' => $hearing->location,
                                        'notes' => $hearing->notes,
                                        'hearing_at' => $hearing->hearing_at ? \App\Support\EthiopianDate::format($hearing->hearing_at, withTime: true) : null,
                                        'creator' => $hearing->creator_name,
                                    ]) }}"
                                >
                                    View
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">
                                {{ __('cases.no_hearings_scheduled') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($hearings->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $hearings->withQueryString()->links() }}
            </div>
            @endif
        </div>

        <div
            x-show="open"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 p-4"
            x-transition.opacity>
            <div class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl" @click.outside="close()">
                <header class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900" x-text="payload?.type ?? 'Hearing Details'"></h3>
                        <p class="text-sm text-gray-500" x-text="payload?.case_number ? `Case ${payload.case_number}` : ''"></p>
                    </div>
                    <button type="button" class="text-gray-400 hover:text-gray-600" @click="close()">&times;</button>
                </header>
                <div class="mt-4 space-y-3 text-sm text-gray-700">
                    <div>
                        <span class="block text-xs uppercase tracking-wide text-gray-400">Title</span>
                        <p x-text="payload?.title ?? '—'"></p>
                    </div>
                    <div>
                        <span class="block text-xs uppercase tracking-wide text-gray-400">Scheduled at</span>
                        <p x-text="payload?.hearing_at ?? '—'"></p>
                    </div>
                    <div>
                        <span class="block text-xs uppercase tracking-wide text-gray-400">Location</span>
                        <p x-text="payload?.location ?? '—'"></p>
                    </div>
                    <div>
                        <span class="block text-xs uppercase tracking-wide text-gray-400">Notes</span>
                        <p x-text="payload?.notes ?? '—'"></p>
                    </div>
                    <div>
                        <span class="block text-xs uppercase tracking-wide text-gray-400">Created by</span>
                        <p x-text="payload?.creator ?? '—'"></p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function hearingViewer() {
            return {
                open: false,
                payload: null,
                activeId: null,
                openDetails(payload) {
                    this.payload = payload;
                    this.activeId = payload.id;
                    this.open = true;
                },
                close() {
                    this.open = false;
                    this.payload = null;
                    this.activeId = null;
                }
            };
        }
    </script>
    @endpush
</x-admin-layout>
