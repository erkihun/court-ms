<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\SystemSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Report why OTP mail delivery is failing on a given environment.
 *
 * The OTP controllers catch send failures and show a generic
 * "could not send a reset code" message, so the underlying SMTP error is only
 * visible in the log. This surfaces it directly, plus the configuration
 * mistakes that most often cause it.
 */
class MailDiagnoseCommand extends Command
{
    protected $signature = 'mail:diagnose {--to= : Send a real test message to this address}';

    protected $description = 'Check SMTP configuration and report the exact delivery error';

    public function handle(): int
    {
        $mailer = (string) config('mail.default');
        $smtp = config('mail.mailers.smtp', []);

        $host = (string) ($smtp['host'] ?? '');
        $port = (int) ($smtp['port'] ?? 0);
        $scheme = $smtp['scheme'] ?? null;
        $username = (string) ($smtp['username'] ?? '');
        $password = (string) ($smtp['password'] ?? '');
        $from = (string) config('mail.from.address');

        $this->reportConfigSource();

        $this->line('');
        $this->line('<comment>Effective mail configuration</comment>');
        $this->table(['Setting', 'Value'], [
            ['MAIL_MAILER', $mailer],
            ['host', $host !== '' ? $host : '<empty>'],
            ['port', $port !== 0 ? (string) $port : '<empty>'],
            ['scheme', $scheme ?? '<null>'],
            ['username', $username !== '' ? $this->mask($username) : '<EMPTY>'],
            ['password', $password !== '' ? 'set ('.strlen($password).' chars)' : '<EMPTY>'],
            ['timeout', (string) ($smtp['timeout'] ?? '<null>')],
            ['from.address', $from !== '' ? $from : '<EMPTY>'],
        ]);

        $problems = $this->configProblems($mailer, $host, $port, $scheme, $username, $password, $from);

        if ($problems !== []) {
            $this->line('');
            $this->line('<comment>Problems found</comment>');

            foreach ($problems as $problem) {
                $this->line('  <fg=red>✗</> '.$problem);
            }
        }

        $to = $this->option('to');

        if (! $to) {
            $this->line('');
            $this->info('Pass --to=you@example.com to attempt a real send.');

            return $problems === [] ? self::SUCCESS : self::FAILURE;
        }

        $this->line('');
        $this->line("Sending test message to {$to} ...");

        try {
            Mail::raw('OTP delivery diagnostic test.', function ($message) use ($to) {
                $message->to($to)->subject('Mail diagnostic');
            });
        } catch (\Throwable $e) {
            $this->line('');
            $this->line('  <fg=red>✗ send failed</>');
            $this->line('  <fg=red>'.get_class($e).'</>');
            $this->line('  '.$e->getMessage());
            $this->line('');
            $this->line('<comment>This is the exact error the OTP flow is hitting.</comment>');

            return self::FAILURE;
        }

        $this->line('  <fg=green>✓ sent successfully</>');

        return self::SUCCESS;
    }

    /**
     * Say whether .env or the System Settings row is supplying SMTP config.
     *
     * MailConfigServiceProvider overrides the .env values at boot whenever
     * mail_enabled is on and a host is stored, so editing .env has no effect
     * in that case — a common source of confusion when mail stops working.
     */
    private function reportConfigSource(): void
    {
        $this->line('');
        $this->line('<comment>Configuration source</comment>');

        try {
            $settings = SystemSetting::cached();
        } catch (\Throwable $e) {
            $this->line('  <fg=yellow>Could not read system_settings: '.$e->getMessage().'</>');

            return;
        }

        if ($settings === null) {
            $this->line('  .env (no system_settings row)');

            return;
        }

        $enabled = (bool) ($settings->mail_enabled ?? false);
        $host = (string) ($settings->mail_host ?? '');

        if ($enabled && $host !== '') {
            $this->line('  <fg=yellow>DATABASE</> — System Settings → Notifications is overriding .env.');
            $this->line('  Stored host: '.$host.', port: '.($settings->mail_port ?: '<from .env>'));
            $this->line('  <fg=yellow>Editing .env will NOT change delivery while this is on.</>');

            return;
        }

        $this->line('  .env (System Settings override is off'.($enabled && $host === '' ? ': enabled but no host stored' : '').')');
    }

    /** @return list<string> */
    private function configProblems(
        string $mailer,
        string $host,
        int $port,
        ?string $scheme,
        string $username,
        string $password,
        string $from,
    ): array {
        $problems = [];

        if ($mailer === 'log' || $mailer === 'array') {
            $problems[] = "MAIL_MAILER is \"{$mailer}\" — mail is not actually delivered.";
        }

        if ($username === '') {
            $problems[] = 'MAIL_USERNAME is empty — SMTP auth will fail.';
        }

        if ($password === '') {
            $problems[] = 'MAIL_PASSWORD is empty — SMTP auth will fail.';
        }

        if ($from === '') {
            $problems[] = 'MAIL_FROM_ADDRESS is empty — messages are rejected without a sender.';
        }

        // Implicit TLS (465) and STARTTLS (587) are not interchangeable; the
        // wrong pairing hangs until MAIL_TIMEOUT and then throws.
        if ($port === 465 && $scheme !== 'smtps') {
            $problems[] = 'Port 465 needs MAIL_SCHEME=smtps (implicit TLS). Current scheme: '.($scheme ?? 'null').'.';
        }

        if ($port === 587 && $scheme === 'smtps') {
            $problems[] = 'Port 587 needs MAIL_SCHEME=smtp (STARTTLS), not smtps.';
        }

        if (str_contains($host, 'gmail') && $password !== '' && strlen(str_replace(' ', '', $password)) !== 16) {
            $problems[] = 'Gmail requires a 16-character App Password, not the account password.';
        }

        return $problems;
    }

    private function mask(string $value): string
    {
        if (! str_contains($value, '@')) {
            return substr($value, 0, 2).'***';
        }

        [$local, $domain] = explode('@', $value, 2);

        return substr($local, 0, 2).'***@'.$domain;
    }
}
