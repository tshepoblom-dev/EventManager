<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Feedback;
use App\Models\Session;
use Illuminate\Http\Request;

class ProgrammeController extends Controller
{
    public function index(Event $event)
    {
        $sessions = $event->sessions()
            ->with('speakers')
            ->withCount('feedback')
            ->withAvg('feedback', 'rating')
            ->get();

        $currentSession = $event->currentSession();

        return view('programme.index', compact('event', 'sessions', 'currentSession'));
    }

    // POST feedback for a session
    public function submitFeedback(Request $request, Event $event, Session $session)
    {
        $request->validate([
            'rating'  => 'nullable|integer|between:1,5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Prevent duplicate feedback from same attendee
        $attendeeId = null;
        if (auth()->check()) {
            $attendee = $event->attendees()->where('email', auth()->user()->email)->first();
            $attendeeId = $attendee?->id;

            if ($attendeeId) {
                $exists = Feedback::where('event_session_id', $session->id)
                    ->where('attendee_id', $attendeeId)
                    ->exists();
                if ($exists) {
                    return back()->with('warning', 'You have already submitted feedback for this session.');
                }
            }
        }

        Feedback::create([
            'event_id'         => $event->id,
            'event_session_id' => $session->id,
            'attendee_id'      => $attendeeId,
            'rating'           => $request->rating,
            'comment'          => $request->comment,
            'type'             => 'session',
        ]);

        if (request()->expectsJson()) {
            return response()->json([
                'ok'             => true,
                'average_rating' => round($session->averageRating(), 1),
                'feedback_count' => $session->feedback()->count(),
            ]);
        }

        return back()->with('success', 'Thank you for your feedback!');
    }
}
