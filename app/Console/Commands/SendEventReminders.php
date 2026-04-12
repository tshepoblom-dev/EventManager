<?php

namespace App\Console\Commands;

use App\Mail\EventReminderMail;
use App\Mail\ThankYouMail;
use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendEventReminders extends Command
{
    protected $signature   = 'events:reminders';
    protected $description = 'Send reminder emails for events starting tomorrow, and thank-you emails for events that ended today';

    public function handle(): void
    {
        $tomorrow = now()->addDay()->toDateString();
        $today    = now()->toDateString();

        // ── Reminders ────────────────────────────────────────────────────
        $upcomingEvents = Event::whereDate('event_date', $tomorrow)
            ->whereIn('status', ['published', 'live'])
            ->get();

        foreach ($upcomingEvents as $event) {
            $sent = 0;
            $event->attendees()->whereHas('checkIn')->orWhere('status', 'confirmed')->each(function ($attendee) use (&$sent) {
                Mail::to($attendee->email)->queue(new EventReminderMail($attendee));
                $sent++;
            });
            $this->info("Reminders queued for [{$event->name}]: {$sent} attendees");
        }

        // ── Thank-yous ───────────────────────────────────────────────────
        $pastEvents = Event::whereDate('event_date', $today)
            ->where('status', 'closed')
            ->get();

        foreach ($pastEvents as $event) {
            $sent = 0;
            $event->attendees()->whereHas('checkIn')->each(function ($attendee) use (&$sent) {
                Mail::to($attendee->email)->queue(new ThankYouMail($attendee));
                $sent++;
            });
            $this->info("Thank-yous queued for [{$event->name}]: {$sent} attendees");
        }
    }
}
