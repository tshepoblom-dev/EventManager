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
    ];

    // Fix #19: qr_emailed must be cast to boolean — without this, comparisons
    // behave inconsistently across DB drivers (e.g. "0" !== false in SQLite).
    protected $casts = [
        'qr_emailed' => 'boolean',
    ];

    // Fix #20: $appends ensures full_name appears in JSON responses from the
    // check-in API and networking endpoints without extra ->append() calls.
    protected $appends = ['full_name'];

    public function event(): BelongsTo    { return $this->belongsTo(Event::class); }
    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
    public function checkIn(): HasOne     { return $this->hasOne(Check_in::class); }
    public function feedback(): HasMany   { return $this->hasMany(Feedback::class); }
    public function leads(): HasMany      { return $this->hasMany(Lead::class); }
    public function registration(): HasOne { return $this->hasOne(Registration::class); }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Fix #25: Check relationLoaded() first to avoid an extra query when the
     * checkIn relation has already been eager-loaded (e.g. in searchCheckIn).
     */
    public function isCheckedIn(): bool
    {
        if ($this->relationLoaded('checkIn')) {
            return $this->checkIn !== null;
        }

        return $this->checkIn()->exists();
    }
}
