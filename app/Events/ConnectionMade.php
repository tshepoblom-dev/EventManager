<?php

namespace App\Events;

use App\Models\Connection;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConnectionMade implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Connection $connection) {}

    public function broadcastOn(): array
    {
        // Notify the receiver on their personal channel
        return [new Channel("attendee.{$this->connection->receiver_id}.connections")];
    }

    public function broadcastAs(): string { return 'connection.made'; }

    public function broadcastWith(): array
    {
        $requester = $this->connection->requester;
        return [
            'connection_id' => $this->connection->id,
            'from_name'     => $requester?->full_name,
            'from_company'  => $requester?->company,
            'status'        => $this->connection->status,
        ];
    }
}
