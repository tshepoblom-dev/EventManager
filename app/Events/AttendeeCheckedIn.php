<?php

namespace App\Events;

use App\Models\Attendee;
use App\Models\Check_in;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttendeeCheckedIn implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Attendee $attendee,
        public readonly Check_in $checkIn,
        public readonly int      $totalCheckedIn,
        public readonly int      $totalAttendees,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("event.{$this->attendee->event_id}.checkins"),
            new Channel("event.{$this->attendee->event_id}.dashboard"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'attendee.checked_in';
    }

    public function broadcastWith(): array
    {
        return [
            'attendee' => [
                'id'         => $this->attendee->id,
                'name'       => $this->attendee->full_name,
                'email'      => $this->attendee->email,
                'company'    => $this->attendee->company,
                'ticket_type'=> $this->attendee->ticket_type,
            ],
            'checked_in_at'   => $this->checkIn->checked_in_at->toISOString(),
            'method'          => $this->checkIn->method,
            'station'         => $this->checkIn->station,
            'total_checked_in'=> $this->totalCheckedIn,
            'total_attendees' => $this->totalAttendees,
        ];
    }
}
