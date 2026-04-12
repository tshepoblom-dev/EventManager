<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateCertificate;
use App\Models\Attendee;
use App\Models\Event;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function index(Event $event)
    {
        $attendees = $event->attendees()
            ->whereHas('checkIn')   // only checked-in attendees get certs
            ->get();

        // Fix #13: one Storage::files() call instead of N individual exists() calls
        $certDir      = "certificates/{$event->id}";
        $existingFiles = collect(Storage::disk('public')->files($certDir))
            ->map(fn($path) => basename($path, '.pdf'))   // ["123", "456", …]
            ->flip();                                      // flip for O(1) lookup

        $attendees = $attendees->map(fn($a) => [
            'attendee'    => $a,
            'cert_exists' => isset($existingFiles[(string) $a->id]),
        ]);

        return view('admin.certificates.index', compact('event', 'attendees'));
    }

    public function generateBulk(Event $event)
    {
        $event->attendees()->whereHas('checkIn')->each(function (Attendee $a) {
            GenerateCertificate::dispatch($a)->onQueue('notifications');
        });

        return back()->with('success', 'Certificate generation queued for all checked-in attendees.');
    }

    public function download(Event $event, Attendee $attendee)
    {
        $path = "certificates/{$event->id}/{$attendee->id}.pdf";

        if (!Storage::disk('public')->exists($path)) {
            $pdf = Pdf::loadView('certificates.certificate', [
                'attendee' => $attendee->load('event'),
            ])->setPaper('a4', 'landscape');

            return response($pdf->output(), 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="certificate-' . $attendee->id . '.pdf"',
            ]);
        }

        return Storage::disk('public')->download($path, "certificate-{$attendee->full_name}.pdf");
    }

    public function emailCertificate(Event $event, Attendee $attendee)
    {
        GenerateCertificate::dispatch($attendee)->onQueue('notifications');
        return back()->with('success', "Certificate queued for {$attendee->full_name}.");
    }
}
