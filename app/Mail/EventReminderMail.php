<?php

namespace App\Mail;

use App\Models\Attendee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Attendee $attendee) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "See you tomorrow — {$this->attendee->event->name}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.reminder');
    }
}
