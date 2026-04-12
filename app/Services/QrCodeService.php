<?php

namespace App\Services;

use App\Models\Attendee;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    /**
     * Generate a unique QR token and save the PNG to storage.
     * Returns the token string (stored on attendee->qr_code).
     */
    public function generateForAttendee(Attendee $attendee): string
    {
        $token = Str::uuid()->toString();

        $png = QrCode::format('png')
                     ->size(400)
                     ->errorCorrection('H')
                     ->generate($token);

        $path = "qrcodes/attendees/{$attendee->id}.png";
        Storage::disk('public')->put($path, $png);

        $attendee->update([
            'qr_code'       => $token,
            'qr_image_path' => $path,
        ]);

        return $token;
    }

    /**
     * Resolve a scanned token to an attendee.
     */
    public function resolveToken(string $token): ?Attendee
    {
        return Attendee::where('qr_code', $token)->first();
    }

    /**
     * Return the public URL for an attendee's QR image.
     */
    public function publicUrl(Attendee $attendee): ?string
    {
        if (! $attendee->qr_image_path) {
            return null;
        }

        return Storage::disk('public')->url($attendee->qr_image_path);
    }
}
