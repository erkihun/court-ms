<!doctype html>
<html>

<body style="font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; color:#0f172a;">
    <h2 style="margin:0 0 8px;">Your filing receipt</h2>
    <p style="margin:0 0 12px;">
        We’ve attached a PDF receipt for your case
        <strong>{{ $case->case_number }}</strong>@if(!empty($case->title)) — <em>{{ $case->title }}</em>@endif.
    </p>

    <p style="margin:0 0 8px;">If you didn’t request this, you can ignore this email.</p>

    <p style="margin:16px 0 0;">Thanks,<br>{{ config('app.name','Court-MS') }}</p>
</body>

</html>