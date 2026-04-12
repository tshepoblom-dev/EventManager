<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateAttendeeQr;
use App\Jobs\SendQrEmail;
use App\Models\Attendee;
use App\Models\Event;
use App\Services\CsvImportService;
use Illuminate\Http\Request;

class AttendeeController extends Controller
{
    public function index(Event $event)
    {
        $attendees = $event->attendees()
            ->withExists('checkIn')
            ->when(request('search'), fn($q, $s) =>
                $q->where(fn($q2) =>
                    $q2->where('first_name', 'like', "%{$s}%")
                       ->orWhere('last_name',  'like', "%{$s}%")
                       ->orWhere('email',      'like', "%{$s}%")
                       ->orWhere('company',    'like', "%{$s}%")
                )
            )
            ->when(request('ticket_type'), fn($q, $t) => $q->where('ticket_type', $t))
            ->when(request('checked_in') === 'yes', fn($q) => $q->whereHas('checkIn'))
            ->when(request('checked_in') === 'no',  fn($q) => $q->whereDoesntHave('checkIn'))
            ->orderBy('last_name')
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'total'      => $event->attendees()->count(),
            'checked_in' => $event->checkedInCount(),
            'qr_sent'    => $event->attendees()->where('qr_emailed', true)->count(),
        ];

        return view('admin.attendees.index', compact('event', 'attendees', 'stats'));
    }

    public function create(Event $event)
    {
        return view('admin.attendees.create', compact('event'));
    }

    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'first_name'  => 'required|string|max:100',
            'last_name'   => 'required|string|max:100',
            'email'       => [
                'required', 'email',
                \Illuminate\Validation\Rule::unique('attendees')
                    ->where('event_id', $event->id),
            ],
            'phone'       => 'nullable|string|max:30',
            'company'     => 'nullable|string|max:150',
            'job_title'   => 'nullable|string|max:150',
            'ticket_type' => 'required|in:general,vip,speaker,sponsor',
        ]);

        $attendee = $event->attendees()->create([
            ...$validated,
            'source' => 'manual',
            'status' => 'registered',
        ]);

        // Generate QR and optionally email it
        $sendEmail = $request->boolean('send_qr_email');
        GenerateAttendeeQr::dispatch($attendee, sendEmail: $sendEmail);

        return redirect()->route('admin.events.attendees.index', $event)
                         ->with('success', "{$attendee->full_name} added. QR code generation queued.");
    }

    public function show(Event $event, Attendee $attendee)
    {
        $attendee->load(['checkIn.checkedInBy', 'event']);

        return view('admin.attendees.show', compact('event', 'attendee'));
    }

    public function destroy(Event $event, Attendee $attendee)
    {
        $attendee->delete();

        return redirect()->route('admin.events.attendees.index', $event)
                         ->with('success', 'Attendee removed.');
    }

    // ── CSV Import ─────────────────────────────────────────────────────────

    public function importForm(Event $event)
    {
        return view('admin.attendees.import', compact('event'));
    }

    public function import(Request $request, Event $event, CsvImportService $csvService)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $result = $csvService->import($request->file('csv_file'), $event);

        $message = "Import complete: {$result['imported']} imported, {$result['skipped']} skipped.";

        if (! empty($result['errors'])) {
            return redirect()->route('admin.events.attendees.index', $event)
                             ->with('warning', $message)
                             ->with('import_errors', $result['errors']);
        }

        return redirect()->route('admin.events.attendees.index', $event)
                         ->with('success', $message);
    }

    // ── QR Email ───────────────────────────────────────────────────────────

    public function sendQr(Event $event, Attendee $attendee)
    {
        if (! $attendee->qr_code) {
            GenerateAttendeeQr::dispatch($attendee, sendEmail: true);
        } else {
            SendQrEmail::dispatch($attendee);
        }

        return back()->with('success', "QR email queued for {$attendee->full_name}.");
    }

    public function sendQrBulk(Request $request, Event $event)
    {
        $request->validate([
            'attendee_ids'   => 'nullable|array',
            'attendee_ids.*' => 'integer|exists:attendees,id',
        ]);

        $query = $event->attendees()->where('qr_emailed', false);

        if ($request->filled('attendee_ids')) {
            $query->whereIn('id', $request->attendee_ids);
        }

        $count = 0;
        $query->each(function (Attendee $attendee) use (&$count) {
            if (! $attendee->qr_code) {
                GenerateAttendeeQr::dispatch($attendee, sendEmail: true);
            } else {
                SendQrEmail::dispatch($attendee);
            }
            $count++;
        });

        return back()->with('success', "QR emails queued for {$count} attendee(s).");
    }
}
