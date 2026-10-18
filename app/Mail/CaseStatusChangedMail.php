<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Queued: sent by the queue worker so the request that triggers it never waits on SMTP.
 * The locale is captured here because the worker renders in its own default language.
 */
class CaseStatusChangedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [60, 300];

    public function __construct(
        public object $caseRow,   // stdClass from DB
        public string $oldStatus,
        public string $newStatus,
        public ?string $note = null,
    ) {
        $this->locale(app()->getLocale());
    }

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
