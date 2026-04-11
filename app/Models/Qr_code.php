<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\MorphTo;

use Illuminate\Database\Eloquent\Model;

class Qr_code extends Model
{
     protected $fillable = [
        'uuid',
        'type',
        'image_path',
        'scan_count',
    ];

    public function qrable(): MorphTo
    {
        return $this->morphTo();
    }
}
