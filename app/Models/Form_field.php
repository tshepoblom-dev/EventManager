<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form_field extends Model
{
    // Laravel derives 'form__fields' from class name Form_field.
    // The migration created 'form_fields', so we must be explicit.
    protected $table = 'form_fields';

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
        'options'  => 'array',
        'required' => 'boolean',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
