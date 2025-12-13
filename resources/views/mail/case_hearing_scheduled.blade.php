<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Hearing Scheduled</title>
</head>

<body style="font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; color:#0f172a;">
    <h2 style="margin:0 0 10px;">Hearing Scheduled</h2>

    <p style="margin:0 0 12px;">
        Your case <strong>{{ $case->case_number ?? '—' }}</strong>
        @if(!empty($case->title)) — <em>{{ $case->title }}</em>@endif
        has a scheduled hearing.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="border-collapse:collapse; width:100%; max-width:560px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px;">
        <tr>
            <td style="padding:12px 14px;">
                <p style="margin:0 0 8px;">
                    <strong>Date &amp; Time:</strong>
                    {{ \Illuminate\Support\Carbon::parse($hearing->hearing_at)->timezone(config('app.timezone'))->format('M d, Y H:i') }}
                </p>
                @if(!empty($hearing->type))
                <p style="margin:0 0 8px;"><strong>Type:</strong> {{ $hearing->type }}</p>
                @endif
            </td>
        </tr>
    </table>

    <p style="margin:16px 0 0;">Please arrive early and bring any required documents.</p>
    <p style="margin:16px 0 0;">— {{ config('app.name','Court-MS') }}</p>
</body>

</html>
