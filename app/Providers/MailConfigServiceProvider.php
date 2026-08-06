<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Throwable;

/**
 * Applies the admin-configured SMTP settings (System Settings → Notifications)
 * over the mail config at boot.
 *
 * The database is only consulted as an override: unless `mail_enabled` is on
 * AND a host is actually stored, the `.env` values remain authoritative. That
 * keeps a blank or half-filled settings row from silently breaking all outgoing
 * mail, and leaves `.env` as a working fallback during an outage.
 *
 * Individual fields fall back to their `.env` counterpart when left empty, so a
 * partially filled form only overrides what it actually specifies.
 */
class MailConfigServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Console commands like migrate/config:cache may run before the
        // settings table exists (or without a usable DB connection), so a
        // failure here must never prevent the app from booting.
        try {
            $settings = SystemSetting::cached();
        } catch (Throwable) {
            return;
        }

        if (! $settings?->mail_enabled || blank($settings->mail_host)) {
            return;
        }

        $smtp = 'mail.mailers.smtp.';

        Config::set([
            'mail.default' => $settings->mail_mailer ?: config('mail.default'),
            $smtp.'host' => $settings->mail_host,
            $smtp.'port' => $settings->mail_port ?: config($smtp.'port'),
            $smtp.'username' => $settings->mail_username ?: config($smtp.'username'),
            $smtp.'password' => $settings->mail_password ?: config($smtp.'password'),
            $smtp.'scheme' => $this->scheme($settings->mail_encryption) ?? config($smtp.'scheme'),
            'mail.from.address' => $settings->mail_from_address ?: config('mail.from.address'),
            'mail.from.name' => $settings->mail_from_name ?: config('mail.from.name'),
        ]);
    }

    /**
     * Map the stored encryption choice onto a Symfony mailer scheme.
     *
     * Laravel 12 drives the transport from `scheme`, not the legacy
     * `encryption` key: "smtps" is implicit TLS (typically port 465) while
     * "smtp" is a plaintext connection upgraded via STARTTLS (port 25/587).
     */
    protected function scheme(?string $encryption): ?string
    {
        return match (strtolower((string) $encryption)) {
            'ssl' => 'smtps',
            'tls', 'starttls' => 'smtp',
            'none' => 'smtp',
            default => null,
        };
    }
}
