<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
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

    public function event(): BelongsTo    { return $this->belongsTo(Event::class); }
    public function feedback(): HasMany   { return $this->hasMany(Feedback::class); }
    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'session_speaker')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function isLive(): bool
    {
        return now()->between($this->starts_at, $this->ends_at);
    }

    public function averageRating(): float
    {
        return $this->feedback()->whereNotNull('rating')->avg('rating') ?? 0;
    }
}
