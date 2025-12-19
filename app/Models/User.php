<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Auth\MustVerifyEmail;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmailContract, AuditableContract
{
    use HasApiTokens, HasFactory, Notifiable, MustVerifyEmail, Auditable;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'must_change_password',
        'status',          // 'active' | 'inactive'
        'gender',
        'date_of_birth',
        'national_id',
        'position',
        'phone',
        'address',
        'avatar_path',
        'signature_path',
        'stamp_path',
    ];

    /**
     * Hidden attributes for arrays/JSON.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Attribute casting.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'must_change_password' => 'boolean',
            'date_of_birth'     => 'date',
        ];
    }

    /**
     * Relationships
     */
    public function roles(): BelongsToMany
    {
        // pivot: role_user (role_id, user_id)
        return $this->belongsToMany(\App\Models\Role::class, 'role_user')->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        // permissions via roles (pivot: permission_role)
        // keys: permission_role (permission_id, role_id)
        return $this->belongsToMany(
            \App\Models\Permission::class,
            'permission_role',
            'role_id',
            'permission_id'
        )->withPivot('role_id');
    }

    /**
     * Permission / Role helpers
     */
    public function hasPermission(string $perm): bool
    {
        return $this->roles()
            ->whereHas('permissions', fn($q) => $q->where('name', $perm))
            ->exists();
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->hasRole('admin');
    }

    /** Convenience: allow admin to pass any perm */
    public function canDo(string $permission): bool
    {
        return $this->is_admin || $this->hasPermission($permission);
    }

    /**
     * Accessors for public URLs
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar_path ? Storage::url($this->avatar_path) : null;
    }

    public function getSignatureUrlAttribute(): ?string
    {
        return $this->signature_path ? Storage::url($this->signature_path) : null;
    }

    public function getStampUrlAttribute(): ?string
    {
        return $this->stamp_path ? Storage::url($this->stamp_path) : null;
    }

    /**
     * Query scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')->withTimestamps();
    }
}
