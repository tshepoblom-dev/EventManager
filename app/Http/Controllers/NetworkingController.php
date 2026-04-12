<?php

namespace App\Http\Controllers;

use App\Events\ConnectionMade;
use App\Http\Controllers\Controller;
use App\Models\Attendee;
use App\Models\Connection;
use App\Models\Event;
use Illuminate\Http\Request;

class NetworkingController extends Controller
{
    private function myAttendee(Event $event): Attendee
    {
        return $event->attendees()
            ->where('email', auth()->user()->email)
            ->firstOrFail();
    }

    public function index(Event $event)
    {
        $me = $this->myAttendee($event);

        $connections = Connection::where(fn($q) =>
            $q->where('requester_id', $me->id)->orWhere('receiver_id', $me->id)
        )
        ->where('event_id', $event->id)
        ->with(['requester', 'receiver'])
        ->get();

        $attendees = $event->attendees()
            ->where('id', '!=', $me->id)
            ->where(fn($q) => $q->whereNotNull('user_id'))   // opt-in: must have a user account
            ->limit(40)
            ->get();

        return view('networking.index', compact('event', 'me', 'connections', 'attendees'));
    }

    // Scan another attendee's QR to connect
    public function connect(Request $request, Event $event)
    {
        $request->validate(['qr_code' => 'required|string']);

        $me     = $this->myAttendee($event);
        $target = $event->attendees()->where('qr_code', $request->qr_code)->first();

        if (!$target) {
            return response()->json(['status' => 'not_found', 'message' => 'Attendee not found.'], 404);
        }

        if ($target->id === $me->id) {
            return response()->json(['status' => 'self', 'message' => "That's your own QR code."], 422);
        }

        $exists = Connection::where('event_id', $event->id)
            ->where(fn($q) =>
                $q->where(['requester_id' => $me->id, 'receiver_id' => $target->id])
                  ->orWhere(['requester_id' => $target->id, 'receiver_id' => $me->id])
            )->exists();

        if ($exists) {
            return response()->json(['status' => 'already_connected', 'message' => 'Already connected.'], 409);
        }

        $connection = Connection::create([
            'event_id'     => $event->id,
            'requester_id' => $me->id,
            'receiver_id'  => $target->id,
            'status'       => 'accepted',
            'connected_at' => now(),
        ]);

        // Notify the receiver via Reverb
        ConnectionMade::dispatch($connection->load('requester'));

        return response()->json([
            'status'  => 'connected',
            'message' => "Connected with {$target->full_name}!",
            'target'  => ['name' => $target->full_name, 'company' => $target->company],
        ]);
    }

    public function profile(Event $event, Attendee $attendee)
    {
        // Only show profiles of people who have a linked user account (opted in)
        abort_unless($attendee->user_id !== null, 404);
        $attendee->load('user');
        return view('networking.profile', compact('event', 'attendee'));
    }
}
