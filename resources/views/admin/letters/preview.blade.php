{{-- resources/views/letters/preview.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Letter Preview</title>
    <style>
        :root {
            font-family: 'Times New Roman', serif;
            color: #0f172a;
        }

        body {
            background: #f3f4f6;
            padding: 2rem;
        }

        .a4-sheet {
            width: 210mm;
            min-height: 297mm;
            background: #fff;
            margin: 0 auto;
            box-shadow: 0 5px 25px rgba(15, 23, 42, 0.2);
            padding: 5mm 10mm;
            display: flex;
            flex-direction: column;
            gap: 10mm;
        }

        .sender-block {
            text-align: left;
            line-height: 1.4;
            width: 100%;
        }

        .sender-block img {
            max-height: 120px;
            margin-bottom: 6mm;
            width: 210mm;

        }

        .sender-lines {
            font-size: 13px;
        }

        .subject-line {
            font-weight: bold;
            font-size: 15px;
            margin-top: 5mm;
            text-align: center;
        }

        .body-block,
        .recipient-block,
        .closing-block {
            font-size: 14px;
            line-height: 1.6;
            text-align: justify;
        }

        .signature-space {
            height: 25mm;
        }

        .meta-line {
            font-size: 12px;
            margin-top: 4mm;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .a4-sheet {
                box-shadow: none;
                margin: 0;
                width: auto;
                min-height: auto;
            }
        }
    </style>
</head>

<body>
    @php
    $letterDate = optional($letter->created_at)->format('F j, Y') ?? now()->format('F j, Y');
    @endphp
    <div class="a4-sheet">
        <div class="sender-block">
            @if($template->header_image_path)
            <img src="{{ asset('storage/' . $template->header_image_path) }}" alt="Letter Header">
            @endif
            <div class="date-line" style="text-align:right; font-size:14px;">{{ $letterDate }}</div>

        </div>

        <div class="recipient-block">
            <p style="margin:0;">
                <strong>{{ $letter->recipient_name }}</strong><br>
                @if($letter->recipient_title)
                {{ $letter->recipient_title }}<br>
                @endif
                @if($letter->recipient_company)
                <strong>{{ $letter->recipient_company }}</strong><br>
                @endif

            </p>
        </div>

        <div class="subject-line">
            Subject: {{ $letter->subject ?: $template->title }}
        </div>

        <div class="body-block">
            {!! nl2br(e($letter->body)) !!}
        </div>

        <div class="closing-block" style="text-align:right;">

            <div class="signature-space"></div>
            <p>Sincerely,</p>
            <p>

                {{ optional($letter->author)->name ?? '[Your Name]' }}<br>
                {{ optional($letter->author)->title ?? '[Your Title]' }}
            </p>
        </div>

        <div class="meta-line">
            ግልባጭ <br> {{ $letter->cc ?? '____________________' }}
        </div>

        @if($template->footer_image_path)
        <div style="margin-top:auto, ">
            <img src="{{ asset('storage/' . $template->footer_image_path) }}" alt="Letter Footer" style="max-height:100px;  width: 210mm;">
        </div>
        @endif
    </div>
</body>

</html>