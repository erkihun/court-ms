<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Queue\Middleware\SkipExpiredOtp;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class PasswordResetOtp extends Notification implements ShouldQueue
{
    use Queueable;

    private readonly CarbonImmutable $expiresAt;

    public function __construct(protected readonly string $code)
    {
        $this->expiresAt = CarbonImmutable::now()->addMinutes(10);
    }

    /**
     * Do not deliver a password-reset code after its matching session state has expired.
     *
     * @return array<int, SkipExpiredOtp>
     */
    public function middleware(mixed $notifiable, string $channel): array
    {
        return [new SkipExpiredOtp($this->expiresAt)];
    }

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
