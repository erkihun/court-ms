<!doctype html>
<html>

<body style="font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; color:#0f172a;">
    <h2 style="margin:0 0 8px">New case message</h2>
    <p style="margin:0 0 12px;">
        Case <strong>{{ $caseRow->case_number }}</strong> — <em>{{ $caseRow->title }}</em>
    </p>

    <p style="margin:0 0 8px;">From: <strong>{{ $sender }}</strong></p>
    <blockquote style="margin:0; padding:10px 12px; background:#f8fafc; border-left:4px solid #64748b;">
        {{ $bodyPreview }}
    </blockquote>

    <p style="margin:16px 0 0;">Open the case to reply.</p>
</body>

</html>