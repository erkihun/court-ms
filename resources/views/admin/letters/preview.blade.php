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

    .preview-toolbar {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .preview-wrapper {
        max-height: calc(297mm * 2 + 50px);
        overflow-y: auto;
        padding: 1rem;
        background: #e5e7eb;
        border-radius: 1.5rem;
        scroll-snap-type: y proximity;
    }

    .a4-sheet {
        width: 210mm;
        height: 297mm;
        background: #fff;
        margin: 0 auto 1rem;
        box-shadow: 0 5px 25px rgba(15, 23, 42, 0.2);
        border-radius: 1rem;
        display: grid;
        grid-template-rows: auto 1fr auto;
        overflow: hidden;
        scroll-snap-align: start;
        position: relative;
    }

    .letter-header,
    .letter-footer {
        padding: 0 15mm;
    }

    .letter-header img,
    .letter-footer img {
        max-height: 110px;
        width: 100%;
        display: block;
        object-fit: cover;
        margin-bottom: 6mm;
    }

    .letter-body {
        padding: 0 15mm;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .letter-body .subject-line,
    .letter-header .subject-line {
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

    .meta-line strong {
        font-weight: 600;
    }

    .hidden {
        display: none;
    }

    @media print {
        body {
            background: #fff;
            padding: 0;
        }

        .preview-wrapper,
        .preview-toolbar {
            background: none;
            padding: 0;
        }

        .a4-sheet {
            box-shadow: none;
            margin: 0 auto 20mm;
            border-radius: 0;
        }

        .letter-body {
            padding: 0 10mm;
        }
    }
    </style>
</head>

<body>
    @php
    $letterDate = optional($letter->created_at)->format('F j, Y') ?? now()->format('F j, Y');
    $authorName = optional($letter->author)->name ?? '[Your Name]';
    $authorTitle = optional($letter->author)->title ?? '[Your Title]';
    $recipientCompanies = $letter->recipient_company
        ? array_filter(array_map('trim', explode(',', $letter->recipient_company)))
        : [];
    $ccRecipients = $letter->cc
        ? array_filter(array_map('trim', explode(',', $letter->cc)))
        : [];
    @endphp

    <div class="preview-toolbar">
        <button type="button"
            class="px-3 py-1.5 rounded-md border border-gray-300 text-xs font-semibold text-gray-700 hover:bg-gray-50"
            onclick="printLetter()">Print</button>
        <button type="button"
            class="px-3 py-1.5 rounded-md border border-gray-300 text-xs font-semibold text-gray-700 hover:bg-gray-50"
            onclick="saveLetterPdf()">Save as PDF</button>
    </div>

    <div class="preview-wrapper">
        <div id="preview-pages"></div>
    </div>

    <template id="layout-template">
        <div class="a4-sheet">
            <div class="letter-header"></div>
            <div class="letter-body">
                <div class="content-slot"></div>
            </div>
            <div class="letter-footer"></div>
        </div>
    </template>

    <template id="header-template">
        <div class="letter-header">
            @if($template->header_image_path)
            <img src="{{ asset('storage/' . $template->header_image_path) }}" alt="Letter Header">
            @endif
            <div class="flex justify-between mt-3 text-xs text-gray-600">
                <span class="text-left flex-1">Ref No: <strong class="text-gray-900">{{ $letter->reference_number ?? '—' }}</strong></span>
                <span class="text-right flex-1">Date: <strong class="text-gray-900">{{ $letterDate }}</strong></span>
            </div>
        </div>
    </template>

    <template id="footer-template">
        <div class="letter-footer">
            @if($template->footer_image_path)
            <img src="{{ asset('storage/' . $template->footer_image_path) }}" alt="Letter Footer">
            @endif
        </div>
    </template>

    <div id="letter-content" class="hidden">
        <div class="recipient-block">
            <p style="margin:0;">
                <strong>{{ $letter->recipient_name }}</strong>
                @if($letter->recipient_title)
                {{ $letter->recipient_title }}<br>
                @endif
            </p>
        </div>
        @if($recipientCompanies)
        <div class="meta-line">
            <ul class="list-disc pl-6 mt-1 text-xs text-gray-600">
                @foreach($recipientCompanies as $company)
                <li>{{ $company }}</li>
                @endforeach
            </ul>
        </div>
        @endif
        <div class="subject-line">
            Subject: {{ $letter->subject ?: $template->title }}
        </div>
        <div class="body-block">
            {!! nl2br(e($letter->body)) !!}
        </div>
        <div class="closing-block" style="text-align:right;">
            <div class="signature-space"></div>
            <p>Sincerely,</p>
            <p>{{ $authorName }}<br>{{ $authorTitle }}</p>
        </div>
        @if($ccRecipients)
        <div class="meta-line">
            <strong>CC recipients:</strong>
            <ul class="list-disc pl-6 mt-1 text-xs text-gray-600">
                @foreach($ccRecipients as $ccRecipient)
                <li>{{ $ccRecipient }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const previewPages = document.getElementById('preview-pages');
        const layoutTemplate = document.getElementById('layout-template');
        const headerTemplate = document.getElementById('header-template');
        const footerTemplate = document.getElementById('footer-template');
        const contentContainer = document.getElementById('letter-content');
        if (!previewPages || !layoutTemplate || !headerTemplate || !footerTemplate || !contentContainer) {
            return;
        }

        const nodes = Array.from(contentContainer.children);
        contentContainer.remove();

        function createPage() {
            const clone = layoutTemplate.content.cloneNode(true);
            const sheet = clone.querySelector('.a4-sheet');
            const bodySlot = clone.querySelector('.content-slot');
            const bodyContainer = sheet.querySelector('.letter-body');
            const header = headerTemplate.content.cloneNode(true).firstElementChild;
            const footer = footerTemplate.content.cloneNode(true).firstElementChild;
            sheet.insertAdjacentElement('afterbegin', header);
            sheet.appendChild(footer);
            previewPages.appendChild(sheet);
            return { body: bodySlot, bodyContainer };
        }

        let currentPage = null;
        let maxHeight = 0;

        function ensurePage() {
            if (!currentPage) {
                currentPage = createPage();
                maxHeight = currentPage.bodyContainer.clientHeight || 280;
            }
        }

        nodes.forEach(node => {
            ensurePage();
            const clone = node.cloneNode(true);
            currentPage.body.appendChild(clone);
            if (currentPage.bodyContainer.scrollHeight > maxHeight) {
                currentPage.body.removeChild(clone);
                currentPage = null;
                ensurePage();
                currentPage.body.appendChild(clone);
            }
        });

        ensurePage();
    });

    function printLetter() {
        window.print();
    }

    function saveLetterPdf() {
        window.print();
    }
    </script>
</body>

</html>
