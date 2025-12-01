@php
$uid = auth()->id();

// limit small numbers for dropdown
$newCases = \DB::table('court_cases as c')
->select('c.id','c.case_number','c.title','c.created_at')
->whereNull('c.assigned_user_id')
->where('c.created_at','>=', now()->subDays(14))
->whereNotExists(function($q) use ($uid){
$q->from('admin_notification_reads as r')
->whereColumn('r.source_id','c.id')
->where('r.type','new_case')
->where('r.user_id',$uid);
})
->orderByDesc('c.created_at')->limit(5)->get();

$msgs = \DB::table('case_messages as m')
->join('court_cases as c','c.id','=','m.case_id')
->select('m.id','m.body','m.created_at','c.id as case_id','c.case_number')
->whereNotNull('m.sender_applicant_id')
->where('c.assigned_user_id',$uid)
->where('m.created_at','>=', now()->subDays(14))
->whereNotExists(function($q) use ($uid){
$q->from('admin_notification_reads as r')
->whereColumn('r.source_id','m.id')
->where('r.type','message')
->where('r.user_id',$uid);
})
->orderByDesc('m.created_at')->limit(5)->get();

$status = \DB::table('case_status_logs as l')
->join('court_cases as c','c.id','=','l.case_id')
->select('l.id','l.from_status','l.to_status','l.created_at','c.id as case_id','c.case_number')
->where('c.assigned_user_id',$uid)
->where('l.created_at','>=', now()->subDays(14))
->whereNotExists(function($q) use ($uid){
$q->from('admin_notification_reads as r')
->whereColumn('r.source_id','l.id')
->where('r.type','status')
->where('r.user_id',$uid);
})
->orderByDesc('l.created_at')->limit(5)->get();

$hearings = \DB::table('case_hearings as h')
->join('court_cases as c','c.id','=','h.case_id')
->select('h.id','h.hearing_at','h.location','h.type','c.id as case_id','c.case_number')
->where('c.assigned_user_id',$uid)
->whereBetween('h.hearing_at',[now()->subDay(), now()->addDays(60)])
->whereNotExists(function($q) use ($uid){
$q->from('admin_notification_reads as r')
->whereColumn('r.source_id','h.id')
->where('r.type','hearing')
->where('r.user_id',$uid);
})
->orderBy('h.hearing_at')->limit(5)->get();

$respondentViews = \DB::table('respondent_case_views as v')
->join('court_cases as c','c.id','=','v.case_id')
->join('respondents as r','r.id','=','v.respondent_id')
->select('v.id','v.viewed_at','v.case_id','c.case_number', \DB::raw("TRIM(CONCAT_WS(' ', r.first_name, r.middle_name, r.last_name)) as respondent_name"))
->where(function($q) use ($uid){
$q->where('c.assigned_user_id',$uid)
->orWhereNull('c.assigned_user_id');
})
->where('v.viewed_at','>=', now()->subDays(14))
->whereNotExists(function($q) use ($uid){
$q->from('admin_notification_reads as r2')
->whereColumn('r2.source_id','v.id')
->where('r2.type','respondent_view')
->where('r2.user_id',$uid);
})
->orderByDesc('v.viewed_at')->limit(5)->get();

$hasAny = $newCases->isNotEmpty() || $msgs->isNotEmpty() || $status->isNotEmpty() || $hearings->isNotEmpty() || $respondentViews->isNotEmpty();
@endphp

