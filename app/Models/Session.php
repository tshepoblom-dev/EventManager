<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Session extends Model
{
    // Avoids collision with Laravel's built-in 'sessions' table
    protected $table = 'event_sessions';

    protected $fillable = [
        'event_id', 'title', 'description', 'room',
        'starts_at', 'ends_at', 'type', 'capacity',
        'is_highlighted', 'sort_order',
    ];

    protected $casts = [
        'starts_at'      => 'datetime',
        'ends_at'        => 'datetime',
        'is_highlighted' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function feedback(): HasMany
    {
        // FK is 'event_session_id' per migration
        return $this->hasMany(Feedback::class, 'event_session_id');
    }

    public function speakers(): BelongsToMany
    {
        // Migration created 'session_speakers' table with 'event_session_id' FK
        return $this->belongsToMany(User::class, 'session_speakers', 'event_session_id', 'user_id')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function isLive(): bool
    {
        return now()->between($this->starts_at, $this->ends_at);
    }

    public function averageRating(): float
    {
        return $this->feedback()->whereNotNull('rating')->avg('rating') ?? 0.0;
    }
}
