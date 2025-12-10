@php
$bodyHtml = $letter->body ?? '';
$displayName = $recipientName ?: 'Recipient';
@endphp

<div style="font-family: Arial, sans-serif; color: #0f172a; padding: 16px; background: #f8fafc;">
    <div style="max-width: 720px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid #e5e7eb; background: linear-gradient(90deg, #f97316 0%, #fb923c 100%); color: #fff;">
            <div style="font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.9;">Approved Letter</div>
            <div style="font-size: 20px; font-weight: 700; margin-top: 4px;">{{ $caseNumber ?: 'Case' }}</div>
        </div>

        <div style="padding: 24px;">
            <p style="margin: 0 0 12px;">Hello {{ $displayName }},</p>
            <p style="margin: 0 0 16px; color: #475569;">
                A letter tied to case <strong>{{ $caseNumber ?: 'N/A' }}</strong> has been approved. The content is included below for your records.
            </p>

            <div style="margin: 20px 0; padding: 16px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc;">
                <div style="font-size: 14px; font-weight: 700; color: #0f172a;">Subject: {{ $letter->subject ?? 'Letter' }}</div>
                @if($letter->reference_number)
                <div style="margin-top: 4px; font-size: 12px; color: #475569;">Reference: {{ $letter->reference_number }}</div>
                @endif
                @if($case?->title)
                <div style="margin-top: 4px; font-size: 12px; color: #475569;">Case Title: {{ $case->title }}</div>
                @endif
                @if($letter->cc)
                <div style="margin-top: 4px; font-size: 12px; color: #475569;">CC: {{ $letter->cc }}</div>
                @endif
            </div>

            <div style="padding: 16px; border: 1px solid #e2e8f0; border-radius: 10px;">
                {!! $bodyHtml !!}
            </div>

            <p style="margin: 16px 0 0; color: #475569; font-size: 13px;">
                If you have any questions, please reply to this email referencing case {{ $caseNumber ?: 'N/A' }}.
            </p>
        </div>

        <div style="padding: 16px 24px; background: #f1f5f9; border-top: 1px solid #e5e7eb; color: #475569; font-size: 12px;">
            This is an automated message from the court management system.
        </div>
    </div>
</div>
