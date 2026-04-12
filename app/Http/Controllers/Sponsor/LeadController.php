<?php

namespace App\Http\Controllers\Sponsor;

use App\Events\LeadCaptured;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Lead;
use App\Models\Sponsor;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    /**
     * Fix #9: Return 403 (not 404) when the authenticated sponsor user
     * does not belong to this event. 404 leaks information about event existence.
     */
    private function getSponsor(Event $event): Sponsor
    {
        $sponsor = $event->sponsors()->where('user_id', auth()->id())->first();

        abort_if($sponsor === null, 403, 'You do not have access to this event as a sponsor.');

        return $sponsor;
    }

    public function dashboard(Event $event)
    {
        $sponsor = $this->getSponsor($event);
        $leads   = $sponsor->leads()->latest()->get();

        $stats = [
            'total'       => $leads->count(),
            'hot'         => $leads->where('interest_level', 'hot')->count(),
            'warm'        => $leads->where('interest_level', 'warm')->count(),
            'cold'        => $leads->where('interest_level', 'cold')->count(),
            'new'         => $leads->where('pipeline_stage', 'new')->count(),
            'contacted'   => $leads->where('pipeline_stage', 'contacted')->count(),
            'followed_up' => $leads->where('pipeline_stage', 'followed_up')->count(),
            'paid'        => $leads->where('pipeline_stage', 'paid')->count(),
        ];

        return view('sponsor.dashboard', compact('event', 'sponsor', 'leads', 'stats'));
    }

    public function create(Event $event)
    {
        $sponsor = $this->getSponsor($event);
        return view('sponsor.leads.create', compact('event', 'sponsor'));
    }

    public function store(Request $request, Event $event)
    {
        $sponsor = $this->getSponsor($event);

        $validated = $request->validate([
            'first_name'     => 'required|string|max:100',
            'last_name'      => 'required|string|max:100',
            'email'          => 'required|email|max:255',
            'phone'          => 'nullable|string|max:30',
            'company'        => 'nullable|string|max:150',
            'business_type'  => 'nullable|string|max:150',
            'interest_level' => 'required|in:hot,warm,cold',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $lead = Lead::create([
            ...$validated,
            'event_id'       => $event->id,
            'sponsor_id'     => $sponsor->id,
            'pipeline_stage' => 'new',
            'source'         => 'booth',
        ]);

        LeadCaptured::dispatch($lead);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'lead_id' => $lead->id]);
        }

        return redirect()->route('sponsor.dashboard', $event)
            ->with('success', "{$lead->first_name} {$lead->last_name} captured as a lead.");
    }

    public function updateStage(Request $request, Event $event, Lead $lead)
    {
        $sponsor = $this->getSponsor($event);
        abort_unless($lead->sponsor_id === $sponsor->id, 403);

        $request->validate(['pipeline_stage' => 'required|in:new,contacted,followed_up,paid']);
        $lead->update(['pipeline_stage' => $request->pipeline_stage]);

        return response()->json(['ok' => true]);
    }

    public function addNote(Request $request, Event $event, Lead $lead)
    {
        $sponsor = $this->getSponsor($event);
        abort_unless($lead->sponsor_id === $sponsor->id, 403);

        $request->validate(['notes' => 'required|string|max:2000']);
        $lead->update(['notes' => $request->notes]);

        return response()->json(['ok' => true]);
    }

    public function export(Event $event): StreamedResponse
    {
        $sponsor = $this->getSponsor($event);
        $leads   = $sponsor->leads()->get();

        return response()->streamDownload(function () use ($leads, $sponsor) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['First Name','Last Name','Email','Phone','Company','Business Type','Interest','Stage','Notes','Date']);
            foreach ($leads as $l) {
                fputcsv($handle, [
                    $l->first_name, $l->last_name, $l->email, $l->phone,
                    $l->company, $l->business_type, $l->interest_level,
                    $l->pipeline_stage, $l->notes,
                    $l->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($handle);
        }, "leads-{$sponsor->company_name}-" . now()->format('Ymd') . '.csv');
    }
}
