Hearing Scheduled

Case: {{ $case->case_number ?? '—' }} @if(!empty($case->title)) — {{ $case->title }} @endif
Date & Time: {{ \Illuminate\Support\Carbon::parse($hearing->hearing_at)->timezone(config('app.timezone'))->format('M d, Y H:i') }}
@if(!empty($hearing->type))Type: {{ $hearing->type }}@endif

Please arrive early and bring any required documents.

— {{ config('app.name','Court-MS') }}
