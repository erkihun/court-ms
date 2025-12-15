<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('letters.titles.preview') }}</title>

    {{-- 1. Include html2pdf library for direct PDF download (self-hosted to avoid tracking blocks) --}}
    <script src="{{ asset('vendor/html2pdf/html2pdf.bundle.min.js') }}"></script>

    <style>
    :root {
        /* Standard Letter Fonts */
        font-family: 'Times New Roman', Times, serif;
        color: #0f172a;
        --a4-width: 210mm;
        --a4-height: 297mm;
    }

    * {
        box-sizing: border-box;
    }

    body {
        background: #e5e7eb;
        margin: 0;
        padding: 2rem;
        min-height: 100vh;
    }

    .page-seal-bottom {
        position: absolute;
        bottom: 30mm;
        right: 32mm;
        z-index: 5;
        max-height: 110px;
        max-width: 110px;
        display: flex;
        justify-content: center;
        align-items: center;
        pointer-events: none;
    }

    .page-seal-bottom img {
        max-height: 150px;
        max-width: 150px;
        object-fit: contain;
        display: block;
        opacity: 0.8;
    }


    /* Adjust the closing block for better flow, removing the previous complex container */
    .closing-block {
        margin-top: 5mm;
        margin-bottom: 5mm;
        text-align: right;
        /* Revert to standard right-alignment for signature */
        color: #0f172a;
    }

    /* Toolbar Styling */
    .preview-toolbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background: white;
        padding: 1rem 2rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        z-index: 1000;
    }

    .toolbar-actions {
        display: flex;
        gap: 1rem;
    }

    .btn {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        background: white;
        font-size: 0.875rem;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    .btn-primary {
        background: #0f172a;
        color: white;
        border: 1px solid #0f172a;
    }

    .btn-primary:hover {
        background: #1e293b;
    }

    /* Preview Container */
    .preview-wrapper {
        margin-top: 60px;
        /* Space for toolbar */
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2rem;
    }

    /* A4 Sheet Logic */
    .a4-sheet {
        width: var(--a4-width);
        height: var(--a4-height);
        background: #fff;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        padding-top: 2mm;
        position: relative;
        overflow: hidden;
        /* Strict A4 */

        /* Flex Column to push footer to bottom */
        display: flex;
        flex-direction: column;
    }

    /* Letter Layout */
    .letter-header,
    .letter-footer {
        flex-shrink: 0;
        /* Never shrink header/footer */
        width: 100%;
    }

    .letter-header img,
    .letter-footer img {
        width: 100%;
        height: 32mm;
        max-height: 32mm;
        object-fit: contain;
        display: block;
    }

    .letter-footer {
        position: relative;
        height: 34mm;
        flex-shrink: 0;
        margin: 0;
        padding-right: 32mm;
        overflow: hidden;
    }

    .letter-body-container {
        flex-grow: 1;
        padding: 0 20mm 0 20mm;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .qr-block {
        position: absolute;
        right: 4mm;
        bottom: 3mm;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 25mm;
        height: 25mm;
    }

    .qr-block svg {
        width: 25mm;
        height: 25mm;
        display: block;
    }

    .content-slot {
        font-size: 12pt;
        /* Standard letter size */
        line-height: 1.5;
        text-align: justify;
    }

    /* Specific Letter Components */
    .meta-header {
        display: flex;
        justify-content: space-between;
        margin-top: 8mm;
        margin-bottom: 5mm;
        font-size: 12pt;
    }

    .recipient-block {
        margin-bottom: 2mm;

    }

    .subject-line {
        font-weight: bold;
        text-decoration: underline;
        text-align: center;
        margin: 5mm 0;
        text-transform: uppercase;
    }

    .body-text p {
        margin-bottom: 0.5em;
    }

    .closing-block {
        margin-top: 5mm;
        margin-bottom: 5mm;
        /* Align right for some formats, left for others. Standard modern is left. */
        text-align: right;
        color: #5539CC;
    }

    .signature-space {
        height: 10mm;
    }

    .cc-block {
        font-size: 12pt;
        margin-top: 5mm;

    }



    /* Print Settings */
    @media print {
        @page {
            size: A4 portrait;
            margin: 0;
            /* Remove browser margins */
        }

        body {
            background: white;
            padding: 0;
            margin: 0;
            -webkit-print-color-adjust: exact;
        }

        .preview-toolbar {
            display: none !important;
        }

        .preview-wrapper {
            margin-top: 0;
            gap: 0;
            display: block;
        }

        .a4-sheet {
            box-shadow: none;
            margin: 0;
            page-break-after: always;
            height: var(--a4-height);
            /* Enforce height in print */
        }

        .a4-sheet:last-child {
            page-break-after: auto;
        }
    }
    </style>
</head>

<body>
    @php
    $letterDate = optional($letter->created_at)->format('F j, Y') ?? now()->format('F j, Y');
    $isApproved = ($letter->approval_status === 'approved');
    $authorName = (function () use ($letter) {
    $full = optional($letter->author)->name;
    if (!$full) return '';
    $parts = array_values(array_filter(explode(' ', $full)));
    return trim(implode(' ', array_slice($parts, 0, 2))) ?: $full;
    })();
    $authorTitle = optional($letter->author)->title ?? '';
    $authorSignature = optional($letter->author)->signature_url ?? null;
    $systemSettings = \App\Models\SystemSetting::query()->first();
    $approvalName = $letter->approved_by_name ?? null;
    $approvalTitle = $letter->approved_by_title ?? null;
    $displayName = $isApproved ? ($approvalName ?: $authorName) : '';
    $displayTitle = $isApproved ? ($approvalTitle ?: (optional($letter->author)->position ?? $authorTitle)) : '';

    $recipientCompanies = $letter->recipient_company
    ? array_filter(array_map('trim', explode(',', $letter->recipient_company)))
    : [];

    $ccRecipients = $letter->cc
    ? array_filter(array_map('trim', explode(',', $letter->cc)))
    : [];

    // Ensure the body allows basic formatting tags but strips malicious scripts
    $safeBody = \Mews\Purifier\Facades\Purifier::clean($letter->body ?? '', 'default');

    // QR payload
    $qrPayload = implode('|', [
    'REF:' . \Illuminate\Support\Str::limit($letter->reference_number ?? 'NA', 20, ''),
    'CASE:' . \Illuminate\Support\Str::limit($letter->case_number ?? 'NA', 20, ''),
    'STATUS:' . ($letter->approval_status ?? 'pending'),
    'DATE:' . ($letter->created_at?->format('Y-m-d') ?? now()->format('Y-m-d')),
    ]);
    $qrSvg = null;
    $qrError = null;

    if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
    try {
    $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
    ->encoding('UTF-8')
    ->size(120)
    ->errorCorrection('L')
    ->generate($qrPayload);
    } catch (\Throwable $e) {
    $qrError = $e->getMessage();
    }
    }

    // Fallback to pure BaconQrCode if the Laravel facade fails (e.g., missing GD on host)
    if (!$qrSvg && class_exists(\BaconQrCode\Writer::class)) {
    try {
    $renderer = new \BaconQrCode\Renderer\ImageRenderer(
    new \BaconQrCode\Renderer\RendererStyle\RendererStyle(120),
    new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
    );
    $writer = new \BaconQrCode\Writer($renderer);
    $qrSvg = $writer->writeString($qrPayload);
    } catch (\Throwable $e) {
    $qrError = $qrError ?: $e->getMessage();
    }
    }

    if (!$qrSvg && $qrError) {
    \Illuminate\Support\Facades\Log::warning('QR code generation failed for letter preview', [
    'reference' => $letter->reference_number,
    'error' => $qrError,
    ]);
    }
    @endphp

    <div class="preview-toolbar">
        <div id="page-counter" class="text-sm font-semibold text-gray-600">Loading Preview...</div>
        <div class="toolbar-actions">
            <button type="button" class="btn" onclick="printLetter()">
                Print / Save Native PDF
            </button>
            <button type="button" class="btn btn-primary" onclick="downloadDirectPDF()">
                Download PDF
            </button>
        </div>
    </div>

    <div class="preview-wrapper" id="preview-container">
    </div>

    <div id="raw-content" style="display: none;">

        <div class="content-block" data-type="meta">
            <div class="meta-header">
                <div>
                    <strong>{{ __('letters.preview.ref_no') }}</strong>
                    {{ $letter->reference_number ?? __('letters.cards.missing') }}
                </div>
                <div>
                    <strong>{{ __('letters.preview.date') }}</strong> {{ $letterDate }}
                </div>
            </div>
        </div>

        <div class="content-block" data-type="recipient">
            <div class="recipient-block">
                @if($recipientCompanies)
                @foreach($recipientCompanies as $company)
                <div class="pl-4"><span class="mr-3 font-bold">✓</span> {{ $company }}</div>
                @endforeach
                @endif
            </div>
            <strong style="text-decoration: underline;">{{ $letter->recipient_name }}</strong>
        </div>

        <div class="content-block" data-type="subject">
            <div class="subject-line">
                {{ __('letters.preview.subject') }}
                {{ $letter->subject ?: ($template->title ?? __('letters.cards.untitled')) }}
            </div>
        </div>

        <div id="raw-body-content">
            {!! $safeBody !!}
        </div>

        <div class="content-block" data-type="closing">
            <div class="closing-block">
                @if($isApproved)
                <div class="signature-space">
                    <div class="inline-flex items-end gap-6">
                        @if($authorSignature)
                        <div class="flex flex-col items-center gap-1">
                            <img src="{{ $authorSignature }}" alt="{{ __('letters.preview.author_signature') }}"
                                style="max-height:50px; max-width:160px; object-fit:contain;">

                        </div>
                        @endif

                    </div>
                </div>
                <p style="margin-top: 0.5rem; ">
                    <strong style="justify-content: center;">{{ $displayName }}<br>
                        {{ $displayTitle }}</strong>
                </p>
                @endif
            </div>
        </div>

        @if(count($ccRecipients) > 0)
        <div class="content-block" data-type="cc">
            <div class="cc-block">
                <strong>{{ __('letters.preview.cc_recipients') }}</strong>
                <ul class="list-disc mt-1 ml-5">
                    @foreach($ccRecipients as $cc)
                    <li>{{ $cc }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>

    <template id="sheet-template">
        <div class="a4-sheet">
            <div class="letter-header">
                @if($template->header_image_path)
                <img src="{{ asset('storage/' . $template->header_image_path) }}" crossorigin="anonymous" alt="Header">
                @else
                <div style="height: 20mm;"></div> @endif
            </div>

            <div class="letter-body-container">
                <div class="content-slot"></div>
            </div>
            {{-- Official seal (approved letters only) --}}
            @if($isApproved && optional($systemSettings)->seal_path)
            <div class="page-seal-bottom">
                <img src="{{ asset('storage/'.$systemSettings->seal_path) }}" crossorigin="anonymous"
                    alt="Official Seal">
            </div>
            @endif
            <div class="letter-footer">
                @if($template->footer_image_path)
                <img src="{{ asset('storage/' . $template->footer_image_path) }}" crossorigin="anonymous" alt="Footer">
                @endif
                @if($qrSvg)
                <div class="qr-block">
                    {!! $qrSvg !!}
                </div>
                @endif
            </div>
        </div>
    </template>
    <script>
    document.addEventListener('DOMContentLoaded', async () => {
        const previewContainer = document.getElementById('preview-container');
        const sheetTemplate = document.getElementById('sheet-template');
        const rawBody = document.getElementById('raw-body-content');
        const pageCounter = document.getElementById('page-counter');

        // --- HELPER: Split large text blocks into smaller chunks ---
        // This allows text to flow across pages instead of jumping as one big block
        function splitTextNode(node) {
            // If it's not a text-heavy element (like a paragraph or div), return as is
            if (node.nodeType !== Node.ELEMENT_NODE || ['TABLE', 'IMG', 'UL', 'OL'].includes(node
                    .tagName)) {
                return [node.cloneNode(true)];
            }

            // Get text and words
            const text = node.innerText;
            // Split by space (works for Amharic and English usually)
            const words = text.split(' ');

            // If it's small enough (less than 50 words), keep it as one block
            if (words.length < 50) {
                return [node.cloneNode(true)];
            }

            // Create chunks of ~40 words
            const chunks = [];
            let currentChunk = [];

            words.forEach((word, index) => {
                currentChunk.push(word);
                // Every 40 words, or at the end, seal the chunk
                if (currentChunk.length >= 40 || index === words.length - 1) {
                    const p = document.createElement('p');
                    // Maintain original styles/classes if needed
                    p.className = node.className;
                    p.style.cssText = node.style.cssText;
                    p.style.marginBottom = '0'; // Reduce gap between split chunks
                    p.style.textAlign = 'justify'; // Keep alignment
                    p.innerText = currentChunk.join(' ');
                    chunks.push(p);
                    currentChunk = [];
                }
            });

            return chunks;
        }

        // 1. Prepare Content Queue
        let contentQueue = [];

        // Add Meta, Recipient, Subject first (Keep them whole)
        document.querySelectorAll('#raw-content > .content-block').forEach(el => {
            if (el.dataset.type !== 'closing' && el.dataset.type !== 'cc') {
                contentQueue.push(el.cloneNode(true));
            }
        });

        // Process Body Content with Splitting Logic
        Array.from(rawBody.children).forEach(child => {
            // Try to split this child into smaller pieces
            const brokenDownParts = splitTextNode(child);
            brokenDownParts.forEach(part => contentQueue.push(part));
        });

        // Add Closing and CC last
        document.querySelectorAll('#raw-content > .content-block').forEach(el => {
            if (el.dataset.type === 'closing' || el.dataset.type === 'cc') {
                contentQueue.push(el.cloneNode(true));
            }
        });

        // 2. Helper to Create a New Page
        function createNewPage() {
            const clone = sheetTemplate.content.cloneNode(true);
            const sheet = clone.querySelector('.a4-sheet');
            const contentSlot = clone.querySelector('.content-slot');
            previewContainer.appendChild(sheet);
            return {
                sheet,
                contentSlot
            };
        }

        // 3. Pagination Logic
        let currentPage = createNewPage();
        let pageIndex = 1;

        function getAvailableHeight(sheetElement) {
            const header = sheetElement.querySelector('.letter-header');
            const footer = sheetElement.querySelector('.letter-footer');
            const bodyContainer = sheetElement.querySelector('.letter-body-container');
            const style = window.getComputedStyle(bodyContainer);
            const vPadding = parseFloat(style.paddingTop) + parseFloat(style.paddingBottom);

            // Return height minus a small buffer (5px)
            return sheetElement.clientHeight - header.offsetHeight - footer.offsetHeight - vPadding - 5;
        }

        // Process Queue
        while (contentQueue.length > 0) {
            const node = contentQueue.shift();

            currentPage.contentSlot.appendChild(node);

            const currentContentHeight = currentPage.contentSlot.offsetHeight;
            const maxAllowedHeight = getAvailableHeight(currentPage.sheet);

            if (currentContentHeight > maxAllowedHeight) {
                // If it doesn't fit...

                if (currentPage.contentSlot.children.length > 1) {
                    // If page has other items, move THIS item to next page
                    currentPage.contentSlot.removeChild(node);
                    currentPage = createNewPage();
                    pageIndex++;
                    currentPage.contentSlot.appendChild(node);
                } else {
                    // If the page is empty and it STILL doesn't fit, 
                    // it means this specific chunk is just huge. 
                    // We leave it here to clip (better than infinite loop).
                    console.warn("Single element is bigger than a whole page.");
                }
            }
        }

        pageCounter.innerText = `Preview: ${pageIndex} Page(s)`;
    });

    function printLetter() {
        window.print();
    }

    function downloadDirectPDF() {
        const element = document.getElementById('preview-container');

        // OPTIMIZED SETTINGS FOR LETTER PDF
        const opt = {
            margin: 0,
            filename: 'letter.pdf',
            image: {
                type: 'jpeg',
                quality: 0.98
            },
            // Higher scale = sharper text
            html2canvas: {
                scale: 2,
                useCORS: true,
                scrollY: 0
            },
            jsPDF: {
                unit: 'mm',
                format: 'a4',
                orientation: 'portrait'
            },
            // This setting is crucial for cutting pages correctly
            pagebreak: {
                mode: ['css', 'legacy']
            }
        };

        const btn = document.querySelector('.btn-primary');
        const originalText = btn.innerText;
        btn.innerText = 'Generating PDF...';

        html2pdf().set(opt).from(element).save().then(() => {
            btn.innerText = originalText;
        });
    }
    </script>
</body>

</html>
