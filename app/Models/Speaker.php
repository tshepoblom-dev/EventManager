<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Speaker extends Model
{
    protected $fillable = [
        'event_id', 'user_id', 'attendee_id', 'name', 'email',
        'title', 'bio', 'photo', 'linkedin', 'twitter',
    ];

    // ── Relations ──────────────────────────────────────────────────────

    /** The Event this speaker belongs to. */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** The User account associated with this speaker (optional). */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The Attendee record associated with this speaker (optional). */
    public function attendee(): BelongsTo
    {
        return $this->belongsTo(Attendee::class);
    }

    /** Sessions this speaker has been assigned to. */
    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(Session::class, 'session_speakers', 'speaker_id', 'event_session_id')
                    ->withPivot('role', 'user_id')
                    ->withTimestamps();
    }

    // ── Helpers ────────────────────────────────────────────────────────

    /** Derived display label for dropdowns. */
    public function getDisplayNameAttribute(): string
    {
        return $this->title
            ? "{$this->name} — {$this->title}"
            : $this->name;
    }

    /** Sync this speaker's name/email from their linked attendee. */
    public function syncFromAttendee(): void
    {
        if ($this->attendee) {
            $this->update([
                'name'  => $this->attendee->full_name,
                'email' => $this->attendee->email,
            ]);
        }
    }

    /** Sync this speaker's name/email from their linked user account. */
    public function syncFromUser(): void
    {
        if ($this->user) {
            $this->update([
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ]);
        }
    }
}
