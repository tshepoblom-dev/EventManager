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

    public function event(): BelongsTo    { return $this->belongsTo(Event::class); }
    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
    public function checkIn(): HasOne     { return $this->hasOne(Check_in::class); }
    public function feedback(): HasMany   { return $this->hasMany(Feedback::class); }
    public function leads(): HasMany      { return $this->hasMany(Lead::class); }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function isCheckedIn(): bool
    {
        return $this->checkIn()->exists();
    }
}
