@php
use Illuminate\Support\Carbon;
@endphp
<!doctype html>
<html lang="am">

<head>
    <meta charset="utf-8">
    <title>Filing Receipt — {{ $case->case_number }}</title>
    <style>
        @page {
            margin: 28mm 18mm;
        }

        body {
            font-family: 'notoethiopic', DejaVu Sans, Arial, sans-serif;
            color: #0f172a;
            font-size: 12px;
            line-height: 1.5;
        }

        h1,
        h2,
        h3 {
            margin: 0 0 8px;
            font-family: 'notoethiopic', DejaVu Sans, Arial, sans-serif;
        }

        .muted {
            color: #64748b;
        }

        .box {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 14px;
        }

        .row {
            display: flex;
            gap: 16px;
        }

        .col {
            flex: 1;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            vertical-align: top;
        }

        .table th {
            background: #f8fafc;
            text-align: left;
        }

        .tag {
            display: inline-block;
            padding: 2px 8px;
            border: 1px solid #c7d2fe;
            border-radius: 999px;
            background: #eef2ff;
            color: #3730a3;
            font-size: 11px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .brand {
            font-weight: 700;
            font-size: 16px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="brand">{{ config('app.name','Court-MS') }}</div>
        <div class="muted">Generated: {{ \App\Support\EthiopianDate::format(now(), withTime: true) }}</div>
    </div>

    <h1>Filing Receipt</h1>
    <div class="muted">Case #: <strong>{{ $case->case_number }}</strong></div>

    <div class="box">
        <div class="row">
            <div class="col">
                <div class="muted">Title</div>
                <div><strong>{{ $case->title }}</strong></div>
            </div>
            <div class="col">
                <div class="muted">Type</div>
                <div>{{ $case->case_type ?? '—' }}</div>
            </div>
            <div class="col">
                <div class="muted">Court</div>
                <div>{{ $case->court_name ?? '—' }}</div>
            </div>
            <div class="col">
                <div class="muted">Filed</div>
                <div>{{ \App\Support\EthiopianDate::format($case->filing_date ?? $case->created_at) }}</div>
            </div>
        </div>
        <div class="row" style="margin-top:10px;">
            <div class="col">
                <div class="muted">Status</div>
                <span class="tag" style="text-transform:capitalize">{{ $case->status }}</span>
            </div>
            <div class="col">
                <div class="muted">Applicant</div>
                <div>
                    {{ trim(($case->first_name ?? '').' '.($case->middle_name ?? '').' '.($case->last_name ?? '')) ?: '—' }}<br>
                    {{ $case->email ?? '—' }}{{ $case->phone ? " · {$case->phone}" : '' }}
                </div>
            </div>
            <div class="col">
                <div class="muted">Respondent</div>
                <div>{{ $case->respondent_name ?? '—' }}</div>
            </div>
            <div class="col">
                <div class="muted">Respondent Address</div>
                <div>{{ $case->respondent_address ?? '—' }}</div>
            </div>
        </div>
    </div>

    <h3>Case Details</h3>
    <div class="box">{!! nl2br(e($case->description)) !!}</div>

    @if(!empty($case->relief_requested))
    <h3>Relief Requested</h3>
    <div class="box">{!! nl2br(e($case->relief_requested)) !!}</div>
    @endif

    @if(($evidenceDocs ?? collect())->isNotEmpty())
    <h3>Evidence (Documents)</h3>
    <table class="table" style="margin-bottom:14px;">
        <thead>
            <tr>
                <th>#</th>
                <th>Title/Label</th>
                <th>File</th>
                <th>Uploaded</th>
            </tr>
        </thead>
        <tbody>
            @foreach($evidenceDocs as $i => $doc)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $doc->title ?? '—' }}</td>
                <td>{{ $doc->path ? basename($doc->path) : '—' }}</td>
                <td>{{ \App\Support\EthiopianDate::format($doc->created_at, withTime: true, fallback: '—') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(($witnesses ?? collect())->isNotEmpty())
    <h3>Witnesses</h3>
    <table class="table" style="margin-bottom:14px;">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
            </tr>
        </thead>
        <tbody>
            @foreach($witnesses as $i => $w)
            <tr>
                <td>{{ $i+1 }}</td>
                <td>{{ $w->title ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    @if(($hearings ?? collect())->isNotEmpty())
    <h3>Hearings</h3>
    <table class="table" style="margin-bottom:14px;">
        <thead>
            <tr>
                <th>Date &amp; Time</th>
                <th>Type</th>
                <th>Location</th>
            </tr>
        </thead>
        <tbody>
            @foreach($hearings as $h)
            <tr>
                <td>{{ \App\Support\EthiopianDate::format($h->hearing_at, withTime: true) }}</td>
                <td>{{ $h->type ?: '—' }}</td>
                <td>{{ $h->location ?: '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="muted" style="margin-top:18px;">
        This receipt was generated electronically by {{ config('app.name','Court-MS') }}.
    </div>
</body>

</html>
