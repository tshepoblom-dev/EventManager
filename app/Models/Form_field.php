<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form_field extends Model
{
    protected $fillable = [
        'form_id',
        'label',
        'type',
        'options',
        'required',
        'sort_order',
        'placeholder',
    ];

    protected $casts = [
        'options' => 'array',
        'required' => 'boolean',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
