<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Connection extends Model
{
     protected $fillable = [
        'event_id',
        'requester_id',
        'receiver_id',
        'status',
        'connected_at',
    ];

    protected $casts = [
        'connected_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function requester()
    {
        return $this->belongsTo(Attendee::class, 'requester_id');
    }

    public function receiver()
    {
        return $this->belongsTo(Attendee::class, 'receiver_id');
    }
}
