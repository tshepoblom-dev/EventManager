<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feedback extends Model
{
     protected $table = 'feedback';

    protected $fillable = [
        'event_id',
        'session_id',
        'attendee_id',
        'rating',
        'comment',
        'type',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function attendee()
    {
        return $this->belongsTo(Attendee::class);
    }
}
