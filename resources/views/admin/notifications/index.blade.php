{{-- resources/views/admin/notifications/index.blade.php --}}
<x-admin-layout title="Notifications">
    @section('page_header', 'Notifications')

    <div class="space-y-6">
        {{-- Actions --}}
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900">Your notifications</h2>
            <form method="POST" action="{{ route('admin.notifications.markAll') }}">
                @csrf
                <button class="rounded-md border border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                    Mark all as seen
                </button>
            </form>
        </div>

        {{-- Applicant messages --}}
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-2">
                <h3 class="text-sm font-semibold text-gray-900">Applicant messages</h3>
                <span class="text-[11px] rounded-full bg-gray-100 px-2 py-0.5 text-gray-600">{{ $msgs->total() }}</span>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($msgs as $m)
                <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition-colors duration-150">
                    <a class="text-sm" href="{{ route('cases.show', $m->case_id) }}">
                        <div class="font-medium text-gray-900">{{ $m->case_number }}</div>
                        <div class="text-xs text-gray-600">
                            {{ \Illuminate\Support\Str::limit($m->body, 120) }}
                            · {{ \Illuminate\Support\Carbon::parse($m->created_at)->diffForHumans() }}
                        </div>
                    </a>
                    <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                        @csrf
                        <input type="hidden" name="type" value="message">
                        <input type="hidden" name="sourceId" value="{{ $m->id }}">
                        <button class="text-xs rounded border border-gray-300 bg-white px-2 py-1 text-gray-700 hover:bg-gray-50 transition-colors duration-200">Seen</button>
                    </form>
                </div>
                @empty
                <div class="px-4 py-6 text-sm text-gray-500">No new messages.</div>
                @endforelse
            </div>
            @if($msgs->hasPages())
            <div class="px-4 py-3">{{ $msgs->withQueryString()->links() }}</div>
            @endif
        </div>

        {{-- New cases --}}
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-2">
                <h3 class="text-sm font-semibold text-gray-900">New cases</h3>
                <span class="text-[11px] rounded-full bg-gray-100 px-2 py-0.5 text-gray-600">{{ $cases->total() }}</span>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($cases as $c)
                <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition-colors duration-150">
                    <a class="text-sm" href="{{ route('cases.show', $c->id) }}">
                        <div class="font-medium text-gray-900">{{ $c->case_number }}</div>
                        <div class="text-xs text-gray-600">
                            {{ \Illuminate\Support\Str::limit($c->title, 120) }}
                            · {{ \Illuminate\Support\Carbon::parse($c->created_at)->diffForHumans() }}
                        </div>
                    </a>
                    <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                        @csrf
                        <input type="hidden" name="type" value="case">
                        <input type="hidden" name="sourceId" value="{{ $c->id }}">
                        <button class="text-xs rounded border border-gray-300 bg-white px-2 py-1 text-gray-700 hover:bg-gray-50 transition-colors duration-200">Seen</button>
                    </form>
                </div>
                @empty
                <div class="px-4 py-6 text-sm text-gray-500">No new cases.</div>
                @endforelse
            </div>
            @if($cases->hasPages())
            <div class="px-4 py-3">{{ $cases->withQueryString()->links() }}</div>
            @endif
        </div>

        {{-- Upcoming hearings --}}
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-2">
                <h3 class="text-sm font-semibold text-gray-900">Upcoming hearings</h3>
                <span class="text-[11px] rounded-full bg-gray-100 px-2 py-0.5 text-gray-600">{{ $hearings->total() }}</span>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($hearings as $h)
                <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition-colors duration-150">
                    <a class="text-sm" href="{{ route('cases.show', $h->case_id) }}">
                        <div class="font-medium text-gray-900">
                            {{ $h->case_number }} — {{ \App\Support\EthiopianDate::format($h->hearing_at, withTime: true) }}
                        </div>
                        <div class="text-xs text-gray-600">
                            {{ optional($h)->type ?: 'Hearing' }} · {{ optional($h)->location ?: '—' }}
                        </div>
                    </a>
                    <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                        @csrf
                        <input type="hidden" name="type" value="hearing">
                        <input type="hidden" name="sourceId" value="{{ $h->id }}">
                        <button class="text-xs rounded border border-gray-300 bg-white px-2 py-1 text-gray-700 hover:bg-gray-50 transition-colors duration-200">Seen</button>
                    </form>
                </div>
                @empty
                <div class="px-4 py-6 text-sm text-gray-500">No upcoming hearings.</div>
                @endforelse
            </div>
            @if($hearings->hasPages())
            <div class="px-4 py-3">{{ $hearings->withQueryString()->links() }}</div>
            @endif
        </div>

        {{-- Respondent views --}}
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-2">
                <h3 class="text-sm font-semibold text-gray-900">Respondent views</h3>
                <span class="text-[11px] rounded-full bg-gray-100 px-2 py-0.5 text-gray-600">{{ $respondentViews->total() }}</span>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($respondentViews as $v)
                <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition-colors duration-150">
                    <a class="text-sm" href="{{ route('cases.show', $v->case_id) }}">
                        <div class="font-medium text-gray-900">{{ $v->case_number }}</div>
                        <div class="text-xs text-gray-600">
                            {{ $v->respondent_name ?: 'Respondent' }} viewed this case
                            A� {{ \Illuminate\Support\Carbon::parse($v->viewed_at)->diffForHumans() }}
                        </div>
                    </a>
                    <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                        @csrf
                        <input type="hidden" name="type" value="respondent_view">
                        <input type="hidden" name="sourceId" value="{{ $v->id }}">
                        <button class="text-xs rounded border border-gray-300 bg-white px-2 py-1 text-gray-700 hover:bg-gray-50 transition-colors duration-200">Seen</button>
                    </form>
                </div>
                @empty
                <div class="px-4 py-6 text-sm text-gray-500">No respondent views.</div>
                @endforelse
            </div>
            @if($respondentViews->hasPages())
            <div class="px-4 py-3">{{ $respondentViews->withQueryString()->links() }}</div>
            @endif
        </div>
    </div>
</x-admin-layout>
