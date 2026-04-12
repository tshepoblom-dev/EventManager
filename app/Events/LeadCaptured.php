<?php

namespace App\Events;

use App\Models\Lead;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadCaptured implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Lead $lead) {}

    public function broadcastOn(): array
    {
        $channels = [new Channel("event.{$this->lead->event_id}.dashboard")];

        if ($this->lead->sponsor_id) {
            $channels[] = new Channel("sponsor.{$this->lead->sponsor_id}.leads");
        }

        return $channels;
    }

    public function broadcastAs(): string { return 'lead.captured'; }

    public function broadcastWith(): array
    {
        return [
            'lead_id'        => $this->lead->id,
            'name'           => $this->lead->first_name . ' ' . $this->lead->last_name,
            'company'        => $this->lead->company,
            'interest_level' => $this->lead->interest_level,
            'pipeline_stage' => $this->lead->pipeline_stage,
            'source'         => $this->lead->source,
            'captured_at'    => $this->lead->created_at->toISOString(),
        ];
    }
}
