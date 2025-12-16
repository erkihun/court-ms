@php
$me = auth('applicant')->id();
$now = now();

// Count
$count = 0;

$count += \DB::table('case_messages as m')
->join('court_cases as c','c.id','=','m.case_id')
->where('c.applicant_id',$me)
->whereNotNull('m.sender_user_id')
->where('m.created_at','>=', $now->subDays(60))
->whereNotExists(function($q) use ($me){
$q->from('notification_reads as nr')
->whereColumn('nr.source_id','m.id')
->where('nr.type','message')
->where('nr.applicant_id',$me);
})
->count();

$count += \DB::table('case_hearings as h')
->join('court_cases as c','c.id','=','h.case_id')
->where('c.applicant_id',$me)
->whereBetween('h.hearing_at', [$now->copy()->subDay(), $now->copy()->addDays(60)])
->whereNotExists(function($q) use ($me){
$q->from('notification_reads as nr')
->whereColumn('nr.source_id','h.id')
->where('nr.type','hearing')
->where('nr.applicant_id',$me);
})
->count();

$count += \DB::table('case_status_logs as s')
->join('court_cases as c','c.id','=','s.case_id')
->where('c.applicant_id',$me)
->where('s.created_at','>=', $now->copy()->subDays(60))
->whereNotExists(function($q) use ($me){
$q->from('notification_reads as nr')
->whereColumn('nr.source_id','s.id')
->where('nr.type','status')
->where('nr.applicant_id',$me);
})
->count();

$count += \DB::table('respondent_case_views as v')
->join('court_cases as c','c.id','=','v.case_id')
->where('c.applicant_id',$me)
->where('v.viewed_at','>=', $now->copy()->subDays(60))
->whereNotExists(function($q) use ($me){
$q->from('notification_reads as nr')
->whereColumn('nr.source_id','v.id')
->where('nr.type','respondent_view')
->where('nr.applicant_id',$me);
})
->count();

// Latest 5 (mixed)
$msgs = \DB::table('case_messages as m')
->join('court_cases as c','c.id','=','m.case_id')
->leftJoin('users as u','u.id','=','m.sender_user_id')
->where('c.applicant_id',$me)
->whereNotNull('m.sender_user_id')
->where('m.created_at','>=', now()->subDays(60))
->whereNotExists(function($q) use ($me){
$q->from('notification_reads as nr')
->whereColumn('nr.source_id','m.id')
->where('nr.type','message')
->where('nr.applicant_id',$me);
})
->selectRaw("'message' as type, m.id as source_id, m.case_id, m.created_at, COALESCE(u.name,'Court Staff') as meta1, NULL as meta2, m.body as meta3");

$hrs = \DB::table('case_hearings as h')
->join('court_cases as c','c.id','=','h.case_id')
->where('c.applicant_id',$me)
->whereBetween('h.hearing_at', [now()->subDay(), now()->addDays(60)])
->whereNotExists(function($q) use ($me){
$q->from('notification_reads as nr')
->whereColumn('nr.source_id','h.id')
->where('nr.type','hearing')
->where('nr.applicant_id',$me);
})
->selectRaw("'hearing' as type, h.id as source_id, h.case_id, h.created_at, NULL as meta1, NULL as meta2, h.hearing_at as meta3");

$sts = \DB::table('case_status_logs as s')
->join('court_cases as c','c.id','=','s.case_id')
->where('c.applicant_id',$me)
->where('s.created_at','>=', now()->subDays(60))
->whereNotExists(function($q) use ($me){
$q->from('notification_reads as nr')
->whereColumn('nr.source_id','s.id')
->where('nr.type','status')
->where('nr.applicant_id',$me);
})
->selectRaw("'status' as type, s.id as source_id, s.case_id, s.created_at, s.from_status as meta1, s.to_status as meta2, NULL as meta3");

