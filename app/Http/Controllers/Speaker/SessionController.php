<?php

namespace App\Http\Controllers\Speaker;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Session;

class SessionController extends Controller
{
    public function index(Event $event)
    {
        $sessions = auth()->user()
            ->sessions()
            ->where('event_id', $event->id)
            ->withCount('feedback')
            ->withAvg('feedback', 'rating')
            ->get();

        return view('speaker.sessions.index', compact('event', 'sessions'));
    }

    public function show(Event $event, Session $session)
    {
        // Only the assigned speaker can view
        abort_unless(
            $session->speakers()->where('users.id', auth()->id())->exists(),
            403
        );

        $session->load([
            'feedback' => fn($q) => $q->latest()->limit(50),
            'feedback.attendee',
        ]);

        $stats = [
            'average'    => round($session->averageRating(), 1),
            'count'      => $session->feedback()->count(),
            'by_rating'  => $session->feedback()
                ->whereNotNull('rating')
                ->selectRaw('rating, count(*) as total')
                ->groupBy('rating')
                ->pluck('total', 'rating')
                ->toArray(),
        ];

        return view('speaker.sessions.show', compact('event', 'session', 'stats'));
    }
}
