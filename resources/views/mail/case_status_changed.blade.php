<!doctype html>
<html>

<body style="font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; color:#0f172a;">
    @php
        $oldStatusLabel = __('cases.status.' . $oldStatus);
        $newStatusLabel = __('cases.status.' . $newStatus);
        $oldStatusLabel = $oldStatusLabel === 'cases.status.' . $oldStatus ? ucfirst($oldStatus) : $oldStatusLabel;
        $newStatusLabel = $newStatusLabel === 'cases.status.' . $newStatus ? ucfirst($newStatus) : $newStatusLabel;
    @endphp

    <h2 style="margin:0 0 8px">{{ __('notifications.mail.case_status_heading') }}</h2>
    <p style="margin:0 0 16px;">
        {{ __('notifications.mail.case_label') }} <strong>{{ $caseRow->case_number }}</strong>
        @if(!empty($caseRow->title)) &mdash; <em>{{ $caseRow->title }}</em>@endif
    </p>

    <p style="margin:0 0 8px;">
        {{ __('notifications.mail.status_label') }}:
        <strong>{{ $oldStatusLabel }}</strong> &rarr; <strong>{{ $newStatusLabel }}</strong>
    </p>

    @if(!empty($note))
    <p style="margin:12px 0 0;"><strong>{{ __('notifications.mail.note_label') }}:</strong> {{ $note }}</p>
    @endif

    <p style="margin:20px 0 0;">{{ __('notifications.mail.thank_you') }}<br>{{ config('app.name') }}</p>
</body>

</html>
