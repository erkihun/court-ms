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
class RespondentViewedCaseMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [60, 300];

    public function __construct(
        public object $case,
        public string $respondentName,
        public string $caseUrl,
        public ?string $timestamp = null
    ) {
        $this->locale(app()->getLocale());
        $this->timestamp = $timestamp ?? now()->toDateTimeString();
    }

    public function build()
    {
        $caseNumber = $this->case->case_number ?? '';
        return $this->subject(__('notifications.mail.respondent_viewed_case_subject', [
            'case' => $caseNumber,
        ]))
            ->view('mail.respondent_case_viewed');
    }
}
