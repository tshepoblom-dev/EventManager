<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sponsor extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'company_name',
        'tier',
        'logo',
        'website',
        'booth_number',
        'description',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }
}
