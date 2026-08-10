<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Mail\SmtpDiagnosticMail;
use Illuminate\Contracts\Mail\Factory;

final readonly class SendSmtpTestEmail
{
    public function __construct(private Factory $mail) {}

    public function execute(string $recipient): void
    {
        $message = (new SmtpDiagnosticMail(now()->toDateTimeString()))->to($recipient);

        $this->mail->mailer()->send($message);
    }
}
