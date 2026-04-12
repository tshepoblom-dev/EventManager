<?php

namespace App\Jobs;

use App\Mail\AttendeeQrMail;
use App\Models\Attendee;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendQrEmail implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $backoff = 60; // seconds between retries

    public function __construct(public readonly Attendee $attendee) {}

    public function handle(): void
    {
        if (! $this->attendee->qr_code) {
            // QR not yet generated — re-queue after generation
            GenerateAttendeeQr::dispatch($this->attendee, sendEmail: true);
            return;
        }

        Mail::to($this->attendee->email)->send(new AttendeeQrMail($this->attendee));

        $this->attendee->update(['qr_emailed' => true]);
    }
}
