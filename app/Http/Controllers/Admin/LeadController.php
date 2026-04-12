<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Lead;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    public function index(Event $event)
    {
        $leads = $event->leads()
            ->with(['sponsor', 'attendee'])
            ->when(request('stage'),  fn($q, $s) => $q->where('pipeline_stage', $s))
            ->when(request('level'),  fn($q, $l) => $q->where('interest_level', $l))
            ->when(request('search'), fn($q, $s) =>
                $q->where(fn($q2) =>
                    $q2->where('first_name', 'like', "%$s%")
                       ->orWhere('last_name',  'like', "%$s%")
                       ->orWhere('email',      'like', "%$s%")
                       ->orWhere('company',    'like', "%$s%")
                )
            )
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $stats = [
            'total'       => $event->leads()->count(),
            'hot'         => $event->leads()->where('interest_level', 'hot')->count(),
            'new'         => $event->leads()->where('pipeline_stage', 'new')->count(),
            'paid'        => $event->leads()->where('pipeline_stage', 'paid')->count(),
        ];

        return view('admin.leads.index', compact('event', 'leads', 'stats'));
    }

    public function updateStage(Request $request, Event $event, Lead $lead)
    {
        $request->validate(['pipeline_stage' => 'required|in:new,contacted,followed_up,paid']);
        $lead->update(['pipeline_stage' => $request->pipeline_stage]);
        return response()->json(['ok' => true, 'stage' => $lead->pipeline_stage]);
    }

    public function export(Event $event): StreamedResponse
    {
        $leads = $event->leads()->with(['sponsor'])->get();

        return response()->streamDownload(function () use ($leads) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['First Name','Last Name','Email','Phone','Company','Business Type','Interest','Stage','Source','Sponsor','Date']);
            foreach ($leads as $l) {
                fputcsv($handle, [
                    $l->first_name, $l->last_name, $l->email, $l->phone,
                    $l->company, $l->business_type, $l->interest_level,
                    $l->pipeline_stage, $l->source,
                    $l->sponsor?->company_name,
                    $l->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($handle);
        }, "leads-{$event->id}-" . now()->format('Ymd') . '.csv');
    }
}
