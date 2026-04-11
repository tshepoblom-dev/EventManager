<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form_Response extends Model
{
    protected $fillable = [
        'form_id',
        'attendee_id',
        'session_token',
        'responses',
        'submitted_at',
    ];

    protected $casts = [
        'responses' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }

    public function attendee()
    {
        return $this->belongsTo(Attendee::class);
    }
}
