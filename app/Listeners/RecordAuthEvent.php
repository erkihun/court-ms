<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;

/**
 * Writes authentication events (login, logout, failed attempt, lockout,
 * password reset) to the system_audits table. SystemAuditMiddleware only
 * covers authenticated state-changing requests, so auth events need their
 * own trail for security review.
 */
class RecordAuthEvent
{
    public function handleLogin(Login $event): void
    {
        $this->record('auth.login', $event->guard, $event->user->getAuthIdentifier());
    }

    public function handleLogout(Logout $event): void
    {
        $this->record('auth.logout', $event->guard, $event->user?->getAuthIdentifier());
    }

    public function handleFailed(Failed $event): void
    {
        $this->record('auth.failed', $event->guard, $event->user?->getAuthIdentifier(), [
            'email' => (string) ($event->credentials['email'] ?? ''),
        ]);
    }

    public function handleLockout(Lockout $event): void
    {
        $this->record('auth.lockout', null, null, [
            'email' => (string) $event->request->input('email', ''),
        ]);
    }

    public function handlePasswordReset(PasswordReset $event): void
    {
        $this->record('auth.password_reset', null, $event->user->getAuthIdentifier());
    }

    private function record(string $action, ?string $guard, int|string|null $userId, array $context = []): void
    {
        $request = request();

        try {
            DB::table('system_audits')->insert([
                'user_id' => is_numeric($userId) ? (int) $userId : null,
                'actor_type' => $guard ?: 'guest',
                'action' => $action,
                'module' => 'auth',
                'route' => optional($request->route())->getName(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 2000),
                'context' => json_encode(array_merge(['path' => $request->path()], $context)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable) {
            // Never let audit logging break authentication itself.
        }
    }
}
