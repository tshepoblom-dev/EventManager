<?php

namespace App\Console\Commands;

use App\Events\SessionHighlighted;
use App\Models\Event;
use Illuminate\Console\Command;

class HighlightCurrentSession extends Command
{
    protected $signature   = 'sessions:highlight';
    protected $description = 'Broadcast the currently live session on all active events via Reverb';

    public function handle(): void
    {
        $events = Event::where('status', 'live')->get();

        foreach ($events as $event) {
            $current = $event->currentSession();

            // Fix #23: If no session is currently active (gap between sessions, or
            // the day's last session has ended), un-highlight any previously
            // highlighted session so the programme view doesn't stay stuck.
            if (! $current) {
                $staleCount = $event->sessions()
                    ->where('is_highlighted', true)
                    ->update(['is_highlighted' => false]);

                if ($staleCount > 0) {
                    // Broadcast with null session_id so the frontend clears highlights
                    SessionHighlighted::dispatch(null, $event->id);
                    $this->info("Cleared stale highlight for [{$event->name}]");
                }

                continue;
            }

            // Only broadcast if not already highlighted (avoids redundant Reverb events)
            if (! $current->is_highlighted) {
                $event->sessions()
                    ->where('id', '!=', $current->id)
                    ->where('is_highlighted', true)
                    ->update(['is_highlighted' => false]);

                $current->update(['is_highlighted' => true]);

                SessionHighlighted::dispatch($current, $event->id);

                $this->info("Highlighted: [{$event->name}] {$current->title}");
            }
        }
    }
}
