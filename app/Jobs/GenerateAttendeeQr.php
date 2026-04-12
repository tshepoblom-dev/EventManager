<?php

namespace App\Jobs;

use App\Models\Attendee;
use App\Services\QrCodeService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Fix #14: Implements ShouldBeUnique so bulk CSV import + bulk QR send
 * cannot queue duplicate jobs for the same attendee. uniqueId() returns
 * the attendee's ID so de-duplication is per-attendee regardless of how
 * many times the job is dispatched.
 */
class GenerateAttendeeQr implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $tries           = 3;
    public int $uniqueFor       = 3600; // hold the lock for 1 hour

    public function __construct(
        public readonly Attendee $attendee,
        public readonly bool     $sendEmail = false,
    ) {}

    /**
     * Unique key — one job per attendee in the queue at any time.
     */
    public function uniqueId(): string
    {
        return (string) $this->attendee->id;
    }

    public function handle(QrCodeService $qrService): void
    {
        // Skip if already generated (e.g. job survived a duplicate flush)
        if ($this->attendee->qr_code) {
            return;
        }

        $qrService->generateForAttendee($this->attendee);

        if ($this->sendEmail) {
            SendQrEmail::dispatch($this->attendee->fresh())->onQueue('notifications');
        }
    }
}
