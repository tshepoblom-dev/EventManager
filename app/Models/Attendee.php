<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Attendee extends Model
{
    protected $fillable = [
        'event_id', 'user_id', 'first_name', 'last_name',
        'email', 'phone', 'company', 'job_title',
        'ticket_type', 'status', 'qr_code',
        'qr_image_path', 'qr_emailed', 'source',
        'invite_token', 'invite_sent_at',
    ];

    protected $casts = [
        'qr_emailed'     => 'boolean',
        'invite_sent_at' => 'datetime',
    ];

    protected $appends = ['full_name'];

    // ── Relations ──────────────────────────────────────────────────────

    public function event(): BelongsTo    { return $this->belongsTo(Event::class); }
    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
    public function checkIn(): HasOne     { return $this->hasOne(Check_in::class); }
    public function feedback(): HasMany   { return $this->hasMany(Feedback::class); }
    public function leads(): HasMany      { return $this->hasMany(Lead::class); }
    public function registration(): HasOne { return $this->hasOne(Registration::class); }
    public function speaker(): HasOne     { return $this->hasOne(Speaker::class); }

    // ── Accessors ──────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    // ── Helpers ────────────────────────────────────────────────────────

    public function isCheckedIn(): bool
    {
        if ($this->relationLoaded('checkIn')) {
            return $this->checkIn !== null;
        }
        return $this->checkIn()->exists();
    }

    public function hasAccount(): bool
    {
        return $this->user_id !== null;
    }

    public function hasPendingInvite(): bool
    {
        return $this->invite_token !== null && $this->user_id === null;
    }

    public function generateInviteToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->update([
            'invite_token'   => $token,
            'invite_sent_at' => now(),
        ]);
        return $token;
    }

    public function clearInviteToken(): void
    {
        $this->update(['invite_token' => null]);
    }
}
