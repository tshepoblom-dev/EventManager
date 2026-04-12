<?php

namespace App\Events;

use App\Models\Session;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionHighlighted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Fix #23: session is nullable so we can broadcast a "clear highlights"
     * event when no session is currently active (gap between sessions / end of day).
     * eventId is required when session is null so we still know which channel to broadcast on.
     */
    public function __construct(
        public readonly ?Session $session,
        public readonly ?int     $eventId = null,
    ) {}

    public function broadcastOn(): array
    {
        $id = $this->session?->event_id ?? $this->eventId;
        return [new Channel("event.{$id}.programme")];
    }

    public function broadcastAs(): string { return 'session.highlighted'; }

    public function broadcastWith(): array
    {
        if ($this->session === null) {
            // null session_id tells the frontend to clear all highlights
            return ['session_id' => null];
        }

        return [
            'session_id' => $this->session->id,
            'title'      => $this->session->title,
            'room'       => $this->session->room,
            'starts_at'  => $this->session->starts_at->toISOString(),
            'ends_at'    => $this->session->ends_at->toISOString(),
            'type'       => $this->session->type,
        ];
    }
}
