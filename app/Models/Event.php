<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Event extends Model
{
     protected $fillable = [
        'name', 'description', 'venue', 'event_date',
        'start_time', 'end_time', 'status', 'logo', 'primary_color',
    ];

    protected $casts = ['event_date' => 'date'];

    public function attendees(): HasMany    { return $this->hasMany(Attendee::class); }
    public function sessions(): HasMany     { return $this->hasMany(Session::class)->orderBy('starts_at'); }
    public function sponsors(): HasMany     { return $this->hasMany(Sponsor::class); }
    public function checkIns(): HasMany     { return $this->hasMany(Check_in::class); }
    public function leads(): HasMany        { return $this->hasMany(Lead::class); }
    public function forms(): HasMany        { return $this->hasMany(Form::class); }
    public function feedback(): HasMany     { return $this->hasMany(Feedback::class); }

    public function checkedInCount(): int
    {
        return $this->checkIns()->count();
    }

    public function currentSession(): ?Session
    {
        $now = now();
        return $this->sessions()
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->first();
    }
}
