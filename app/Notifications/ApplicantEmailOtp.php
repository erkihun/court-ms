<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Queue\Middleware\SkipExpiredOtp;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class ApplicantEmailOtp extends Notification implements ShouldQueue
{
    use Queueable;

    private readonly CarbonImmutable $expiresAt;

    public function __construct(protected readonly string $code)
    {
        $this->expiresAt = CarbonImmutable::now()->addMinutes(10);
    }

    /**
     * Do not deliver a verification code after its matching session state has expired.
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
        return (new MailMessage)
            ->subject('Your Email Verification Code')
            ->greeting('Hello '.($notifiable->full_name ?? 'Applicant').',')
            ->line('Use the 6-digit code below to verify your email address:')
            ->line('# '.$this->code)
            ->line('This code expires in **10 minutes**.')
            ->line('If you did not create an account, no further action is required.');
    }
}
