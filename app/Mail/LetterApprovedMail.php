<?php

namespace App\Mail;

use App\Models\CourtCase;
use App\Models\Letter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

/**
 * Queued: sent by the queue worker so the request that triggers it never waits on SMTP.
 * The locale is captured here because the worker renders in its own default language.
 */
class LetterApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [60, 300];

    public function __construct(
        public Letter $letter,
        public ?CourtCase $case = null,
        public ?string $recipientName = null
    ) {
        $this->locale(app()->getLocale());
    }

    public function build(): static
    {
        $caseNumber = $this->letter->case_number ?? $this->case?->case_number ?? '';
        $subject = trim('Approved Letter - ' . ($caseNumber ?: ($this->letter->subject ?? '')));

        $previewUrl = Route::has('letters.case-preview')
            ? URL::signedRoute('letters.case-preview', ['letter' => $this->letter->getKey()])
            : URL::to('/case-letters/' . $this->letter->getKey());

        return $this->subject($subject)
            ->view('mail.letter-approved')
            ->with([
                'letter'        => $this->letter,
                'case'          => $this->case,
                'caseNumber'    => $caseNumber,
                'recipientName' => $this->recipientName,
                'previewUrl'    => $previewUrl,
            ]);
    }
}
