<?php

namespace App\Http\Controllers\Staff;

use App\Events\AttendeeCheckedIn;
use App\Http\Controllers\Controller;
use App\Models\Attendee;
use App\Models\Check_in;
use App\Models\Event;
use App\Services\QrCodeService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CheckInController extends Controller
{
    public function __construct(private readonly QrCodeService $qrService) {}

    /**
     * Scanner UI — staff open this on their device.
     */
    public function index(Event $event)
    {
        $stats = [
            'total'      => $event->attendees()->count(),
            'checked_in' => $event->checkedInCount(),
        ];

        $recent = $event->checkIns()
            ->with('attendee')
            ->latest('checked_in_at')
            ->limit(10)
            ->get();

        return view('staff.checkin.index', compact('event', 'stats', 'recent'));
    }

    /**
     * Process a QR token scan — called via AJAX from the scanner UI.
     */
    public function scanQr(Request $request, Event $event): JsonResponse
    {
        $request->validate(['token' => 'required|string']);

        $attendee = $this->qrService->resolveToken($request->token);

        if (! $attendee) {
            return response()->json(['status' => 'not_found', 'message' => 'QR code not recognised.'], 404);
        }

        if ($attendee->event_id !== $event->id) {
            return response()->json(['status' => 'wrong_event', 'message' => 'This QR belongs to a different event.'], 422);
        }

        return $this->performCheckIn($attendee, $event, 'qr', $request->input('station'));
    }

    /**
     * Process a manual name/email search check-in.
     */
    public function searchCheckIn(Request $request, Event $event): JsonResponse
    {
        $request->validate(['query' => 'required|string|min:2']);

        $query = $request->input('query');

        $attendees = $event->attendees()
            ->where(fn($q) =>
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name',  'like', "%{$query}%")
                  ->orWhere('email',      'like', "%{$query}%")
            )
            ->withExists('checkIn')
            ->limit(10)
            ->get()
            ->map(fn($a) => [
                'id'          => $a->id,
                'name'        => $a->full_name,
                'email'       => $a->email,
                'company'     => $a->company,
                'ticket_type' => $a->ticket_type,
                'checked_in'  => (bool) $a->check_in_exists,
            ]);

        return response()->json(['attendees' => $attendees]);
    }

    /**
     * Check in a specific attendee by ID (from search result click).
     */
    public function checkInById(Request $request, Event $event, Attendee $attendee): JsonResponse
    {
        if ($attendee->event_id !== $event->id) {
            return response()->json(['status' => 'wrong_event'], 422);
        }

        return $this->performCheckIn($attendee, $event, 'manual', $request->input('station'));
    }

    // ── Private ────────────────────────────────────────────────────────────

    private function performCheckIn(Attendee $attendee, Event $event, string $method, ?string $station): JsonResponse
    {
        // Fix #25: use relationLoaded() to avoid redundant query when relation already eager-loaded
        if ($attendee->relationLoaded('checkIn') ? $attendee->checkIn !== null : $attendee->isCheckedIn()) {
            $checkIn = $attendee->checkIn ?? $attendee->checkIn()->first();
            return response()->json([
                'status'   => 'already_checked_in',
                'message'  => "{$attendee->full_name} is already checked in.",
                'attendee' => [
                    'name'          => $attendee->full_name,
                    'email'         => $attendee->email,
                    'company'       => $attendee->company,
                    'ticket_type'   => $attendee->ticket_type,
                    'checked_in_at' => $checkIn->checked_in_at->format('H:i'),
                ],
            ], 409);
        }

        try {
            // Fix #5: wrap in try/catch to handle the race condition where two scanners
            // simultaneously pass the isCheckedIn() check before either write completes.
            // The DB unique constraint on (attendee_id) catches the second insert.
            $checkIn = Check_in::create([
                'event_id'      => $event->id,
                'attendee_id'   => $attendee->id,
                'checked_in_by' => auth()->id(),
                'checked_in_at' => now(),
                'method'        => $method,
                'station'       => $station,
            ]);
        } catch (UniqueConstraintViolationException) {
            $checkIn = $attendee->checkIn()->first();
            return response()->json([
                'status'   => 'already_checked_in',
                'message'  => "{$attendee->full_name} was just checked in at another station.",
                'attendee' => [
                    'name'          => $attendee->full_name,
                    'email'         => $attendee->email,
                    'company'       => $attendee->company,
                    'ticket_type'   => $attendee->ticket_type,
                    'checked_in_at' => $checkIn->checked_in_at->format('H:i'),
                ],
            ], 409);
        }

        // Fix #11: invalidate the cached checkedInCount so the next read is fresh
        Cache::forget("event.{$event->id}.checked_in_count");

        $totalCheckedIn = $event->checkedInCount();
        $totalAttendees = $event->attendees()->count();

        AttendeeCheckedIn::dispatch($attendee, $checkIn, $totalCheckedIn, $totalAttendees);

        return response()->json([
            'status'   => 'success',
            'message'  => "Welcome, {$attendee->full_name}!",
            'attendee' => [
                'name'          => $attendee->full_name,
                'email'         => $attendee->email,
                'company'       => $attendee->company,
                'ticket_type'   => $attendee->ticket_type,
                'checked_in_at' => $checkIn->checked_in_at->format('H:i'),
            ],
            'stats' => [
                'total_checked_in' => $totalCheckedIn,
                'total_attendees'  => $totalAttendees,
            ],
        ]);
    }
}
