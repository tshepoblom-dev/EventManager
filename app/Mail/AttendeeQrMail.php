<?php

namespace App\Mail;

use App\Models\Attendee;
use App\Services\QrCodeService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class AttendeeQrMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Attendee $attendee) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Your QR check-in code — {$this->attendee->event->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.attendee-qr',
        );
    }

    public function attachments(): array
    {
        $path = $this->attendee->qr_image_path;

        if ($path && Storage::disk('public')->exists($path)) {
            return [
                \Illuminate\Mail\Mailables\Attachment::fromStorageDisk('public', $path)
                    ->as('qr-code.png')
                    ->withMime('image/png'),
            ];
        }

        return [];
    }
}
