<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RespondentViewedCaseMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public object $case,
        public string $respondentName,
        public string $caseUrl,
        public ?string $timestamp = null
    ) {
        $this->timestamp = $timestamp ?? now()->toDateTimeString();
    }

    public function build()
    {
        $caseNumber = $this->case->case_number ?? '';
        return $this->subject("Respondent viewed case {$caseNumber}")
            ->view('mail.respondent_case_viewed');
    }
}
