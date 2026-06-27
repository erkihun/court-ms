{{ __('notifications.mail.hearing_scheduled_heading') }}

{{ __('notifications.mail.case_label') }}: {{ $case->case_number ?? '-' }} @if(!empty($case->title)) - {{ $case->title }} @endif
{{ __('notifications.mail.date_time_label') }}: {{ \App\Support\EthiopianDate::format($hearing->hearing_at, withTime: true) }}
@if(!empty($hearing->type)){{ __('notifications.mail.type_label') }}: {{ $hearing->type }}@endif

{{ __('notifications.mail.hearing_arrive_early') }}

- {{ config('app.name','Court-MS') }}
