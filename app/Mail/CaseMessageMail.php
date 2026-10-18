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
class CaseMessageMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [60, 300];

    public function __construct(
        public object $caseRow,
        public string $sender,     // "Applicant" or "Court Staff"
        public string $bodyPreview // short preview
    ) {
        $this->locale(app()->getLocale());
    }

    public function build()
    {
        return $this->subject(__('notifications.mail.case_message_subject', [
            'case' => $this->caseRow->case_number,
        ]))
            ->view('mail.case_message');
    }
}
