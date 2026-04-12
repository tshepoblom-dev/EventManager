<?php

namespace App\Models;

/**
 * @deprecated Use App\Models\Session instead.
 *
 * The real implementation lives in Session.php which explicitly sets
 * $table = 'event_sessions' to avoid collision with Laravel's built-in
 * session handling. This stub is kept only for backwards-compatibility;
 * all new code should reference Session directly.
 */
class Event_session extends Session
{
    //
}
