@php
use Illuminate\Support\Facades\DB;

$respondentId = auth('respondent')->id();
$notifCount = 0;
$respondentMessages = collect();

if ($respondentId) {
$base = DB::table('respondent_case_views as v')
->join('case_messages as m', 'm.case_id', '=', 'v.case_id')
->join('court_cases as c', 'c.id', '=', 'v.case_id')
->leftJoin('users as u', 'u.id', '=', 'm.sender_user_id')
->select(
'v.id',
'v.viewed_at',
'c.case_number',
'c.id as case_id',
'm.body',
'm.created_at',
'u.name as admin_name'
)
->where('v.respondent_id', $respondentId)
->whereNotNull('m.sender_user_id')
->where('m.created_at', '>=', now()->subDays(30))
->whereNotExists(function ($q) use ($respondentId) {
$q->from('respondent_notification_reads as r')
->whereColumn('r.source_id', 'v.id')
->where('r.type', 'respondent_view')
->where('r.respondent_id', $respondentId);
})
->orderByDesc('v.viewed_at');

$notifCount = (clone $base)->count();
$respondentMessages = (clone $base)->limit(6)->get();
}
@endphp

<div x-data="{ open:false }" class="relative">
    <button @click="open=!open" type="button"
        class="relative inline-flex items-center gap-1.5 rounded-full border border-white/30 bg-white/10 px-3 py-1.5 text-sm text-white hover:bg-white/20">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0a3 3 0 1 1-6 0h6z" />
        </svg>
        @if($notifCount > 0)
        <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-[11px] font-semibold text-white">
            {{ $notifCount }}
        </span>
        @endif

    </button>

    <div x-cloak x-show="open" @click.outside="open=false"
        class="absolute right-0 mt-2 w-80 rounded-md border border-white/20 bg-white/90 text-slate-800 shadow-lg backdrop-blur">
        <div class="p-3 border-b border-white/30 text-sm font-semibold uppercase tracking-wide text-slate-600 flex items-center justify-between gap-3">
            <span>{{ __('respondent.recent_replies') }}</span>
            <a href="{{ route('respondent.responses.index') }}" class="text-xs font-semibold text-blue-600 hover:underline">
                {{ __('respondent.view_all') }}
            </a>
        </div>
        <div class="px-3 pb-2 flex items-center justify-between gap-2 text-xs uppercase text-slate-500">
            <span>{{ __('respondent.notifications') }}</span>
            @if($notifCount > 0)
            <form method="POST" action="{{ route('respondent.notifications.markAll') }}">
                @csrf
                <button type="submit" class="text-blue-600 hover:underline text-[11px]">
                    {{ __('respondent.mark_all_seen') }}
                </button>
            </form>
            @endif
        </div>
        @if($respondentMessages->isEmpty())
        <div class="p-3 text-sm text-slate-500">{{ __('respondent.no_notifications') }}</div>
        @else
        <ul class="divide-y divide-white/30 text-sm">
            @foreach($respondentMessages as $msg)
            <li class="px-3 py-2">
                <div class="text-[10px] text-slate-500">
                    {{ $msg->case_number }} · {{ \Illuminate\Support\Carbon::parse($msg->created_at)->diffForHumans() }}
                </div>
                <div class="text-slate-900 font-medium">
                    {{ $msg->admin_name ?: __('respondent.court_staff') }}
                </div>
                @php
                $msgBody = trim($msg->body);
                $isUrl = filter_var($msgBody, FILTER_VALIDATE_URL);
                @endphp
                <div class="text-slate-700 truncate">
                    @if($isUrl)
                    <a href="{{ $msgBody }}" class="text-blue-600 hover:underline" target="_blank" rel="noreferrer">
                        View letter preview
                    </a>
                    @else
                    {{ \Illuminate\Support\Str::limit($msg->body, 120) }}
                    @endif
                </div>
                <div class="mt-1 flex items-center justify-between text-[11px] text-slate-500">
                    <span>{{ $msg->admin_name ?: __('respondent.court_staff') }}</span>
                    <form method="POST" action="{{ route('respondent.notifications.markOne') }}" class="inline">
                        @csrf
                        <input type="hidden" name="type" value="respondent_view">
                        <input type="hidden" name="sourceId" value="{{ $msg->id }}">
                        <button type="submit" class="text-blue-600 hover:underline">
                            {{ __('respondent.seen') }}
                        </button>
                    </form>
                </div>
            </li>
            @endforeach
        </ul>
        @endif
    </div>
</div>
