<?php

use App\Models\Attendee;
use App\Models\Sponsor;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Phase 2 — check-in + dashboard channels
| Phase 4 — programme channel (session highlighting)
| Phase 5 — live admin dashboard
| Phase 6 — sponsor lead channel
| Phase 7 — attendee networking channel
|
*/

// ── Admin / Staff channels ─────────────────────────────────────────────

// Real-time check-in feed visible to all staff at the event
Broadcast::channel('event.{eventId}.checkins', function ($user) {
    return $user->isStaff();
});

// Live admin dashboard — all stats updates
Broadcast::channel('event.{eventId}.dashboard', function ($user) {
    return $user->isStaff();
});

// ── Public programme channel ────────────────────────────────────────────

// Any authenticated user watching the programme (attendees, speakers, sponsors)
Broadcast::channel('event.{eventId}.programme', function ($user) {
    return auth()->check(); // all authenticated users
});

// ── Sponsor channels ────────────────────────────────────────────────────

// Sponsor sees only their own lead notifications
Broadcast::channel('sponsor.{sponsorId}.leads', function ($user, $sponsorId) {
    return Sponsor::where('id', $sponsorId)
                  ->where('user_id', $user->id)
                  ->exists();
});

// ── Attendee networking ─────────────────────────────────────────────────

// Private channel per attendee for connection notifications
Broadcast::channel('attendee.{attendeeId}.connections', function ($user, $attendeeId) {
    // User model has no attendees() relation — query Attendee directly
    return Attendee::where('id', $attendeeId)
                   ->where('user_id', $user->id)
                   ->exists();
});
