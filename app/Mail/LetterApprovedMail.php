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
        $subjectLine = $this->letter->subject ?: 'Approved Letter';
        $subject = trim("{$subjectLine} — {$caseNumber}") ?: 'Approved Letter';
        $fileName = Str::of($caseNumber ?: 'case')
            ->slug('-')
            ->append('-letter-', $this->letter->id, '.pdf')
            ->value();

        $pdf = Pdf::loadView('pdf.letter-approved', [
            'letter'     => $this->letter,
            'case'       => $this->case,
            'caseNumber' => $caseNumber,
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
