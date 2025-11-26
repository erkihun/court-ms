<!doctype html>
<html>

<body style="font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; color:#0f172a;">
    <h2 style="margin:0 0 8px">Case status updated</h2>
    <p style="margin:0 0 16px;">
        Case <strong>{{ $caseRow->case_number }}</strong> — <em>{{ $caseRow->title }}</em>
    </p>

    <p style="margin:0 0 8px;">
        Status: <strong>{{ ucfirst($oldStatus) }}</strong> → <strong>{{ ucfirst($newStatus) }}</strong>
    </p>

    @if(!empty($note))
    <p style="margin:12px 0 0;"><strong>Note:</strong> {{ $note }}</p>
    @endif

    <p style="margin:20px 0 0;">Thank you,<br>{{ config('app.name') }}</p>
</body>

</html>