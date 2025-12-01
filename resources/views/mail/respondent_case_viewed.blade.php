<!doctype html>
<html>

<body style="font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; color:#0f172a;">
    <h2 style="margin-bottom: 8px">Respondent viewed case</h2>
    <p style="margin:0 0 12px;">
        Case <strong>{{ $case->case_number ?? 'Unknown' }}</strong>
        {{ !empty($case->title) ? ' – ' . $case->title : '' }}
    </p>
    <p style="margin:0 0 12px;">
        {{ $respondentName }} opened the case at {{ $timestamp }}.
    </p>
    <p style="margin:0;">
        <a href="{{ $caseUrl }}" style="color:#0ea5e9; text-decoration:none;">Open the case for details</a>
    </p>
</body>

</html>
