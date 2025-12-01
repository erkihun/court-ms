{{-- resources/views/apply/notifications/index.blade.php --}}
<x-applicant-layout title="Notifications">
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-lg font-semibold">Notifications</h1>
        <form method="POST" action="{{ route('applicant.notifications.markAll') }}">
            @csrf
            <button class="text-xs px-3 py-1.5 rounded-md border hover:bg-slate-50">Mark all as seen</button>
        </form>
    </div>

    {{-- Hearings --}}
    <div class="rounded-lg border bg-white p-4">
        <div class="mb-2 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">Hearings</h2>
            <span class="text-[11px] rounded-full bg-slate-100 px-2 py-0.5 text-slate-700">
                {{ $unseenHearings->total() }}
            </span>
        </div>

        @if($unseenHearings->count())
        <ul class="divide-y">
            @foreach($unseenHearings as $h)
            <li class="py-2 flex items-center justify-between">
                <a href="{{ route('applicant.cases.show', $h->case_id) }}" class="text-sm">
                    <div class="font-medium text-slate-800">
                        {{ $h->case_number }} — {{ \Illuminate\Support\Carbon::parse($h->hearing_at)->format('M d, Y H:i') }}
                    </div>
                    <div class="text-xs text-slate-500">{{ $h->type ?: 'Hearing' }} · {{ $h->location ?: '—' }}</div>
                </a>
                <form method="POST" action="{{ route('applicant.notifications.markOne') }}">
                    @csrf
                    <input type="hidden" name="type" value="hearing">
                    <input type="hidden" name="sourceId" value="{{ $h->id }}">
                    <button class="text-xs px-2 py-1 rounded border hover:bg-slate-50">Seen</button>
                </form>
            </li>
            @endforeach
        </ul>
        <div class="mt-3">{{ $unseenHearings->onEachSide(0)->links() }}</div>
        @else
        <div class="text-sm text-slate-500">No new hearings.</div>
        @endif
    </div>

    {{-- Messages --}}
    <div class="mt-6 rounded-lg border bg-white p-4">
        <div class="mb-2 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">Messages</h2>
            <span class="text-[11px] rounded-full bg-slate-100 px-2 py-0.5 text-slate-700">
                {{ $unseenMsgs->total() }}
            </span>
        </div>

        @if($unseenMsgs->count())
        <ul class="divide-y">
            @foreach($unseenMsgs as $m)
            <li class="py-2 flex items-center justify-between">
                <a href="{{ route('applicant.cases.show', $m->case_id) }}" class="text-sm">
                    <div class="font-medium text-slate-800">{{ $m->case_number }}</div>
                    <div class="text-xs text-slate-500">
                        {{ \Illuminate\Support\Str::limit($m->body, 80) }}
                        · {{ \Illuminate\Support\Carbon::parse($m->created_at)->diffForHumans() }}
                    </div>
                </a>
                <form method="POST" action="{{ route('applicant.notifications.markOne') }}">
                    @csrf
                    <input type="hidden" name="type" value="message">
                    <input type="hidden" name="sourceId" value="{{ $m->id }}">
                    <button class="text-xs px-2 py-1 rounded border hover:bg-slate-50">Seen</button>
                </form>
            </li>
            @endforeach
        </ul>
        <div class="mt-3">{{ $unseenMsgs->onEachSide(0)->links() }}</div>
        @else
        <div class="text-sm text-slate-500">No new messages.</div>
        @endif
    </div>

    {{-- Respondent views --}}
    <div class="mt-6 rounded-lg border bg-white p-4">
        <div class="mb-2 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">Respondent viewed case</h2>
            <span class="text-[11px] rounded-full bg-slate-100 px-2 py-0.5 text-slate-700">
                {{ $respondentViews->total() }}
            </span>
        </div>

        @if($respondentViews->count())
        <ul class="divide-y">
            @foreach($respondentViews as $v)
            <li class="py-2 flex items-center justify-between">
                <a href="{{ route('applicant.cases.show', $v->case_id) }}" class="text-sm">
                    <div class="font-medium text-slate-800">
                        {{ $v->case_number }}
                    </div>
                    <div class="text-xs text-slate-500">
                        {{ $v->respondent_name ?: 'Respondent' }}
                        viewed this case
                        A� {{ \Illuminate\Support\Carbon::parse($v->viewed_at)->diffForHumans() }}
                    </div>
                </a>
                <form method="POST" action="{{ route('applicant.notifications.markOne') }}">
                    @csrf
                    <input type="hidden" name="type" value="respondent_view">
                    <input type="hidden" name="sourceId" value="{{ $v->id }}">
                    <button class="text-xs px-2 py-1 rounded border hover:bg-slate-50">Seen</button>
                </form>
            </li>
            @endforeach
        </ul>
        <div class="mt-3">{{ $respondentViews->onEachSide(0)->links() }}</div>
        @else
        <div class="text-sm text-slate-500">No recent respondent views.</div>
        @endif
    </div>

    {{-- Status --}}
    <div class="mt-6 rounded-lg border bg-white p-4">
        <div class="mb-2 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">Status updates</h2>
            <span class="text-[11px] rounded-full bg-slate-100 px-2 py-0.5 text-slate-700">
                {{ $unseenStatus->total() }}
            </span>
        </div>

        @if($unseenStatus->count())
        <ul class="divide-y">
            @foreach($unseenStatus as $s)
            <li class="py-2 flex items-center justify-between">
                <a href="{{ route('applicant.cases.show', $s->case_id) }}" class="text-sm">
                    <div class="font-medium text-slate-800">{{ $s->case_number }}</div>
                    <div class="text-xs text-slate-500">
                        {{ ucfirst($s->from_status) }} → <strong>{{ ucfirst($s->to_status) }}</strong>
                        · {{ \Illuminate\Support\Carbon::parse($s->created_at)->diffForHumans() }}
                    </div>
                </a>
                <form method="POST" action="{{ route('applicant.notifications.markOne') }}">
                    @csrf
                    <input type="hidden" name="type" value="status">
                    <input type="hidden" name="sourceId" value="{{ $s->id }}">
                    <button class="text-xs px-2 py-1 rounded border hover:bg-slate-50">Seen</button>
                </form>
            </li>
            @endforeach
        </ul>
        <div class="mt-3">{{ $unseenStatus->onEachSide(0)->links() }}</div>
        @else
        <div class="text-sm text-slate-500">No new status updates.</div>
        @endif
    </div>
</x-applicant-layout>
