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
->select('h.id','h.hearing_at','c.id as case_id','c.case_number')
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
->select(
    'v.id',
    'v.viewed_at',
    'v.case_id',
    'c.case_number',
    \DB::raw(
        (\DB::getDriverName() === 'sqlite')
            ? "TRIM(COALESCE(r.first_name,'') || ' ' || COALESCE(r.middle_name,'') || ' ' || COALESCE(r.last_name,'')) as respondent_name"
            : "TRIM(CONCAT_WS(' ', r.first_name, r.middle_name, r.last_name)) as respondent_name"
    )
)
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
        <div class="text-sm font-semibold text-slate-700">{{ __('app.Notifications') }}</div>
        @if($hasAny)
        <form method="POST" action="{{ route('admin.notifications.markAll') }}">
            @csrf
            <button class="text-xs px-2 py-1 rounded border hover:bg-slate-50">{{ __('app.Mark all as seen') }}</button>
        </form>
        @endif
    </div>

    @unless($hasAny)
    <div class="text-sm text-slate-500">{{ __('app.youre_all_caught_up') }}</div>
    @endunless

    {{-- New filings --}}
    @if($newCases->isNotEmpty())
    <div class="mt-3">
        <div class="text-xs font-medium text-slate-500 mb-1">{{ __('app.New cases') }}</div>
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
                    <button class="text-xs px-2 py-1 rounded border hover:bg-slate-50">{{ __('app.Seen') }}</button>
                </form>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Messages --}}
    @if($msgs->isNotEmpty())
    <div class="mt-3">
        <div class="text-xs font-medium text-slate-500 mb-1">{{ __('app.Applicant messages') }}</div>
        <ul class="divide-y">
            @foreach($msgs as $m)
            @php
            $legacyApplicantUpdate = 'Applicant updated the case details. Please review the submission.';
            $displayBody = trim((string) $m->body) === $legacyApplicantUpdate
                ? __('cases.notifications.applicant_updated_submission')
                : (string) $m->body;
            @endphp
            <li class="py-2 flex items-center justify-between">
                <a href="{{ route('cases.show', $m->case_id) }}" class="text-sm">
                    <div class="font-medium text-slate-800">{{ $m->case_number }}</div>
                    <div class="text-xs text-slate-500">
                        {{ \Illuminate\Support\Str::limit($displayBody, 80) }}
                        · {{ \Illuminate\Support\Carbon::parse($m->created_at)->diffForHumans() }}
                    </div>
                </a>
                <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                    @csrf
                    <input type="hidden" name="type" value="message">
                    <input type="hidden" name="sourceId" value="{{ $m->id }}">
                    <button class="text-xs px-2 py-1 rounded border hover:bg-slate-50">{{ __('app.Seen') }}</button>
                </form>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Status --}}
    @if($status->isNotEmpty())
    <div class="mt-3">
        <div class="text-xs font-medium text-slate-500 mb-1">{{ __('app.admin_notifications.status_updates') }}</div>
        <ul class="divide-y">
            @foreach($status as $s)
            <li class="py-2 flex items-center justify-between">
                <a href="{{ route('cases.show', $s->case_id) }}" class="text-sm">
                    <div class="font-medium text-slate-800">{{ $s->case_number }}</div>
                    <div class="text-xs text-slate-500">
                        {{ __('app.admin_notifications.status_changed', ['from' => ucfirst($s->from_status), 'to' => ucfirst($s->to_status)]) }}
                        · {{ \Illuminate\Support\Carbon::parse($s->created_at)->diffForHumans() }}
                    </div>
                </a>
                <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                    @csrf
                    <input type="hidden" name="type" value="status">
                    <input type="hidden" name="sourceId" value="{{ $s->id }}">
                    <button class="text-xs px-2 py-1 rounded border hover:bg-slate-50">{{ __('app.Seen') }}</button>
                </form>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Hearings --}}
    @if($hearings->isNotEmpty())
    <div class="mt-3">
        <div class="text-xs font-medium text-slate-500 mb-1">{{ __('app.Upcoming hearings') }}</div>
        <ul class="divide-y">
            @foreach($hearings as $h)
            <li class="py-2 flex items-center justify-between">
                <a href="{{ route('cases.show', $h->case_id) }}" class="text-sm">
                    <div class="font-medium text-slate-800">
                        {{ $h->case_number }}
                        — {{ \App\Support\EthiopianDate::format($h->hearing_at, withTime: true) }}
                    </div>
                    <div class="text-xs text-slate-500">{{ $h->type ?: __('app.Hearing') }} · {{ $h->location ?: '—' }}</div>
                </a>
                <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                    @csrf
                    <input type="hidden" name="type" value="hearing">
                    <input type="hidden" name="sourceId" value="{{ $h->id }}">
                    <button class="text-xs px-2 py-1 rounded border hover:bg-slate-50">{{ __('app.Seen') }}</button>
                </form>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Respondent views --}}
    @if($respondentViews->isNotEmpty())
    <div class="mt-3">
        <div class="text-xs font-medium text-slate-500 mb-1">{{ __('app.admin_notifications.respondent_views') }}</div>
        <ul class="divide-y">
            @foreach($respondentViews as $v)
            <li class="py-2 flex items-center justify-between gap-3">
                <a href="{{ route('cases.show', $v->case_id) }}" class="text-sm flex-1">
                    <div class="font-medium text-slate-800">{{ $v->case_number }}</div>
                    <div class="text-xs text-slate-500">
                        {{ __('app.admin_notifications.respondent_viewed_case', ['name' => ($v->respondent_name ?: __('app.admin_notifications.respondent_default'))]) }}
                        <span class="text-slate-400">·</span>
                        {{ \Illuminate\Support\Carbon::parse($v->viewed_at)->diffForHumans() }}
                    </div>
                </a>
                <form method="POST" action="{{ route('admin.notifications.markOne') }}">
                    @csrf
                    <input type="hidden" name="type" value="respondent_view">
                    <input type="hidden" name="sourceId" value="{{ $v->id }}">
                    <button class="text-xs px-2 py-1 rounded border border-slate-200 hover:bg-slate-50">
                        {{ __('app.Seen') }}
                    </button>
                </form>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="mt-3 text-right">
        <a href="{{ route('admin.notifications.index') }}" class="text-xs text-blue-700 hover:underline">
            {{ __('app.View all') }}
        </a>
    </div>
</div>
