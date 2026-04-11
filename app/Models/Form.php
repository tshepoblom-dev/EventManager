<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Event;
use App\Models\FormField;
use App\Models\FormResponse;

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
        'is_active' => 'boolean',
        'allow_anonymous' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function fields()
    {
        return $this->hasMany(Form_field::class);
    }

    public function responses()
    {
        return $this->hasMany(Form_response::class);
    }
}
