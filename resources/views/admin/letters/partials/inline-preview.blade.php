@php
    $template = data_get($letter, 'template');
    if (!$template && class_exists(\App\Models\LetterTemplate::class)) {
        $templateId = data_get($letter, 'letter_template_id') ?? data_get($letter, 'template_id');
        if (!empty($templateId)) {
            $template = \App\Models\LetterTemplate::find($templateId);
        }
    }

    $systemSettings = isset($systemSettings) ? $systemSettings : \App\Models\SystemSetting::query()->first();

    $resolvePublicUrl = static function ($path) {
        if (empty($path)) {
            return null;
        }
        $trimmed = ltrim($path);
        if (\Illuminate\Support\Str::startsWith($trimmed, ['http://', 'https://', 'data:'])) {
            return $trimmed;
        }
        if (\Illuminate\Support\Str::startsWith($trimmed, ['/storage/', 'storage/'])) {
            return asset(ltrim($trimmed, '/'));
        }
        if (\Illuminate\Support\Str::startsWith($trimmed, ['public/'])) {
            $trimmed = substr($trimmed, 7);
        }

        return asset('storage/' . ltrim($trimmed, '/'));
    };

    $headerImage = $resolvePublicUrl(optional($template)->header_image_path ?? null);
    $footerImage = $resolvePublicUrl(optional($template)->footer_image_path ?? null);
    $sealPath = $resolvePublicUrl(optional($systemSettings)->seal_path);

    $isApproved = ($letter->approval_status === 'approved');
    $displaySeal = $isApproved && !empty($sealPath);

    $authorName = \Illuminate\Support\Str::of(data_get($letter, 'author.name', ''))->trim();
    $authorSignature = $resolvePublicUrl(data_get($letter, 'author.signature_url'));
    $authorPosition = data_get($letter, 'author.position') ?? data_get($letter, 'author.title');
    $displayName = $isApproved ? ($letter->approved_by_name ?: $authorName) : '';
    $displayTitle = $isApproved ? ($letter->approved_by_title ?: $authorPosition ?? '') : '';

    if ($isApproved && (empty($displayName) || empty($authorSignature) || empty($displayTitle))) {
        $authorId = data_get($letter, 'user_id') ?? data_get($letter, 'author_id');
        if ($authorId && class_exists(\App\Models\User::class)) {
            $authorModel = \App\Models\User::query()->find($authorId);
            if ($authorModel) {
                if (empty($displayName)) {
                    $displayName = $authorModel->name ?? $displayName;
                }
                if (empty($displayTitle)) {
                    $displayTitle = $authorModel->position ?? $authorModel->title ?? $displayTitle;
                }
                if (empty($authorSignature) && !empty($authorModel->signature_url)) {
                    $authorSignature = $resolvePublicUrl($authorModel->signature_url);
                }
            }
        }
    }

    $recipientCompanies = $letter->recipient_company
        ? array_filter(array_map('trim', explode(',', $letter->recipient_company)))
        : [];

    $ccRecipients = $letter->cc
        ? array_filter(array_map('trim', explode(',', $letter->cc)))
        : [];

    $safeBody = \Mews\Purifier\Facades\Purifier::clean($letter->body ?? '', 'default');

    if (class_exists(\App\Support\EthiopianDate::class)) {
        $letterDate = \App\Support\EthiopianDate::format($letter->created_at ?? now());
    } else {
        $letterDate = optional($letter->created_at)->toFormattedDateString() ?? now()->toFormattedDateString();
    }

    $qrPayload = implode('|', [
        'REF:' . \Illuminate\Support\Str::limit($letter->reference_number ?? 'NA', 20, ''),
        'CASE:' . \Illuminate\Support\Str::limit($letter->case_number ?? 'NA', 20, ''),
        'STATUS:' . ($letter->approval_status ?? 'pending'),
        'DATE:' . $letterDate,
    ]);

    $qrSvg = null;
    try {
        if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
            $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->encoding('UTF-8')
                ->size(120)
                ->errorCorrection('L')
                ->generate($qrPayload);
        } elseif (class_exists(\BaconQrCode\Writer::class)) {
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(120),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );
            $writer = new \BaconQrCode\Writer($renderer);
            $qrSvg = $writer->writeString($qrPayload);
        }
    } catch (\Throwable $qrEx) {
        \Illuminate\Support\Facades\Log::debug('Letter QR generation failed for record preview', [
            'letter_id' => $letter->id ?? null,
            'error' => $qrEx->getMessage(),
        ]);
        $qrSvg = null;
    }

    $previewId = 'letter-preview-' . $letter->id;
@endphp

<div class="letter-preview-container letter-live-preview" data-letter-preview-id="{{ $previewId }}">

        <div id="preview-container-{{ $previewId }}" class="preview-wrapper"></div>

        <div id="raw-content-{{ $previewId }}" style="display:none;">
            <div class="content-block" data-role="before-body">
                <div class="meta-row content-meta">
                    <div>
                        <strong>{{ __('letters.preview.ref_no') }}</strong>
                        {{ $letter->reference_number ?? __('letters.cards.missing') }}
                    </div>
                    <div>
                        <strong>{{ __('letters.preview.date') }}</strong> {{ $letterDate }}
                    </div>
                </div>
            </div>

            <div class="content-block" data-role="before-body">
                <div class="recipient-block">
                    @foreach($recipientCompanies as $company)
                        <div class="pl-4"><span class="mr-3 font-bold">ሰ</span> {{ $company }}</div>
                    @endforeach
                </div>
                <strong style="text-decoration: underline;">{{ $letter->recipient_name }}</strong>
            </div>

            <div class="content-block" data-role="before-body">
                <div class="subject-line">
                    {{ __('letters.preview.subject') }}
                    {{ $letter->subject ?: ($template->title ?? __('letters.cards.untitled')) }}
                </div>
            </div>

            <div id="raw-body-content-{{ $previewId }}">
                {!! $safeBody !!}
            </div>

            <div class="content-block" data-role="after-body">
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
                        <p style="margin-top: 0.5rem;">
                            <strong>{{ $displayName }}</strong><br>
                            {{ $displayTitle }}
                        </p>
                    @endif
                </div>
            </div>

            @if(count($ccRecipients) > 0)
                <div class="content-block" data-role="after-body">
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

        <template id="sheet-template-{{ $previewId }}">
            <div class="a4-sheet">
                <div class="letter-header">
                    @if($headerImage)
                        <img src="{{ $headerImage }}" crossorigin="anonymous" alt="Header">
                    @else
                        <div style="height: 20mm;"></div>
                    @endif
                </div>

                <div class="letter-body-container">
                    <div class="content-slot"></div>
                </div>

                @if($displaySeal)
                    <div class="page-seal-bottom">
                        <img src="{{ $sealPath }}" crossorigin="anonymous" alt="Official Seal">
                    </div>
                @endif

                <div class="letter-footer">
                    @if($footerImage)
                        <img src="{{ $footerImage }}" crossorigin="anonymous" alt="Footer">
                    @endif
                    @if($qrSvg)
                        <div class="qr-block">{!! $qrSvg !!}</div>
                    @endif
                </div>
            </div>
        </template>
  
</div>
