<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Form_Response extends Model
{
    // Migration created 'form__responses' (double underscore matches PHP naming convention)
    protected $table = 'form__responses';

    protected $fillable = [
        'form_id',
        'attendee_id',
        'session_token',
        'responses',
        'submitted_at',
    ];

    protected $casts = [
        'responses'    => 'array',
        'submitted_at' => 'datetime',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function attendee(): BelongsTo
    {
        return $this->belongsTo(Attendee::class);
    }
}