<div class="p-3">
    <div class="mb-2 flex items-center justify-between">
        <div class="text-sm font-semibold text-slate-700">Notifications</div>
        @if($hasAny)
        <form method="POST" action="{{ route('admin.notifications.markAll') }}">
            @csrf
            <button class="text-xs px-2 py-1 rounded border hover:bg-slate-50">Mark all as seen</button>
        </form>
        @endif
    </div>

    @unless($hasAny)
    <div class="text-sm text-slate-500">You’re all caught up.</div>
    @endunless

    {{-- New filings --}}
    @if($newCases->isNotEmpty())
    <div class="mt-3">
        <div class="text-xs font-medium text-slate-500 mb-1">New filings</div>
        <ul class="divide-y">
            @foreach($newCases as $c)
            <li class="py-2 flex items-center justify-between">
                <a href="{{ route('cases.show', $c->id) }}" class="text-sm">
                    <div class="font-medium text-slate-800">{{ $c->case_number }}</div>
                    <div class="text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($c->title, 70) }}</div>
                </a>
                <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                    @csrf
                    <input type="hidden" name="type" value="new_case">
                    <input type="hidden" name="sourceId" value="{{ $c->id }}">
                    <button class="text-xs px-2 py-1 rounded border hover:bg-slate-50">Seen</button>
                </form>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Messages --}}
    @if($msgs->isNotEmpty())
    <div class="mt-3">
        <div class="text-xs font-medium text-slate-500 mb-1">Applicant messages</div>
        <ul class="divide-y">
            @foreach($msgs as $m)
            <li class="py-2 flex items-center justify-between">
                <a href="{{ route('cases.show', $m->case_id) }}" class="text-sm">
                    <div class="font-medium text-slate-800">{{ $m->case_number }}</div>
                    <div class="text-xs text-slate-500">
                        {{ \Illuminate\Support\Str::limit($m->body, 80) }}
                        · {{ \Illuminate\Support\Carbon::parse($m->created_at)->diffForHumans() }}
                    </div>
                </a>
                <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                    @csrf
                    <input type="hidden" name="type" value="message">
                    <input type="hidden" name="sourceId" value="{{ $m->id }}">
                    <button class="text-xs px-2 py-1 rounded border hover:bg-slate-50">Seen</button>
                </form>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Status --}}
    @if($status->isNotEmpty())
    <div class="mt-3">
        <div class="text-xs font-medium text-slate-500 mb-1">Status updates</div>
        <ul class="divide-y">
            @foreach($status as $s)
            <li class="py-2 flex items-center justify-between">
                <a href="{{ route('cases.show', $s->case_id) }}" class="text-sm">
                    <div class="font-medium text-slate-800">{{ $s->case_number }}</div>
                    <div class="text-xs text-slate-500">
                        {{ ucfirst($s->from_status) }} → <strong>{{ ucfirst($s->to_status) }}</strong>
                        · {{ \Illuminate\Support\Carbon::parse($s->created_at)->diffForHumans() }}
                    </div>
                </a>
                <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                    @csrf
                    <input type="hidden" name="type" value="status">
                    <input type="hidden" name="sourceId" value="{{ $s->id }}">
                    <button class="text-xs px-2 py-1 rounded border hover:bg-slate-50">Seen</button>
                </form>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Hearings --}}
    @if($hearings->isNotEmpty())
    <div class="mt-3">
        <div class="text-xs font-medium text-slate-500 mb-1">Upcoming hearings</div>
        <ul class="divide-y">
            @foreach($hearings as $h)
            <li class="py-2 flex items-center justify-between">
                <a href="{{ route('cases.show', $h->case_id) }}" class="text-sm">
                    <div class="font-medium text-slate-800">
                        {{ $h->case_number }}
                        — {{ \Illuminate\Support\Carbon::parse($h->hearing_at)->format('M d, Y H:i') }}
                    </div>
                    <div class="text-xs text-slate-500">{{ $h->type ?: 'Hearing' }} · {{ $h->location ?: '—' }}</div>
                </a>
                <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                    @csrf
                    <input type="hidden" name="type" value="hearing">
                    <input type="hidden" name="sourceId" value="{{ $h->id }}">
                    <button class="text-xs px-2 py-1 rounded border hover:bg-slate-50">Seen</button>
                </form>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Respondent views --}}
    @if($respondentViews->isNotEmpty())
    <div class="mt-3">
        <div class="text-xs font-medium text-slate-500 mb-1">Respondents viewed</div>
        <ul class="divide-y">
            @foreach($respondentViews as $v)
            <li class="py-2 flex items-center justify-between gap-3">
                <a href="{{ route('cases.show', $v->case_id) }}" class="text-sm flex-1">
                    <div class="font-medium text-slate-800">{{ $v->case_number }}</div>
                    <div class="text-xs text-slate-500">
                        {{ $v->respondent_name ?: 'Respondent' }} viewed this case
                        <span class="text-slate-400">A�</span>
                        {{ \Illuminate\Support\Carbon::parse($v->viewed_at)->diffForHumans() }}
                    </div>
                </a>
                <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                    @csrf
                    <input type="hidden" name="type" value="respondent_view">
                    <input type="hidden" name="sourceId" value="{{ $v->id }}">
                    <button class="text-xs px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">
                        Seen
                    </button>
                </form>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="mt-3 text-right">
        <a href="{{ route('admin.notifications.index') }}" class="text-xs text-blue-700 hover:underline">
            View all
        </a>
    </div>
</div>