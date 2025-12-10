<?php

namespace App\Mail;

use App\Models\CourtCase;
use App\Models\Letter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class LetterApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Letter $letter,
        public ?CourtCase $case = null,
        public ?string $recipientName = null
    ) {
    }

    public function build(): static
    {
        $caseNumber = $this->letter->case_number ?? $this->case?->case_number ?? '';
        $subject = trim('Approved Letter - ' . ($caseNumber ?: ($this->letter->subject ?? '')));
        $fileName = Str::of($caseNumber ?: 'case')
            ->slug('-')
            ->append('-letter-', $this->letter->id, '.pdf')
            ->value();

        // PDF attachment using the existing admin preview template
        $pdf = Pdf::loadView('admin.letters.preview', [
            'letter' => $this->letter,
            'template' => $this->letter->template,
        ])->setPaper('a4');

        return $this->subject($subject)
            ->view('mail.letter-approved')
            ->with([
                'letter'        => $this->letter,
                'case'          => $this->case,
                'caseNumber'    => $caseNumber,
                'recipientName' => $this->recipientName,
            ])
            ->attachData($pdf->output(), $fileName, [
                'mime' => 'application/pdf',
            ]);
    }
}
