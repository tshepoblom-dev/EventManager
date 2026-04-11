<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'event_id',
        'sponsor_id',
        'attendee_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'business_type',
        'interest_level',
        'pipeline_stage',
        'notes',
        'source',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }

    public function attendee()
    {
        return $this->belongsTo(Attendee::class);
    }
}
