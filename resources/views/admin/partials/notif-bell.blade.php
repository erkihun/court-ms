{{-- Admin notifications bell (dark theme) --}}
@php
$uid = auth()->id();
$now = now();

// Applicant → Admin messages (last 14d) unseen by this admin
$adminUnseenMsgs = \DB::table('case_messages as m')
->join('court_cases as c', 'c.id', '=', 'm.case_id')
->select('m.id','m.body','m.created_at','c.case_number','c.id as case_id')
->whereNotNull('m.sender_applicant_id')
->where('m.created_at', '>=', $now->copy()->subDays(14))
->whereNotExists(function($q) use ($uid){
$q->from('admin_notification_reads as nr')
->whereColumn('nr.source_id', 'm.id')
->where('nr.type', 'message')
->where('nr.user_id', $uid);
})
->orderByDesc('m.created_at')
->limit(5)
->get();

// New pending & unassigned cases (last 14d)
$adminUnseenCases = \DB::table('court_cases as c')
->select('c.id','c.case_number','c.title','c.created_at')
->where('c.status', 'pending')
->whereNull('c.assigned_user_id')
->where('c.created_at', '>=', $now->copy()->subDays(14))
->whereNotExists(function($q) use ($uid){
$q->from('admin_notification_reads as nr')
->whereColumn('nr.source_id', 'c.id')
->where('nr.type', 'case')
->where('nr.user_id', $uid);
})
->orderByDesc('c.created_at')
->limit(5)
->get();

// Upcoming hearings for cases assigned to me (next 14d)
$adminUpcomingHearings = \DB::table('case_hearings as h')
->join('court_cases as c', 'c.id', '=', 'h.case_id')
->select('h.id','h.hearing_at','c.id as case_id','c.case_number')
->where('c.assigned_user_id', $uid)
->whereBetween('h.hearing_at', [$now, $now->copy()->addDays(14)])
->whereNotExists(function($q) use ($uid){
$q->from('admin_notification_reads as nr')
->whereColumn('nr.source_id', 'h.id')
->where('nr.type', 'hearing')
->where('nr.user_id', $uid);
})
->orderBy('h.hearing_at')
->limit(5)
->get();

$__adminNotifCount = $adminUnseenMsgs->count() + $adminUnseenCases->count() + $adminUpcomingHearings->count();
@endphp

<div class="relative" x-data="{ bell:false }">
    <button @click="bell = !bell"
        class="relative inline-flex items-center justify-center rounded-md border border-slate-700 bg-slate-900/60 px-2.5 py-1.5 hover:bg-slate-800">
        {{-- bell icon --}}
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-200" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0h6z" />
        </svg>
        @if($__adminNotifCount > 0)
        <span class="absolute -top-1 -right-1 grid h-5 min-w-[20px] place-items-center rounded-full bg-rose-600 px-1 text-[11px] font-semibold text-white">
            {{ $__adminNotifCount > 99 ? '99+' : $__adminNotifCount }}
        </span>
        @endif
    </button>

    {{-- Dropdown --}}
    <div x-cloak x-show="bell" @click.outside="bell=false"
        class="absolute right-0 mt-2 w-[32rem] max-w-[90vw] rounded-md border border-slate-700 bg-slate-900 text-slate-100 shadow-xl">
        <div class="p-3">
            <div class="mb-2 flex items-center justify-between">
                <div class="text-sm font-semibold">Notifications</div>
                @if($__adminNotifCount > 0)
                <form method="POST" action="{{ route('admin.notifications.markAll') }}">
                    @csrf
                    <button class="text-xs px-2 py-1 rounded border border-slate-700 hover:bg-slate-800">Mark all as seen</button>
                </form>
                @endif
            </div>

            @if($__adminNotifCount === 0)
            <div class="text-sm text-slate-400">You’re all caught up.</div>
            @else
            {{-- Applicant messages --}}
            @if($adminUnseenMsgs->isNotEmpty())
            <div class="mt-3">
                <div class="text-xs font-medium text-slate-400 mb-1">Applicant messages</div>
                <ul class="divide-y divide-slate-800">
                    @foreach($adminUnseenMsgs as $m)
                    <li class="py-2 flex items-center justify-between">
                        <a href="{{ route('cases.show', $m->case_id) }}" class="text-sm">
                            <div class="font-medium text-slate-100">{{ $m->case_number }}</div>
                            <div class="text-xs text-slate-400">
                                {{ \Illuminate\Support\Str::limit($m->body, 80) }}
                                · {{ \Illuminate\Support\Carbon::parse($m->created_at)->diffForHumans() }}
                            </div>
                        </a>
                        <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                            @csrf
                            <input type="hidden" name="type" value="message">
                            <input type="hidden" name="sourceId" value="{{ $m->id }}">
                            <button class="text-xs px-2 py-1 rounded border border-slate-700 hover:bg-slate-800">Seen</button>
                        </form>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- New cases --}}
            @if($adminUnseenCases->isNotEmpty())
            <div class="mt-3">
                <div class="text-xs font-medium text-slate-400 mb-1">New cases</div>
                <ul class="divide-y divide-slate-800">
                    @foreach($adminUnseenCases as $c)
                    <li class="py-2 flex items-center justify-between">
                        <a href="{{ route('cases.show', $c->id) }}" class="text-sm">
                            <div class="font-medium text-slate-100">{{ $c->case_number }}</div>
                            <div class="text-xs text-slate-400">
                                {{ \Illuminate\Support\Str::limit($c->title, 80) }}
                                · {{ \Illuminate\Support\Carbon::parse($c->created_at)->diffForHumans() }}
                            </div>
                        </a>
                        <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                            @csrf
                            <input type="hidden" name="type" value="case">
                            <input type="hidden" name="sourceId" value="{{ $c->id }}">
                            <button class="text-xs px-2 py-1 rounded border border-slate-700 hover:bg-slate-800">Seen</button>
                        </form>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Upcoming hearings --}}
            @if($adminUpcomingHearings->isNotEmpty())
            <div class="mt-3">
                <div class="text-xs font-medium text-slate-400 mb-1">Upcoming hearings</div>
                <ul class="divide-y divide-slate-800">
                    @foreach($adminUpcomingHearings as $h)
                    <li class="py-2 flex items-center justify-between">
                        <a href="{{ route('cases.show', $h->case_id) }}" class="text-sm">
                            <div class="font-medium text-slate-100">
                                {{ $h->case_number }} — {{ \App\Support\EthiopianDate::format($h->hearing_at, withTime: true) }}
                            </div>
                            <div class="text-xs text-slate-400">
                                {{ $h->type ?: 'Hearing' }} · {{ $h->location ?: '—' }}
                            </div>
                        </a>
                        <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                            @csrf
                            <input type="hidden" name="type" value="hearing">
                            <input type="hidden" name="sourceId" value="{{ $h->id }}">
                            <button class="text-xs px-2 py-1 rounded border border-slate-700 hover:bg-slate-800">Seen</button>
                        </form>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="mt-3 flex items-center justify-end">
                <a href="{{ route('admin.notifications.index') }}"
                    class="text-xs px-2 py-1 rounded border border-slate-700 hover:bg-slate-800">View all</a>
            </div>
            @endif
        </div>
    </div>
</div>
