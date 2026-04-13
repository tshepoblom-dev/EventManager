<?php

namespace App\Mail;

use App\Models\Attendee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AttendeeInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Attendee $attendee,
        public readonly string   $inviteUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You\'re invited to create your Heidedal Scale Up account',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.attendee-invite',
        );
    }
}