$views = \DB::table('respondent_case_views as v')
->join('court_cases as c','c.id','=','v.case_id')
->join('respondents as r','r.id','=','v.respondent_id')
->where('c.applicant_id',$me)
->where('v.viewed_at','>=', now()->subDays(60))
->whereNotExists(function($q) use ($me){
$q->from('notification_reads as nr')
->whereColumn('nr.source_id','v.id')
->where('nr.type','respondent_view')
->where('nr.applicant_id',$me);
})
->selectRaw("'respondent_view' as type, v.id as source_id, v.case_id, v.viewed_at as created_at, TRIM(CONCAT_WS(' ', r.first_name, r.middle_name, r.last_name)) as meta1, NULL as meta2, NULL as meta3");

$items = $msgs->unionAll($hrs)->unionAll($sts)->unionAll($views)->orderBy('created_at','desc')->limit(5)->get();
@endphp

<div x-data="{ open:false }" class="relative">
    <button @click="open = !open"
        class="relative inline-flex items-center gap-2 rounded-md border px-3 py-1.5 text-sm hover:bg-slate-50">
        {{-- bell --}}
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M14.243 19H5a2 2 0 0 1-2-2v-1.2a2 2 0 0 1 1.07-1.76l1.31-.7A2 2 0 0 0 6 11.56V9a6 6 0 1 1 12 0v2.56a2 2 0 0 0 1.62 1.96l1.31.7A2 2 0 0 1 22 15.8V17a2 2 0 0 1-2 2h-4" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19a3 3 0 0 0 6 0" />
        </svg>
        @if($count>0)
        <span class="absolute -top-1 -right-1 inline-flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-600 px-1.5 text-[11px] font-semibold text-white">
            {{ $count }}
        </span>
        @endif
        <span class="hidden md:inline">Notifications</span>
    </button>

    <div x-cloak x-show="open" @click.outside="open=false"
        class="absolute right-0 mt-2 w-80 rounded-md border bg-white shadow-lg">
        <div class="flex items-center justify-between px-3 py-2 border-b">
            <div class="text-sm font-semibold">Notifications</div>
            <form method="POST" action="{{ route('applicant.notifications.markAll') }}">
                @csrf
                <button class="text-xs text-slate-600 hover:text-slate-900">Mark all as read</button>
            </form>
        </div>

        @if($count===0)
        <div class="p-3 text-sm text-slate-500">You’re all caught up.</div>
        @else
        <ul class="max-h-80 overflow-auto divide-y">
            @foreach($items as $n)
            @php
            $url = route('applicant.cases.show', $n->case_id);
            if ($n->type === 'respondent_view') {
                $url = null;
            } elseif ($n->type === 'hearing') {
                $url .= '#hearings';
            } elseif ($n->type === 'message') {
                $url .= '#messages';
            } else {
                $url .= '#timeline';
            }
            @endphp
            <li>
                @if($url)
                <a href="{{ $url }}" class="block px-3 py-2 hover:bg-slate-50">
                @else
                <div class="block px-3 py-2">
                @endif
                    <div class="text-xs text-slate-500">
                        {{ \Illuminate\Support\Carbon::parse($n->created_at)->diffForHumans() }}
                        → <span class="uppercase">{{ $n->type }}</span>
                    </div>
                    <div class="text-sm text-slate-800">
                        @if($n->type === 'message')
                        New message from {{ $n->meta1 ?? 'Court Staff' }}
                        @elseif($n->type === 'hearing')
                        Hearing {{ $n->meta2 ? "($n->meta2) " : '' }}on
                        {{ \App\Support\EthiopianDate::format($n->meta3, withTime: true) }}
                        @elseif($n->type === 'respondent_view')
                        Respondent {{ $n->meta1 ?? 'Viewed the case' }} viewed this case
                        @else
                        Status changed: {{ ucfirst($n->meta1 ?? '-') }} &rarr; {{ ucfirst($n->meta2 ?? '-') }}
                        @endif
                    </div>
                @if($url)</a>@else</div>@endif
            </li>
            @endforeach
        </ul>
        <div class="px-3 py-2">
            <a href="{{ route('applicant.notifications.index') }}" class="text-xs text-slate-700 hover:underline">
                See all
            </a>
        </div>
        @endif
    </div>
</div>
