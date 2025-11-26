<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CaseStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public object $caseRow,   // stdClass from DB
        public string $oldStatus,
        public string $newStatus,
        public ?string $note = null,
    ) {}

    public function build()
    {
        return $this->subject("Your case {$this->caseRow->case_number} status: " . ucfirst($this->newStatus))
            ->view('mail.case_status_changed');
    }
}
