<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A system that calls the /api/v1/integration routes (today: EUISIS).
 * Its token is stored as a SHA-256 hash only and is never a Sanctum token,
 * so the person-facing API (/api/v1/cases …) can never accept it.
 */
class IntegrationClient extends Model
{
    public const ABILITY_REFERRALS_CREATE = 'referrals:create';

    public const ABILITY_REFERRALS_READ = 'referrals:read';

    protected $guarded = [];

    protected $hidden = ['token_hash'];

    protected $casts = [
        'abilities' => 'array',
        'allowed_ips' => 'array',
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /** A new random token for this client; returns the plain text, shown once. */
    public function issueToken(): string
    {
        $plain = 'cms_'.Str::random(48);
        $this->forceFill(['token_hash' => hash('sha256', $plain), 'revoked_at' => null])->save();

        return $plain;
    }

    public static function findByToken(string $plain): ?self
    {
        return self::query()->where('token_hash', hash('sha256', $plain))->whereNull('revoked_at')->first();
    }

    public function hasAbility(string $ability): bool
    {
        $abilities = $this->abilities ?? [];

        return in_array('*', $abilities, true) || in_array($ability, $abilities, true);
    }

    public function allowsIp(?string $ip): bool
    {
        $allowed = array_values(array_filter($this->allowed_ips ?? []));

        return $allowed === [] || ($ip !== null && in_array($ip, $allowed, true));
    }
}
