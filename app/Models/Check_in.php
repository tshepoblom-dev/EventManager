<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Check_in extends Model
{
     protected $fillable = [
        'event_id',
        'attendee_id',
        'checked_in_by',
        'checked_in_at',
        'method',
        'station',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function attendee()
    {
        return $this->belongsTo(Attendee::class);
    }

    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }
}
