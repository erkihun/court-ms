{{-- resources/views/partials/applicant-notifications.blade.php --}}
@php
$aid = auth('applicant')->id();
if (!$aid) {
// Not signed in — render a friendly note
echo '<div class="p-4 text-sm text-slate-600">Please sign in to see notifications.</div>';
return;
}

$now = now();

// Unseen hearings (from yesterday up to next 60 days)
$unseenHearings = \DB::table('case_hearings as h')
->join('court_cases as c', 'c.id', '=', 'h.case_id')
->select('h.id','h.hearing_at','h.location','h.type','c.id as case_id','c.case_number')
->where('c.applicant_id', $aid)
->whereBetween('h.hearing_at', [now()->subDay(), now()->addDays(60)])
->whereNotExists(function($q) use ($aid){
$q->from('notification_reads as nr')
->whereColumn('nr.source_id','h.id')
->where('nr.type','hearing')
->where('nr.applicant_id',$aid);
})
->orderBy('h.hearing_at')
->limit(10)
->get();

// Unseen admin messages (last 14 days)
$unseenMsgs = \DB::table('case_messages as m')
->join('court_cases as c', 'c.id', '=', 'm.case_id')
->select('m.id','m.body','m.created_at','c.id as case_id','c.case_number')
->whereNotNull('m.sender_user_id')
->where('c.applicant_id', $aid)
->where('m.created_at','>=', now()->subDays(14))
->whereNotExists(function($q) use ($aid){
$q->from('notification_reads as nr')
->whereColumn('nr.source_id','m.id')
->where('nr.type','message')
->where('nr.applicant_id',$aid);
})
->orderByDesc('m.created_at')
->limit(10)
->get();

// Unseen status changes (last 14 days)
$unseenStatus = \DB::table('case_status_logs as l')
->join('court_cases as c', 'c.id', '=', 'l.case_id')
->select('l.id','l.from_status','l.to_status','l.created_at','c.id as case_id','c.case_number')
->where('c.applicant_id', $aid)
->where('l.created_at','>=', now()->subDays(14))
->whereNotExists(function($q) use ($aid){
$q->from('notification_reads as nr')
->whereColumn('nr.source_id','l.id')
->where('nr.type','status')
->where('nr.applicant_id',$aid);
})
->orderByDesc('l.created_at')
->limit(10)
->get();

$hasAny = $unseenHearings->isNotEmpty() || $unseenMsgs->isNotEmpty() || $unseenStatus->isNotEmpty();
@endphp

<div class="p-3">
    <div class="flex items-center justify-between">
        <div class="text-sm font-semibold text-slate-700">Notifications</div>

        @if($hasAny)
        <form method="POST" action="{{ route('applicant.notifications.markAll') }}">
            @csrf
            <button class="text-xs px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">
                Mark all as seen
            </button>
        </form>
        @endif
    </div>

    @unless($hasAny)
    <div class="text-sm text-slate-500 mt-3">You’re all caught up 🎉</div>
    @endunless

    {{-- Hearings --}}
    @if($unseenHearings->isNotEmpty())
    <div class="mt-3">
        <div class="mb-1 flex items-center justify-between">
            <div class="text-xs font-medium text-slate-500">Hearings</div>
            <span class="text-[11px] rounded-full bg-slate-100 px-2 py-0.5 text-slate-700">
                {{ $unseenHearings->count() }}
            </span>
        </div>
        <ul class="divide-y">
            @foreach($unseenHearings as $h)
            <li class="py-2 flex items-center justify-between gap-3">
                <a href="{{ route('applicant.cases.show', $h->case_id) }}" class="text-sm flex-1">
                    <div class="font-medium text-slate-800">
                        {{ $h->case_number }}
                        <span class="text-slate-400">·</span>
                        {{ \Illuminate\Support\Carbon::parse($h->hearing_at)->format('M d, Y H:i') }}
                    </div>
                    <div class="text-xs text-slate-500">
                        {{ $h->type ?: 'Hearing' }}
                        @if($h->location)
                        <span class="text-slate-400">·</span> {{ $h->location }}
                        @endif
                    </div>
                </a>
                <form method="POST"
                    action="{{ route('applicant.notifications.markOne', ['type'=>'hearing','sourceId'=>$h->id]) }}">
                    @csrf
                    <button class="text-xs px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">
                        Seen
                    </button>
                </form>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Messages --}}
    @if($unseenMsgs->isNotEmpty())
    <div class="mt-4">
        <div class="mb-1 flex items-center justify-between">
            <div class="text-xs font-medium text-slate-500">Messages</div>
            <span class="text-[11px] rounded-full bg-slate-100 px-2 py-0.5 text-slate-700">
                {{ $unseenMsgs->count() }}
            </span>
        </div>
        <ul class="divide-y">
            @foreach($unseenMsgs as $m)
            <li class="py-2 flex items-center justify-between gap-3">
                <a href="{{ route('applicant.cases.show', $m->case_id) }}" class="text-sm flex-1">
                    <div class="font-medium text-slate-800">{{ $m->case_number }}</div>
                    <div class="text-xs text-slate-500">
                        {{ \Illuminate\Support\Str::limit($m->body, 80) }}
                        <span class="text-slate-400">·</span>
                        {{ \Illuminate\Support\Carbon::parse($m->created_at)->diffForHumans() }}
                    </div>
                </a>
                <form method="POST"
                    action="{{ route('applicant.notifications.markOne', ['type'=>'message','sourceId'=>$m->id]) }}">
                    @csrf
                    <button class="text-xs px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">
                        Seen
                    </button>
                </form>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Status updates --}}
    @if($unseenStatus->isNotEmpty())
    <div class="mt-4">
        <div class="mb-1 flex items-center justify-between">
            <div class="text-xs font-medium text-slate-500">Status updates</div>
            <span class="text-[11px] rounded-full bg-slate-100 px-2 py-0.5 text-slate-700">
                {{ $unseenStatus->count() }}
            </span>
        </div>
        <ul class="divide-y">
            @foreach($unseenStatus as $s)
            <li class="py-2 flex items-center justify-between gap-3">
                <a href="{{ route('applicant.cases.show', $s->case_id) }}" class="text-sm flex-1">
                    <div class="font-medium text-slate-800">{{ $s->case_number }}</div>
                    <div class="text-xs text-slate-500">
                        {{ ucfirst($s->from_status) }} → <strong>{{ ucfirst($s->to_status) }}</strong>
                        <span class="text-slate-400">·</span>
                        {{ \Illuminate\Support\Carbon::parse($s->created_at)->diffForHumans() }}
                    </div>
                </a>
                <form method="POST"
                    action="{{ route('applicant.notifications.markOne', ['type'=>'status','sourceId'=>$s->id]) }}">
                    @csrf
                    <button class="text-xs px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">
                        Seen
                    </button>
                </form>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Footer actions --}}
    <div class="mt-3 border-t pt-2 flex items-center justify-between">
        @isset($showFooterLinks)
        {{-- When included from notifications index, you may hide these by passing $showFooterLinks=false --}}
        @endisset

        <a href="{{ route('applicant.notifications.index') }}"
            class="text-xs text-slate-600 hover:text-slate-800">View all →</a>

        <a href="{{ route('applicant.notifications.settings') }}"
            class="text-xs text-slate-600 hover:text-slate-800">Settings</a>
    </div>
</div>