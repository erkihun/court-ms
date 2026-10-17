<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\ViewErrorBag;

/**
 * Turns the session's one-shot flash data into toast messages.
 *
 * Controllers flash a plain string, a translation key, or
 * ['key' => ..., 'replace' => [...]]; all three resolve to display text here
 * so the frontend only renders strings.
 */
final class FlashMessages
{
    private const TYPES = [
        'success' => 'success',
        'ok' => 'success',
        'error' => 'error',
        'warning' => 'warning',
        'info' => 'info',
    ];

    private const STATUS_KEYS = [
        'profile-updated' => 'messages.status.profile_updated',
        'password-updated' => 'messages.status.password_updated',
        'verification-link-sent' => 'messages.status.verification_link_sent',
    ];

    /**
     * @return list<array{type: 'success'|'error'|'warning'|'info', message: string, details: list<string>}>
     */
    public static function from(Session $session): array
    {
        $toasts = [];

        foreach (self::TYPES as $key => $type) {
            self::push($toasts, $type, $session->get($key));
        }

        if ($session->has('status')) {
            $status = $session->get('status');
            self::push($toasts, 'info', is_string($status) ? (self::STATUS_KEYS[$status] ?? $status) : $status);
        }

        $errors = $session->get('errors');
        if ($errors instanceof ViewErrorBag && $errors->any()) {
            $toasts[] = [
                'type' => 'error',
                'message' => __('messages.error.validation_summary'),
                'details' => array_values(array_map(self::resolve(...), $errors->all())),
            ];
        }

        return $toasts;
    }

    public static function resolve(mixed $message): string
    {
        if (is_array($message)) {
            $key = $message['key'] ?? null;
            $replace = is_array($message['replace'] ?? null) ? $message['replace'] : [];

            return is_string($key) ? (string) __($key, $replace) : '';
        }

        if (! is_scalar($message)) {
            return '';
        }

        $message = (string) $message;

        return Lang::has($message) ? (string) __($message) : $message;
    }

    /**
     * @param  list<array{type: string, message: string, details: list<string>}>  $toasts
     */
    private static function push(array &$toasts, string $type, mixed $message): void
    {
        if (blank($message)) {
            return;
        }

        $text = self::resolve($message);
        if ($text !== '') {
            $toasts[] = ['type' => $type, 'message' => $text, 'details' => []];
        }
    }
}
