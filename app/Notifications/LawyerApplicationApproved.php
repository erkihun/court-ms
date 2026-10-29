<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The registry verified the licence of a lawyer who applied at /lawyer/register:
 * the email carries the link to choose a password (the `applicants` broker),
 * then the lawyer signs in at /lawyer/login.
 */
class LawyerApplicationApproved extends Notification
{
    public function __construct(private readonly string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('lawyer.apply.approved_mail.subject'))
            ->line(__('lawyer.apply.approved_mail.intro'))
            ->action(__('lawyer.mail.action'), route('lawyer.password.reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()]))
            ->line(__('lawyer.apply.approved_mail.outro'));
    }
}
