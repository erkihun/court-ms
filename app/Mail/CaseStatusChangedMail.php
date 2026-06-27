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
        $status = __("cases.status.{$this->newStatus}");
        if ($status === "cases.status.{$this->newStatus}") {
            $status = ucfirst($this->newStatus);
        }

        return $this->subject(__('notifications.mail.case_status_subject', [
            'case' => $this->caseRow->case_number,
            'status' => $status,
        ]))
            ->view('mail.case_status_changed');
    }
}
