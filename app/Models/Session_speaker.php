<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot model for the session_speakers table.
 * Gives access to the 'role' column stored on the pivot.
 */
class Session_speaker extends Pivot
{
    protected $table = 'session_speakers';

    protected $fillable = ['event_session_id', 'user_id', 'role'];
}
