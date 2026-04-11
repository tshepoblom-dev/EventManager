<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    protected $fillable = [
        'event_id',
        'attendee_id',
        'registration_number',
        'registered_at',
        'payment_status',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function attendee()
    {
        return $this->belongsTo(Attendee::class);
    }
}
