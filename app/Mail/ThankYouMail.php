<?php

namespace App\Mail;

use App\Models\Attendee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ThankYouMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Attendee $attendee) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Thank you for attending — {$this->attendee->event->name}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.thank-you');
    }
}
