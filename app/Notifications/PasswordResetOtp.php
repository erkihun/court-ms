<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetOtp extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected string $code) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $name = $notifiable->full_name ?? $notifiable->name ?? 'User';

        return (new MailMessage)
            ->subject('Password Reset Code')
            ->greeting('Hello '.$name.',')
            ->line('You requested to reset your password. Use the code below:')
            ->line('# '.$this->code)
            ->line('This code expires in **10 minutes**.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}
