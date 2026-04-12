<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    protected $fillable = [
        'event_id',
        'title',
        'description',
        'type',
        'is_active',
        'allow_anonymous',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'allow_anonymous' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function fields(): HasMany
    {
        // Relationship to Form_field model
        return $this->hasMany(Form_field::class);
    }

    public function responses(): HasMany
    {
        // Class name is Form_Response (capital R) — must match exactly on case-sensitive filesystems
        return $this->hasMany(Form_Response::class);
    }
}
