Hearing Scheduled

Case: {{ $case->case_number ?? '—' }} @if(!empty($case->title)) — {{ $case->title }} @endif
Date & Time: {{ \App\Support\EthiopianDate::format($hearing->hearing_at, withTime: true) }}
@if(!empty($hearing->type))Type: {{ $hearing->type }}@endif

Please arrive early and bring any required documents.

— {{ config('app.name','Court-MS') }}
