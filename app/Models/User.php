<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'role_id', 'phone', 'company',
        'job_title', 'bio', 'networking_opt_in', 'profile_photo',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'networking_opt_in' => 'boolean',
    ];

    // ── Relations ──────────────────────────────────────────────────────

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /** All attendee records linked to this user account. */
    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    /** Speaker profile linked to this user (if any). */
    public function speaker(): HasOne
    {
        return $this->hasOne(Speaker::class);
    }

    // ── Role helpers ───────────────────────────────────────────────────

    public function hasRole(string $role): bool
    {
        return $this->role?->name === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role?->name, $roles);
    }

    public function isAdmin(): bool    { return $this->hasRole('admin'); }
    public function isStaff(): bool    { return $this->hasAnyRole(['admin', 'staff']); }
    public function isSponsor(): bool  { return $this->hasRole('sponsor'); }
    public function isSpeaker(): bool  { return $this->hasRole('speaker'); }
}
