<?php

namespace App\Jobs;

use App\Models\Attendee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GenerateCertificate implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly Attendee $attendee) {}

    public function handle(): void
    {
        $pdf = Pdf::loadView('certificates.certificate', [
            'attendee' => $this->attendee->load('event'),
        ])->setPaper('a4', 'landscape');

        $path = "certificates/{$this->attendee->event_id}/{$this->attendee->id}.pdf";
        Storage::disk('public')->put($path, $pdf->output());
    }
}
