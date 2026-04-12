<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


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

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->role?->name === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role?->name, $roles);
    }

    public function sessions()
    {
        // Table is 'session_speakers' (plural) — matches the migration definition
        return $this->belongsToMany(Session::class, 'session_speakers', 'user_id', 'event_session_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function isAdmin(): bool    { return $this->hasRole('admin'); }
    public function isStaff(): bool    { return $this->hasAnyRole(['admin', 'staff']); }
    public function isSponsor(): bool  { return $this->hasRole('sponsor'); }
    public function isSpeaker(): bool  { return $this->hasRole('speaker'); }
}
