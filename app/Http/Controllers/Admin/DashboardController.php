<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $events = Event::whereIn('status', ['published', 'live', 'closed'])
                       ->latest('event_date')
                       ->limit(10)
                       ->get();

        $event = null;
        if (request()->filled('event')) {
            $event = $events->firstWhere('id', (int) request('event'));
        }
        $event ??= $events->first();

        $stats = null;

        if ($event) {
            // Fix #12: cache expensive dashboard stats for 60 s per event.
            // Only live events update frequently; closed events are effectively static.
            $ttl = $event->status === 'live' ? 60 : 300;

            $stats = Cache::remember("dashboard.stats.{$event->id}", $ttl, function () use ($event) {
                $total     = $event->attendees()->count();
                $checkedIn = $event->checkedInCount();

                return [
                    'total_attendees' => $total,
                    'checked_in'      => $checkedIn,
                    'check_in_pct'    => $total > 0 ? round($checkedIn / $total * 100, 1) : 0,
                    'total_leads'     => $event->leads()->count(),
                    'total_forms'     => $event->forms()->count(),
                    'total_feedback'  => $event->feedback()->count(),
                    'active_session'  => $event->currentSession()?->load('speakers'),
                ];
            });

            // Recent check-ins are always fresh (they change every few seconds during events)
            $stats['recent_checkins'] = $event->checkIns()
                ->with('attendee')
                ->latest('checked_in_at')
                ->limit(8)
                ->get();
        }

        return view('admin.dashboard', compact('event', 'stats', 'events'));
    }
}
