<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('recordes.titles.pdf') }}</title>

    <style>
        @font-face {
            font-family: 'Abyssinica';
            font-style: normal;
            font-weight: 400;
            src: url('{{ asset('fonts/AbyssinicaSIL-Regular.ttf') }}') format('truetype');
        }

        :root {
            --a4-width: 210mm;
            --a4-height: 297mm;
            --record-horizontal-padding: 18mm;
            --print-margin-top: 12mm;
            --print-margin-right: 14mm;
            --print-margin-bottom: 14mm;
            --print-margin-left: 14mm;
            --print-content-height: calc(var(--a4-height) - var(--print-margin-top) - var(--print-margin-bottom));
        }

        @page {
            size: A4 portrait;
            margin: var(--print-margin-top) var(--print-margin-right) var(--print-margin-bottom) var(--print-margin-left);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #e5e7eb;
            min-height: 100vh;
            font-family: 'Abyssinica', 'Nyala', 'DejaVu Serif', serif;
            color: #0f172a;
        }

        .pdf-toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .pdf-toolbar h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }

        .toolbar-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            border: 1px solid #93c5fd;
            border-radius: 6px;
            padding: 8px 14px;
            background: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, color 0.2s, border-color 0.2s;
            color: #1d4ed8;
        }

        .btn:hover {
            background: #eff6ff;
            border-color: #60a5fa;
        }

        .btn-primary {
            background: #2563eb;
            color: #fff;
            border-color: #2563eb;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .page-wrapper {
            padding: 20px;
            display: flex;
            justify-content: center;
        }
        body.pdf-export .page-wrapper {
            padding: 5mm 0;
        }

        .record-page {
            width: var(--a4-width);
            min-height: var(--a4-height);
            background: #fff;
            box-shadow: 0 4px 6px rgba(15, 23, 42, 0.1);
            padding: 22mm var(--record-horizontal-padding);
            font-size: 14px;
            line-height: 1.7;
            position: relative;
        }
        body.pdf-export .record-page {
            padding: 12mm 14mm 14mm;
            box-shadow: none;
        }

        .record-page h1 {
            font-size: 26px;
            margin-bottom: 10px;
        }

        .record-page h2 {
            font-size: 20px;
            margin: 20px 0 10px;
        }

        .record-page h3 {
            font-size: 16px;
            margin: 14px 0 8px;
        }

        .section {
            margin-bottom: 18px;
            width: 100%;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .page-counter-overlay {
            position: fixed;
            bottom: 20px;
            right: 30px;
            background: rgba(15, 23, 42, 0.85);
            color: #fff;
            font-size: 13px;
            padding: 6px 12px;
            border-radius: 999px;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.2);
            pointer-events: none;
            z-index: 1000;
        }

        .page-preview-footer {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            font-size: 12px;
            color: #475569;
            background: rgba(255, 255, 255, 0.9);
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid #cbd5f5;
            pointer-events: none;
        }

        body.pdf-export .page-preview-footer {
            display: none !important;
        }

        body.pdf-export .page-counter-overlay {
            display: none !important;
        }

        @media print {
            .page-preview-footer,
            .page-counter-overlay {
                display: none !important;
            }
        }

        .section > * {
            width: 100%;
        }

        .card {
            border: 1px solid #dbeafe;
            width: 100%;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }

        .meta {
            font-size: 14px;
            color: #475569;
            word-break: break-word;
        }

        .content {
            margin-top: 8px;
            text-align: justify;
            word-break: break-word;
        }

        .content p,
        .content li,
        .letter-preview-container p,
        .letter-preview-container li {
            orphans: 3;
            widows: 3;
        }

        .content img,
        .content table,
        .content svg,
        .letter-preview-container img,
        .letter-preview-container table,
        .letter-preview-container svg {
            break-inside: avoid-page;
            page-break-inside: avoid;
        }

        .muted {
            font-size: 12px;
            color: #64748b;
        }

        .pdf-preview {
            margin-top: 10px;
            border: 1px solid #cbd5f5;
            border-radius: 6px;
            overflow: hidden;
            background: #f8fafc;
        }

        .pdf-rendered-viewer {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 12px;
        }

        .pdf-preview iframe.fallback-pdf-frame {
            border: none;
            width: 100%;
            min-height: 297mm;
            aspect-ratio: 210 / 297;
            background: #fff;
        }

        .pdf-rendered-viewer canvas {
            width: 100% !important;
            height: auto !important;
            border-radius: 4px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.08);
        }

        .pdf-rendered-viewer .loading-text {
            font-size: 13px;
            color: #475569;
            text-align: center;
            padding: 40px 0;
        }

        .pdf-preview iframe {
            width: 100%;
            min-height: 297mm;
            height: auto;
            border: none;
            background: #f8fafc;
        }

        .content a,
        .meta a {
            color: inherit;
            text-decoration: none;
            pointer-events: none;
            cursor: default;
        }

        .letter-full-preview {
            margin-top: 16px;
            border: 1px solid #dbeafe;
            border-radius: 12px;
            padding: 18px;
            background: #fff;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.1);
            page-break-inside: avoid;
        }

        .letter-full-preview + .letter-full-preview {
            page-break-before: always;
        }

        .letter-preview-container {
            margin-top: 10px;
            overflow-x: auto;
            overflow-y: hidden;
            padding-bottom: 4px;
        }

        .preview-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
            margin: 0;
        }

        .a4-sheet {
            width: var(--a4-width);
            min-height: var(--a4-height);
            background: #fff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            padding-top: 2mm;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .a4-sheet .letter-header img,
        .a4-sheet .letter-footer img {
            width: 100%;
            height: auto;
            display: block;
        }

        .a4-sheet .letter-body-container {
            flex: 1;
            padding: 0 20mm 0 20mm;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .a4-sheet .content-slot {
            width: 100%;
        }

        .letter-preview-container .a4-sheet {
            width: var(--a4-width);
            min-width: var(--a4-width);
            min-height: var(--a4-height);
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
            border: 1px solid #e2e8f0;
            flex: 0 0 auto;
        }

        .letters-section {
            margin-left: calc(var(--record-horizontal-padding) * -1);
            margin-right: calc(var(--record-horizontal-padding) * -1);
        }

        .letters-section > h2,
        .letters-section > .meta {
            padding-left: var(--record-horizontal-padding);
            padding-right: var(--record-horizontal-padding);
        }

        .letters-section > .letter-preview-container {
            padding-left: var(--record-horizontal-padding);
            padding-right: var(--record-horizontal-padding);
        }

        .letters-section .preview-wrapper {
            width: max-content;
            min-width: var(--a4-width);
            margin: 0 auto;
        }

        .letter-preview-container .letter-header img,
        .letter-preview-container .letter-footer img {
            width: 100%;
            height: auto;
            display: block;
        }

        .letter-preview-container .letter-body-container {
            flex: 1;
            padding: 0 20mm 0 20mm;
            font-size: 13px;
            color: #0f172a;
            line-height: 1.65;
            display: flex;
            flex-direction: column;
        }

        .letter-preview-container .meta-row {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            font-size: 12px;
            color: #475569;
            gap: 8px;
            margin-bottom: 12px;
        }

        .letter-preview-container .recipient-block {
            margin-bottom: 10px;
            font-weight: 600;
        }

        .letter-preview-container .subject-line {
            text-transform: uppercase;
            text-align: center;
            font-weight: 700;
            margin: 16px 0;
            letter-spacing: 0.04em;
        }

        .letter-preview-container .letter-body-content {
            text-align: justify;
        }

        .letter-preview-container .closing-block {
            margin-top: 5mm;
            margin-bottom: 5mm;
            text-align: right;
            color: #5539CC;
        }

        .letter-preview-container .closing-block img {
            max-height: 60px;
            max-width: 200px;
            display: inline-block;
            margin-bottom: 6px;
        }

        .letter-preview-container .page-seal-bottom {
            position: absolute;
            bottom: min(30mm, 12vw);
            right: min(32mm, 12vw);
            z-index: 5;
            width: clamp(70px, 14vw, 120px);
            height: clamp(70px, 14vw, 120px);
            display: flex;
            justify-content: center;
            align-items: center;
            pointer-events: none;
        }

        .letter-preview-container .page-seal-bottom img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            opacity: 0.85;
        }

        .letter-preview-container .letter-footer {
            position: relative;
            height: 34mm;
            flex-shrink: 0;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            padding: 0 8mm;
            gap: 6mm;
            overflow: hidden;
        }

        .letter-preview-container .letter-footer img {
            width: calc(100% - clamp(50px, 6vw, 80px) - 6mm);
            height: auto;
        }

        .letter-preview-container .qr-block {
            width: clamp(50px, 6vw, 80px);
            height: clamp(50px, 6vw, 80px);
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border-radius: 8px;
            border: 1px solid rgba(148, 163, 184, 0.5);
            padding: 4px;
            flex-shrink: 0;
        }

        .letter-preview-container .qr-block svg {
            width: 100%;
            height: 100%;
            display: block;
        }

        .letter-preview-container .cc-block {
            padding: 0 26px 24px;
            font-size: 12px;
            color: #475569;
        }

        .letter-preview-container .signature-space {
            height: 10mm;
        }

        .letter-preview-container .recipient-list-item {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .letter-preview-container ul {
            margin: 4px 0 0 16px;
        }

        .letter-preview-container .pl-4 { padding-left: 1rem; }
        .letter-preview-container .mr-3 { margin-right: 0.75rem; }
        .letter-preview-container .font-bold { font-weight: 700; }
        .letter-preview-container .inline-flex { display: inline-flex; }
        .letter-preview-container .items-end { align-items: flex-end; }
        .letter-preview-container .gap-6 { gap: 1.5rem; }
        .letter-preview-container .flex { display: flex; }
        .letter-preview-container .flex-col { flex-direction: column; }
        .letter-preview-container .items-center { align-items: center; }
        .letter-preview-container .gap-1 { gap: 0.25rem; }
        .letter-preview-container .list-disc { list-style-type: disc; }
        .letter-preview-container .mt-1 { margin-top: 0.25rem; }
        .letter-preview-container .ml-5 { margin-left: 1.25rem; }
        .bench-judges-panel {
            margin-top: 18px;
            border: 1px solid #dbeafe;
            border-radius: 10px;
            padding: 12px;
            background: #f8fafc;
        }

        .bench-judges-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
            margin-top: 10px;
        }

        .bench-judge-card {
            border: 1px dashed #cbd5f5;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            background: #fff;
        }

        .bench-judge-name {
            font-weight: 600;
        }

        .bench-judge-date {
            font-size: 12px;
            color: #475569;
            margin-top: 4px;
        }

        .bench-signatures {
            display: flex;
            gap: 30px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        .bench-signature-line {
            flex: 1;
            min-width: 220px;
        }

        .bench-signature-placeholder {
            height: 45px;
            border-bottom: 1px solid #94a3b8;
            margin-bottom: 6px;
        }

        .bench-signature-label {
            font-size: 13px;
            color: #475569;
            text-align: center;
        }

        body.print-export-active .record-page,
        body.pdf-export .record-page {
            width: auto;
            min-height: auto;
            padding: 0;
            box-shadow: none;
        }

        body.print-export-active .letters-section,
        body.pdf-export .letters-section {
            break-before: page;
            page-break-before: always;
        }

        body.print-export-active .pdf-preview,
        body.print-export-active .pdf-rendered-viewer,
        body.pdf-export .pdf-preview,
        body.pdf-export .pdf-rendered-viewer {
            break-inside: avoid-page;
            page-break-inside: avoid;
        }

        body.print-export-active .preview-wrapper,
        body.pdf-export .preview-wrapper {
            gap: 0;
        }

        body.print-export-active .letter-live-preview,
        body.pdf-export .letter-live-preview {
            margin-top: 0;
            break-inside: avoid-page;
            page-break-inside: avoid;
        }

        body.print-export-active .letter-live-preview + .letter-live-preview,
        body.pdf-export .letter-live-preview + .letter-live-preview {
            break-before: page;
            page-break-before: always;
        }

        body.print-export-active .preview-wrapper .a4-sheet,
        body.pdf-export .preview-wrapper .a4-sheet {
            width: 100%;
            min-height: var(--print-content-height);
            height: var(--print-content-height);
            margin: 0;
            border: none;
            border-radius: 0;
            box-shadow: none;
            break-inside: avoid-page;
            page-break-inside: avoid;
        }

        body.print-export-active .preview-wrapper .a4-sheet + .a4-sheet,
        body.pdf-export .preview-wrapper .a4-sheet + .a4-sheet {
            break-before: page;
            page-break-before: always;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .pdf-toolbar {
                display: none;
            }
            .page-wrapper {
                padding: 0;
            }
            .record-page {
                width: auto;
                min-height: auto;
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="pdf-toolbar">
        <h1>{{ __('recordes.titles.pdf') }}</h1>
        <div class="toolbar-actions">
            <button id="print-record" class="btn">{{ __('recordes.buttons.print') }}</button>
            <button id="download-pdf" class="btn btn-primary">{{ __('recordes.buttons.download_pdf') }}</button>
        </div>
    </div>

    <div class="page-wrapper" id="page-wrapper">
        <div id="record-document" class="record-page">
            @php
                $notAvailable = __('recordes.messages.not_available');
                $formatDisplayDate = static function ($value, bool $withTime = false) use ($notAvailable) {
                    if (empty($value)) {
                        return $notAvailable;
                    }

                    try {
                        $date = $value instanceof \Illuminate\Support\Carbon
                            ? $value
                            : \Illuminate\Support\Carbon::parse($value);
                    } catch (\Throwable $e) {
                        return $notAvailable;
                    }

                    if (class_exists(\App\Support\EthiopianDate::class)) {
                        return \App\Support\EthiopianDate::format($date, withTime: $withTime);
                    }

                    return $withTime ? $date->toDayDateTimeString() : $date->toDateString();
                };
                $sanitizeRichContent = static function (?string $html): string {
                    $cleaned = \Mews\Purifier\Facades\Purifier::clean((string) $html, 'default');
                    $withoutWrappedAnchors = preg_replace('#<a\b[^>]*>(.*?)</a>#is', '$1', $cleaned);
                    $withoutAnchors = preg_replace('#</?a\b[^>]*>#i', '', $withoutWrappedAnchors ?? $cleaned);

                    return $withoutAnchors ?? $cleaned;
                };
                $resolveEmbeddedImageSource = static function ($path) {
                    if (empty($path)) {
                        return null;
                    }

                    $trimmed = ltrim((string) $path);
                    if (\Illuminate\Support\Str::startsWith($trimmed, ['http://', 'https://', 'data:'])) {
                        return $trimmed;
                    }

                    $publicDiskPath = $trimmed;
                    if (\Illuminate\Support\Str::startsWith($publicDiskPath, ['/storage/', 'storage/'])) {
                        $publicDiskPath = preg_replace('#^/?storage/#', '', $publicDiskPath) ?? $publicDiskPath;
                    } elseif (\Illuminate\Support\Str::startsWith($publicDiskPath, ['public/'])) {
                        $publicDiskPath = substr($publicDiskPath, 7);
                    }

                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($publicDiskPath)) {
                        $absolutePath = \Illuminate\Support\Facades\Storage::disk('public')->path($publicDiskPath);
                        $mimeType = mime_content_type($absolutePath) ?: 'application/octet-stream';
                        $contents = @file_get_contents($absolutePath);

                        if ($contents !== false) {
                            return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
                        }
                    }

                    $publicCandidates = [
                        public_path(ltrim($trimmed, '/')),
                        public_path('storage/' . ltrim($publicDiskPath, '/')),
                    ];

                    foreach ($publicCandidates as $candidate) {
                        if (!is_file($candidate)) {
                            continue;
                        }

                        $mimeType = mime_content_type($candidate) ?: 'application/octet-stream';
                        $contents = @file_get_contents($candidate);
                        if ($contents !== false) {
                            return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
                        }
                    }

                    return null;
                };
            @endphp
            <div class="section">
                <h1>{{ __('recordes.titles.record') }}</h1>
                <div class="meta">
                    {{ __('recordes.labels.generated') }} 
                    @php
                        $generatedDate = now();
                    @endphp
                    {{ $formatDisplayDate($generatedDate, true) }}
                    <br>
                    {{ __('recordes.labels.case_number') }} {{ (string) ($case->case_number ?? $notAvailable) }} {{ __('recordes.labels.title_pipe') }} {{ (string) ($case->title ?? '') }}
                </div>

                <div class="card">
                    <div class="meta">
                        {{ __('recordes.labels.status_label') }} {{ (string) ($case->status ?? $notAvailable) }}
                        @if(!empty($case->caseType?->name))
                            | {{ __('recordes.labels.type') }} {{ $case->caseType->name }}
                        @endif
                        | {{ __('recordes.labels.opened') }} {{ $formatDisplayDate($case->filing_date) }}
                        | {{ __('recordes.labels.closed') }} {{ $closedAt ? $formatDisplayDate($closedAt) : __('recordes.labels.not_closed') }}
                    </div>

                    <div class="meta">
                        {{ __('recordes.labels.assigned_to') }} {{ $assignedUser->name ?? __('recordes.labels.unassigned') }}
                        @if(!empty($case->assigned_at))
                            | {{ __('recordes.labels.last_assigned') }} {{ $formatDisplayDate($case->assigned_at, true) }}
                        @endif
                    </div>

                    @if(($assignedTeams ?? collect())->isNotEmpty())
                        <div class="meta">{{ __('recordes.labels.teams') }} {{ $assignedTeams->join(', ') }}</div>
                    @endif

                    @if(!empty($case->judge_id))
                        <div class="meta">{{ __('recordes.labels.judge_id') }} {{ $case->judge_id }}</div>
                    @endif

                    @if(!empty($case->applicant))
                        <div class="meta">
                            {{ __('recordes.labels.applicant') }}
                            {{ trim(($case->applicant->first_name ?? '').' '.($case->applicant->middle_name ?? '').' '.($case->applicant->last_name ?? '')) }}
                            | {{ __('recordes.labels.email') }} {{ $case->applicant->email ?? $notAvailable }}
                            | {{ __('recordes.labels.phone') }} {{ $case->applicant->phone ?? $notAvailable }}
                        </div>
                    @endif

                    <div class="meta">
                        {{ __('recordes.labels.respondent') }} {{ (string) ($case->respondent_name ?? $notAvailable) }}
                        | {{ __('recordes.labels.address') }} {{ (string) ($case->respondent_address ?? $notAvailable) }}
                    </div>
                </div>
            </div>

            <div class="section">
                <h2>{{ __('recordes.labels.case_submission') }}</h2>
                <div class="meta">
                    {{ __('recordes.labels.case_number') }} {{ (string) ($case->case_number ?? $notAvailable) }} |
                    {{ __('recordes.labels.status_label') }} {{ (string) ($case->status ?? $notAvailable) }} |
                    {{ __('recordes.labels.filed') }}: {{ $formatDisplayDate($case->filing_date) }}
                    @if(!empty($case->caseType?->name))
                        | {{ __('recordes.labels.type') }} {{ $case->caseType->name }}
                    @endif
                </div>

                @if(!empty($case->applicant))
                    <div class="meta">
                        {{ __('recordes.labels.applicant') }}
                        {{ trim(($case->applicant->first_name ?? '').' '.($case->applicant->middle_name ?? '').' '.($case->applicant->last_name ?? '')) }}
                        | {{ __('recordes.labels.email') }} {{ $case->applicant->email ?? $notAvailable }}
                        | {{ __('recordes.labels.phone') }} {{ $case->applicant->phone ?? $notAvailable }}
                    </div>
                @endif
            </div>

            @php
                $firstEvidence = ($evidences ?? collect())->first();
                $firstEvidenceEmbedData = data_get($firstEvidenceEmbed ?? null, 'data');
                $firstEvidenceEmbedMime = data_get($firstEvidenceEmbed ?? null, 'mime', 'application/pdf');
                $normalizeSignerPayload = static function ($value) {
                    if ($value instanceof \Illuminate\Support\Collection) {
                        return $value;
                    }

                    if (is_string($value)) {
                        $decoded = json_decode($value, true);
                        return collect(is_array($decoded) ? $decoded : []);
                    }

                    if (is_array($value)) {
                        return collect($value);
                    }

                    return collect();
                };
            @endphp

            @if(!empty($case->description))
                <div class="section">
                    <h2>{{ __('recordes.labels.case_details') }}</h2>
                    <div class="content">{!! $sanitizeRichContent($case->description) !!}</div>
                </div>
            @endif

            @if(!empty($case->relief_requested))
                <div class="section">
                <h2>{{ __('recordes.labels.relief_requested') }}</h2>
                    <div class="content">{!! $sanitizeRichContent($case->relief_requested) !!}</div>
                </div>
            @endif

            <div class="section">
                <h2>{{ __('recordes.labels.witnesses') }}</h2>
                @forelse($witnesses ?? [] as $wit)
                    <div class="card">
                        <div><strong>{{ $wit->full_name ?? __('recordes.labels.witness') }}</strong></div>
                        <div class="meta">
                            {{ __('recordes.labels.phone') }} {{ $wit->phone ?? $notAvailable }} |
                            {{ __('recordes.labels.email') }} {{ $wit->email ?? $notAvailable }} |
                            {{ __('recordes.labels.address') }} {{ $wit->address ?? $notAvailable }}
                        </div>
                    </div>
                @empty
                    <div class="meta">{{ __('recordes.messages.no_witnesses') }}</div>
                @endforelse
            </div>

            <div class="section">
                <h2>{{ __('recordes.labels.submitted_documents') }}</h2>
                @forelse($files ?? [] as $file)
                    <div class="card">
                        <div><strong>{{ $file->label ?? __('recordes.labels.document') }}</strong></div>
                        <div class="meta">
                            {{ $formatDisplayDate($file->created_at, true) }}
                            @if(!empty($file->mime)) | {{ __('recordes.labels.mime') }} {{ $file->mime }} @endif
                            @if(!empty($file->size)) | {{ __('recordes.labels.size') }} {{ $file->size }} {{ __('recordes.labels.bytes') }} @endif
                        </div>
                    </div>
                @empty
                    <div class="meta">{{ __('recordes.messages.no_files') }}</div>
                @endforelse
            </div>

            @if($firstEvidence)
                <div class="section">
                    <h2>{{ __('recordes.labels.applicant_initial_pdf') }}</h2>
                    <div class="card">
                        <div><strong>{{ $firstEvidence->title ?? __('recordes.labels.document') }}</strong></div>
                        <div class="meta">
                            {{ $formatDisplayDate($firstEvidence->created_at, true) }}
                            @if(!empty($firstEvidence->mime)) | {{ __('recordes.labels.mime') }} {{ $firstEvidence->mime }} @endif
                            @if(!empty($firstEvidence->size)) | {{ __('recordes.labels.size') }} {{ $firstEvidence->size }} {{ __('recordes.labels.bytes') }} @endif
                        </div>
                        @if(!empty($firstEvidenceEmbedData))
                            <div class="pdf-preview">
                                <div id="applicant-pdf-viewer" class="pdf-rendered-viewer" aria-live="polite" style="max-height:none;">
                                    <div class="loading-text">{{ __('recordes.descriptions.applicant_pdf_loading') }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="section">
                <h2>{{ __('recordes.labels.applicant_evidence') }}</h2>
                @forelse($evidences ?? [] as $ev)
                    <div class="card">
                        <div><strong>{{ $ev->title ?? __('recordes.labels.document') }}</strong></div>
                        <div class="meta">{{ $formatDisplayDate($ev->created_at, true) }}</div>
                        @if(!empty($ev->description))
                            <div class="content">{!! $sanitizeRichContent($ev->description) !!}</div>
                        @endif
                        <div class="meta">
                            {{ __('recordes.labels.type') }} {{ $ev->type ?? __('recordes.labels.document') }}
                            @if(!empty($ev->mime)) | {{ __('recordes.labels.mime') }} {{ $ev->mime }} @endif
                            @if(!empty($ev->size)) | {{ __('recordes.labels.size') }} {{ $ev->size }} {{ __('recordes.labels.bytes') }} @endif
                        </div>
                    </div>
                @empty
                    <div class="meta">{{ __('recordes.messages.no_evidence') }}</div>
                @endforelse
            </div>

            <div class="section letters-section">
                <h2>{{ __('recordes.labels.letters_section') }}</h2>
                @php
                    $systemSettings = $systemSettings ?? \App\Models\SystemSetting::query()->first();
                @endphp
                @forelse($letters ?? [] as $letter)
                    @include('admin.letters.partials.inline-preview', [
                        'letter' => $letter,
                        'systemSettings' => $systemSettings
                    ])
                @empty
                    <div class="meta">{{ __('recordes.messages.no_letters') }}</div>
                @endforelse
            </div>

            <div class="section">
                <h2>{{ __('recordes.labels.respondent_responses') }}</h2>
                @php $responsePdfEmbeds = []; @endphp
                @forelse($respondentResponses ?? [] as $resp)
                    <div class="card">
                        <div><strong>{{ $resp->title ?? __('recordes.labels.response') }}</strong></div>
                        <div class="meta">{{ $formatDisplayDate($resp->created_at, true) }}</div>
                        @if(!empty($resp->description))
                            <div class="content">{!! $sanitizeRichContent($resp->description) !!}</div>
                        @endif
                        @if(!empty(data_get($resp, 'pdf_embed.data')))
                            @php
                                $viewerId = 'response-pdf-viewer-' . $loop->index;
                                $responsePdfEmbeds[] = [
                                    'id' => $viewerId,
                                    'data' => data_get($resp, 'pdf_embed.data'),
                                    'mime' => data_get($resp, 'pdf_embed.mime', 'application/pdf'),
                                    'title' => $resp->title ?? __('recordes.labels.response'),
                                ];
                            @endphp
                            <div class="pdf-preview" style="margin-top:12px;">
                                <div id="{{ $viewerId }}" class="pdf-rendered-viewer" aria-live="polite">
                                    <div class="loading-text">{{ __('recordes.descriptions.applicant_pdf_loading') }}</div>
                                </div>
                            </div>
                        @elseif(!empty($resp->download_url))
                            <div class="meta">
                                {{ __('recordes.labels.attachment') }} {{ $resp->title ?? __('recordes.labels.response') }}
                            </div>
                        @else
                            <div class="meta">{{ __('recordes.messages.no_files') }}</div>
                        @endif
                    </div>
                @empty
                    <div class="meta">{{ __('recordes.messages.no_responses') }}</div>
                @endforelse
            </div>

            <div class="section">
                <h2>{{ __('recordes.labels.hearings') }}</h2>
                @forelse($hearings ?? [] as $hearing)
                    @php
                        $hearingMoment = !empty($hearing->hearing_at)
                            ? \Illuminate\Support\Carbon::parse($hearing->hearing_at)
                            : null;
                        $hearingFormatted = $hearingMoment
                            ? $formatDisplayDate($hearingMoment, true)
                            : __('recordes.labels.hearing_unknown');
                    @endphp
                    <div class="card">
                        <div>
                            <strong>{{ __('recordes.labels.hearing_at') }} {{ $hearingFormatted }}</strong>
                        </div>
                        <div class="meta">{{ __('recordes.labels.location') }} {{ $hearing->location ?? $notAvailable }}</div>
                        @if(!empty($hearing->notes))
                            <div class="content">
                                <strong>{{ __('recordes.labels.hearing_notes') }}</strong>
                                {!! $sanitizeRichContent($hearing->notes) !!}
                            </div>
                        @endif
                        @if(!empty($hearing->judge_notes))
                            <div class="content">
                                <strong>{{ __('recordes.labels.judge_notes') }}</strong>
                                {!! $sanitizeRichContent($hearing->judge_notes) !!}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="meta">{{ __('recordes.messages.no_hearings') }}</div>
                @endforelse
            </div>

            <div class="section">
                <h2>{{ __('recordes.labels.bench_notes') }}</h2>
                @forelse($benchNotes ?? [] as $note)
                    @php
                        $manualJudges = collect([
                            [
                                'name' => $note->judge_one_name ?? null,
                                'date' => !empty($note->created_at) ? $formatDisplayDate($note->created_at, true) : '',
                                'title' => $note->judge_one_title ?? __('recordes.labels.judge'),
                                'signature' => $resolveEmbeddedImageSource($note->judge_one_signature ?? null),
                            ],
                            [
                                'name' => $note->judge_two_name ?? null,
                                'date' => !empty($note->created_at) ? $formatDisplayDate($note->created_at, true) : '',
                                'title' => $note->judge_two_title ?? __('recordes.labels.judge'),
                                'signature' => $resolveEmbeddedImageSource($note->judge_two_signature ?? null),
                            ],
                            [
                                'name' => $note->judge_three_name ?? null,
                                'date' => !empty($note->created_at) ? $formatDisplayDate($note->created_at, true) : '',
                                'title' => $note->judge_three_title ?? __('recordes.labels.judge'),
                                'signature' => $resolveEmbeddedImageSource($note->judge_three_signature ?? null),
                            ],
                        ])->filter(fn ($judge) => !empty($judge['name']));

                        // Display all judge names/dates, but only render signature block for judge 1
                        $judges = $manualJudges;
                        $signers = $manualJudges->take(1);
                    @endphp
                    <div class="card bench-note-entry">
                        @php
                            $benchCreated = !empty($note->created_at) ? \Illuminate\Support\Carbon::parse($note->created_at) : null;
                            $benchCreatedFormatted = $benchCreated ? $formatDisplayDate($benchCreated, true) : '';
                        @endphp
                        <div class="meta" style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:6px;">
                            <div style="font-weight:600;">{{ $note->title ?? __('recordes.labels.bench_notes') }}</div>
                            <div style="text-align:right;">
                                <strong>{{ __('recordes.labels.created') }}</strong> {{ $benchCreatedFormatted }}
                            </div>
                        </div>
                        @if($judges->isNotEmpty())
                            <div class="bench-judges-grid" style="margin-bottom:12px;">
                                @foreach($judges as $index => $judge)
                                    <div class="bench-judge-card" style="text-align:left;">
                                        <div class="bench-judge-name">{{ ($index + 1) }}. {{ $judge['name'] }}</div>
                                        @if(!empty($judge['date']))
                                            <div class="bench-judge-date">{{ $judge['date'] }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <div class="content">{!! $sanitizeRichContent($note->note ?? $note->body ?? '') !!}</div>
                        @if($signers->isNotEmpty())
                            <div class="bench-signatures" style="display:flex;gap:20px;flex-wrap:nowrap;justify-content:center;">
                                @foreach($signers as $signer)
                                    <div class="bench-signature-line" style="flex:1;">
                                        <div class="bench-signature-placeholder">
                                            @if(!empty($signer['signature']))
                                                <img src="{{ $signer['signature'] }}" alt="{{ $signer['name'] }}" style="max-height:45px;max-width:160px;object-fit:contain;">
                                            @endif
                                        </div>
                                        <div class="bench-signature-label">
                                            {{ $signer['name'] ?? __('recordes.labels.judge') }}
                                            @if(!empty($signer['title']))
                                                <br><span style="font-size:12px;">{{ $signer['title'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="meta">{{ __('recordes.messages.no_bench_notes') }}</div>
                @endforelse
            </div>

            <div class="section">
                <h2>{{ __('recordes.labels.final_judgment') }}</h2>
                @if(!empty($decision))
                    @php
                        $decisionSigners = $normalizeSignerPayload($decision->signatures ?? [])
                            ->map(fn ($signer) => [
                                'name' => data_get($signer, 'name') ?? data_get($signer, 'judge_name') ?? __('recordes.labels.judge'),
                                'title' => data_get($signer, 'title') ?? __('recordes.labels.judge'),
                                'signature' => $resolveEmbeddedImageSource(data_get($signer, 'signature')),
                            ])
                            ->filter(fn ($signer) => !empty($signer['name']))
                            ->take(3);

                        $decisionDate = !empty($decision->created_at) ? \Illuminate\Support\Carbon::parse($decision->created_at) : null;
                        $decisionDateFormatted = $decisionDate ? $formatDisplayDate($decisionDate, true) : '';
                    @endphp
                    <div class="card bench-note-entry">
                        <div class="meta" style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-bottom:6px;">
                            <div style="font-weight:600;">{{ $decision->title ?? __('recordes.labels.decision') }}</div>
                            <div style="text-align:right;">
                                <strong>{{ __('recordes.labels.created') }}</strong> {{ $decisionDateFormatted }}
                            </div>
                        </div>
                        @php
                            $decisionContent = $decision->decision_content ?? $decision->body ?? '';
                        @endphp
                        <div class="content">{!! $sanitizeRichContent($decisionContent) !!}</div>
                        @if($decisionSigners->isNotEmpty())
                            <div class="bench-signatures" style="display:flex;gap:20px;flex-wrap:nowrap;">
                                @foreach($decisionSigners as $signer)
                                    <div class="bench-signature-line" style="flex:1;">
                                        <div class="bench-signature-placeholder">
                                            @if(!empty($signer['signature']))
                                                <img src="{{ $signer['signature'] }}" alt="{{ $signer['name'] }}" style="max-height:45px;max-width:160px;object-fit:contain;">
                                            @endif
                                        </div>
                                        <div class="bench-signature-label">
                                            {{ $signer['name'] ?? __('recordes.labels.judge') }}
                                            @if(!empty($signer['title']))
                                                <br><span style="font-size:12px;">{{ $signer['title'] }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    <div class="meta">{{ __('recordes.messages.no_decision') }}</div>
                @endif
            </div>

        </div>
    </div>

    <script>
        const downloadBtn = document.getElementById('download-pdf');
        const printBtn = document.getElementById('print-record');
        if (downloadBtn) {
            downloadBtn.addEventListener('click', () => printRecord());
        }
        if (printBtn) {
            printBtn.addEventListener('click', () => printRecord());
        }

        const pageWrapper = document.getElementById('page-wrapper');
        const pageCounterOverlay = document.createElement('div');
        pageCounterOverlay.className = 'page-counter-overlay';
        pageCounterOverlay.style.display = 'none';
        document.body.appendChild(pageCounterOverlay);

        const recordDocument = document.getElementById('record-document');
        let previewFooters = [];
        let pageHeightPx = measurePageHeightPx();
        let exporting = false;
        let printPreparing = false;

        function measurePageHeightPx() {
            const ruler = document.createElement('div');
            ruler.style.height = '297mm';
            ruler.style.visibility = 'hidden';
            ruler.style.position = 'absolute';
            ruler.style.pointerEvents = 'none';
            document.body.appendChild(ruler);
            const height = ruler.getBoundingClientRect().height;
            ruler.remove();
            return height;
        }

        const updatePageCounter = () => {
            if (exporting) return;
            if (!pageWrapper || !pageCounterOverlay) return;
            const pageHeight = pageWrapper.clientHeight || pageHeightPx;
            const scrollTop = pageWrapper.scrollTop || window.scrollY;
            const scrollSize = pageWrapper.scrollHeight || document.documentElement.scrollHeight;
            const totalPages = Math.max(1, Math.ceil(scrollSize / pageHeight));
            const currentPage = Math.min(totalPages, Math.floor(scrollTop / pageHeight) + 1);
            if (totalPages <= 1) {
                pageCounterOverlay.style.display = 'none';
                return;
            }
            pageCounterOverlay.style.display = 'block';
            pageCounterOverlay.textContent = `{{ __('recordes.labels.page') }} ${currentPage} {{ __('recordes.labels.of') }} ${totalPages}`;
        };

        function renderPreviewFooters() {
            if (exporting) return;
            if (!recordDocument) return;
            previewFooters.forEach((footer) => footer.remove());
            previewFooters = [];
            const totalPages = Math.max(1, Math.ceil(recordDocument.scrollHeight / pageHeightPx));
            for (let i = 1; i <= totalPages; i++) {
                const footer = document.createElement('div');
                footer.className = 'page-preview-footer';
                footer.textContent = `{{ __('recordes.labels.page') }} ${i}`;
                footer.style.top = `${pageHeightPx * i - 30}px`;
                recordDocument.appendChild(footer);
                previewFooters.push(footer);
            }
        }

        const scheduleFooterRender = () => {
            pageHeightPx = measurePageHeightPx();
            renderPreviewFooters();
            updatePageCounter();
        };

        const cleanupPreviewArtifacts = () => {
            document.querySelectorAll('.page-preview-footer').forEach(el => el.remove());
            previewFooters = [];
            if (pageCounterOverlay) {
                pageCounterOverlay.style.display = 'none';
            }
        };

        const wait = (ms) => new Promise(resolve => setTimeout(resolve, ms));

        async function waitForOutputAssets(root) {
            const images = Array.from(root.querySelectorAll('img'));
            const iframes = Array.from(root.querySelectorAll('iframe'));
            const pending = [];

            for (const image of images) {
                if (image.complete) {
                    continue;
                }

                pending.push(new Promise(resolve => {
                    image.addEventListener('load', resolve, { once: true });
                    image.addEventListener('error', resolve, { once: true });
                }));
            }

            for (const frame of iframes) {
                if (frame.dataset.loaded === '1') {
                    continue;
                }

                pending.push(new Promise(resolve => {
                    const finalize = () => {
                        frame.dataset.loaded = '1';
                        resolve();
                    };

                    frame.addEventListener('load', () => setTimeout(finalize, 120), { once: true });
                    setTimeout(finalize, 1500);
                }));
            }

            if (document.fonts?.ready) {
                pending.push(document.fonts.ready.catch(() => undefined));
            }

            if (pending.length) {
                await Promise.allSettled(pending);
            }

            await new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)));
        }

        async function prepareOutputMode(mode) {
            cleanupPreviewArtifacts();
            if (mode === 'print') {
                printPreparing = true;
                document.body.classList.add('print-export-active');
            } else {
                exporting = true;
                document.body.classList.add('pdf-export');
            }

            await waitForOutputAssets(recordDocument);
            scheduleFooterRender();
            cleanupPreviewArtifacts();
            await wait(80);
        }

        function restoreOutputMode(mode) {
            if (mode === 'print') {
                printPreparing = false;
                document.body.classList.remove('print-export-active');
            } else {
                exporting = false;
                document.body.classList.remove('pdf-export');
            }

            scheduleFooterRender();
        }

        async function printRecord() {
            if (printPreparing || exporting) {
                return;
            }

            if (downloadBtn) {
                downloadBtn.disabled = true;
            }
            if (printBtn) {
                printBtn.disabled = true;
            }

            await prepareOutputMode('print');
            window.print();

            setTimeout(() => {
                if (printPreparing) {
                    restoreOutputMode('print');
                    if (downloadBtn) {
                        downloadBtn.disabled = false;
                    }
                    if (printBtn) {
                        printBtn.disabled = false;
                    }
                }
            }, 1500);
        }

        if (pageWrapper) {
            pageWrapper.addEventListener('scroll', updatePageCounter);
        } else {
            window.addEventListener('scroll', updatePageCounter);
        }
        window.addEventListener('resize', scheduleFooterRender);
        window.addEventListener('afterprint', () => {
            if (!printPreparing) {
                return;
            }

            restoreOutputMode('print');
            if (downloadBtn) {
                downloadBtn.disabled = false;
            }
            if (printBtn) {
                printBtn.disabled = false;
            }
        });
        document.addEventListener('DOMContentLoaded', scheduleFooterRender);
        scheduleFooterRender();

        document.addEventListener('keydown', (event) => {
            if ((event.ctrlKey || event.metaKey) && event.key === 's') {
                event.preventDefault();
                printRecord();
            }
        });

        const applicantPdfData = @json($firstEvidenceEmbedData);
        const responsePdfEmbeds = @json($responsePdfEmbeds ?? []);

        function renderApplicantPdfFallback(container) {
            if (!applicantPdfData) {
                container.innerHTML = '<div class="loading-text">{{ __('recordes.descriptions.applicant_pdf_missing') }}</div>';
                scheduleFooterRender();
                return;
            }

            const iframe = document.createElement('iframe');
            iframe.className = 'fallback-pdf-frame';
            iframe.title = '{{ __('recordes.labels.applicant_initial_pdf') }}';
            iframe.scrolling = 'no';
            iframe.src = 'data:{{ $firstEvidenceEmbedMime }};base64,' + applicantPdfData + '#toolbar=0&navpanes=0&scrollbar=0';
            iframe.addEventListener('load', () => {
                iframe.dataset.loaded = '1';
                scheduleFooterRender();
            }, { once: true });
            container.innerHTML = '';
            container.appendChild(iframe);
            scheduleFooterRender();
        }

        async function renderApplicantPdf() {
            const container = document.getElementById('applicant-pdf-viewer');

            if (!container || !applicantPdfData) {
                return;
            }

            renderApplicantPdfFallback(container);
        }

        if (applicantPdfData) {
            window.addEventListener('load', renderApplicantPdf);
        }

        document.addEventListener('DOMContentLoaded', () => {
            initializeInlineLetterPreviews();
        });

        function renderResponsePdfFallback(container, embed) {
            if (!container) return;
            const iframe = document.createElement('iframe');
            iframe.className = 'fallback-pdf-frame';
            iframe.title = embed.title || '{{ __('recordes.labels.respondent_responses') }}';
            iframe.scrolling = 'no';
            iframe.src = `data:${embed.mime || 'application/pdf'};base64,${embed.data}#toolbar=0&navpanes=0&scrollbar=0`;
            iframe.addEventListener('load', () => {
                iframe.dataset.loaded = '1';
                scheduleFooterRender();
            }, { once: true });
            container.innerHTML = '';
            container.appendChild(iframe);
        }

        async function renderResponsePdf(embed) {
            const container = document.getElementById(embed.id);
            if (!container || !embed.data) {
                return;
            }

            renderResponsePdfFallback(container, embed);
            scheduleFooterRender();
        }

        function renderAllResponsePdfs() {
            if (!responsePdfEmbeds.length) {
                return;
            }
            (async () => {
                for (const embed of responsePdfEmbeds) {
                    await renderResponsePdf(embed);
                }
            })();
        }

        if (responsePdfEmbeds.length) {
            window.addEventListener('load', renderAllResponsePdfs);
        }

        function initializeInlineLetterPreviews() {
            const wrappers = document.querySelectorAll('[data-letter-preview-id]');
            if (!wrappers.length) {
                return;
            }

            wrappers.forEach(wrapper => renderLetterPreview(wrapper));
        }

        function renderLetterPreview(wrapper) {
            const previewId = wrapper.getAttribute('data-letter-preview-id');
            if (!previewId) return;

            const previewContainer = document.getElementById(`preview-container-${previewId}`);
            const sheetTemplate = document.getElementById(`sheet-template-${previewId}`);
            const rawContent = document.getElementById(`raw-content-${previewId}`);
            const rawBody = document.getElementById(`raw-body-content-${previewId}`);

            if (!previewContainer || !sheetTemplate || !rawContent || !rawBody) {
                return;
            }

            function splitTextNode(node) {
                if (node.nodeType !== Node.ELEMENT_NODE || ['TABLE', 'IMG', 'UL', 'OL'].includes(node.tagName)) {
                    return [node.cloneNode(true)];
                }

                const text = node.innerText || '';
                const words = text.trim().split(/\s+/);
                if (words.length < 50) {
                    return [node.cloneNode(true)];
                }

                const chunks = [];
                let currentChunk = [];

                words.forEach((word, index) => {
                    currentChunk.push(word);
                    if (currentChunk.length >= 40 || index === words.length - 1) {
                        const p = document.createElement('p');
                        p.className = node.className;
                        p.style.cssText = node.style.cssText;
                        p.style.marginBottom = '0';
                        p.style.textAlign = 'justify';
                        p.innerText = currentChunk.join(' ');
                        chunks.push(p);
                        currentChunk = [];
                    }
                });

                return chunks;
            }

            const contentQueue = [];

            rawContent.querySelectorAll('.content-block[data-role="before-body"]').forEach(el => {
                contentQueue.push(el.cloneNode(true));
            });

            Array.from(rawBody.children).forEach(child => {
                splitTextNode(child).forEach(part => contentQueue.push(part));
            });

            rawContent.querySelectorAll('.content-block[data-role="after-body"]').forEach(el => {
                contentQueue.push(el.cloneNode(true));
            });

            function createNewPage() {
                const clone = sheetTemplate.content.cloneNode(true);
                const sheet = clone.querySelector('.a4-sheet');
                const contentSlot = clone.querySelector('.content-slot');
                previewContainer.appendChild(sheet);
                return { sheet, contentSlot };
            }

            function getAvailableHeight(sheetElement) {
                const header = sheetElement.querySelector('.letter-header');
                const footer = sheetElement.querySelector('.letter-footer');
                const bodyContainer = sheetElement.querySelector('.letter-body-container');
                if (!bodyContainer) {
                    return sheetElement.clientHeight;
                }
                const style = window.getComputedStyle(bodyContainer);
                const padding = parseFloat(style.paddingTop) + parseFloat(style.paddingBottom);
                return sheetElement.clientHeight - header.offsetHeight - footer.offsetHeight - padding - 5;
            }

            let currentPage = createNewPage();
            let maxHeight = getAvailableHeight(currentPage.sheet);

            contentQueue.forEach(node => {
                const clone = node.cloneNode(true);
                currentPage.contentSlot.appendChild(clone);

                if (currentPage.contentSlot.offsetHeight > maxHeight) {
                    currentPage.contentSlot.removeChild(clone);
                    currentPage = createNewPage();
                    maxHeight = getAvailableHeight(currentPage.sheet);
                    currentPage.contentSlot.appendChild(clone);
                }
            });
        }

    </script>
</body>
</html>
