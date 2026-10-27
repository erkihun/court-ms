<?php

declare(strict_types=1);

namespace App\Services\Security;

use App\Models\User;
use App\Support\UserAgent;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * An admin's live sessions for the profile's Devices tab (the EUISIS shape;
 * EUISIS App\Services\Security\UserSessionRegistry). Only the database
 * session driver keeps a per-user index; with any other the tab says so.
 */
final class UserSessionRegistry
{
    public function supported(): bool
    {
        return config('session.driver') === 'database';
    }

    /** @return list<array{id: string, ip_address: ?string, browser: ?string, os: ?string, device: string, last_active_at: string, current: bool}> */
    public function forUser(User $user, string $currentSessionId): array
    {
        if (! $this->supported()) {
            return [];
        }

        return $this->liveRows($user)
            ->orderByDesc('last_activity')
            ->get(['id', 'ip_address', 'user_agent', 'last_activity'])
            ->map(fn (object $row): array => [
                'id' => (string) $row->id,
                'ip_address' => $row->ip_address !== null ? (string) $row->ip_address : null,
                ...UserAgent::summarize($row->user_agent !== null ? (string) $row->user_agent : null),
                'last_active_at' => CarbonImmutable::createFromTimestamp((int) $row->last_activity)->toIso8601String(),
                'current' => hash_equals($currentSessionId, (string) $row->id),
            ])
            ->values()
            ->all();
    }

    public function revoke(User $user, string $sessionId, string $currentSessionId): bool
    {
        if (! $this->supported() || hash_equals($currentSessionId, $sessionId)) {
            return false;
        }

        return $this->table()->where('user_id', (string) $user->getKey())->where('id', $sessionId)->delete() > 0;
    }

    public function revokeOthers(User $user, string $currentSessionId): int
    {
        if (! $this->supported()) {
            return 0;
        }

        return $this->table()->where('user_id', (string) $user->getKey())->where('id', '!=', $currentSessionId)->delete();
    }

    private function liveRows(User $user): Builder
    {
        $lifetimeMinutes = max(1, (int) config('session.lifetime', 120));

        return $this->table()
            ->where('user_id', (string) $user->getKey())
            ->where('last_activity', '>=', now()->subMinutes($lifetimeMinutes)->getTimestamp());
    }

    private function table(): Builder
    {
        return DB::connection(config('session.connection'))->table((string) config('session.table', 'sessions'));
    }
}
