<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Session;
use App\Models\Speaker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index(Event $event)
    {
        $sessions = $event->sessions()
            ->with('speakers')
            ->withCount('feedback')
            ->withAvg('feedback', 'rating')
            ->get();

        // All speaker profiles (not just those with user accounts)
        $speakers = Speaker::orderBy('name')->get();

        $stats = [
            'total'    => $sessions->count(),
            'talk'     => $sessions->where('type', 'talk')->count(),
            'workshop' => $sessions->where('type', 'workshop')->count(),
            'panel'    => $sessions->where('type', 'panel')->count(),
            'break'    => $sessions->where('type', 'break')->count(),
        ];

        $sessionsData = $sessions->map(fn($s) => $this->sessionPayload($s))->values();

        $speakersData = $speakers->map(fn($s) => [
            'id'      => $s->id,
            'name'    => $s->name,
            'title'   => $s->title,
            'display' => $s->display_name,
        ])->values();

        return view('admin.sessions.index', compact(
            'event', 'stats', 'sessionsData', 'speakersData'
        ));
    }

    /** AJAX: create a session */
    public function store(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string|max:2000',
            'room'          => 'nullable|string|max:100',
            'starts_at'     => 'required|date',
            'ends_at'       => 'required|date|after:starts_at',
            'type'          => 'required|in:talk,workshop,panel,break',
            'capacity'      => 'nullable|integer|min:1',
            'speaker_ids'   => 'nullable|array',
            'speaker_ids.*' => 'exists:speakers,id',
        ]);

        $speakerIds  = $validated['speaker_ids'] ?? [];
        $sessionData = collect($validated)->except('speaker_ids')->toArray();
        $sessionData['sort_order'] = ($event->sessions()->max('sort_order') ?? 0) + 1;

        $session = $event->sessions()->create($sessionData);

        $sync = collect($speakerIds)->mapWithKeys(fn($id) => [$id => ['role' => 'speaker']]);
        $session->speakers()->sync($sync);
        $session->load('speakers');
        $session->loadCount('feedback');

        return response()->json([
            'ok'      => true,
            'session' => $this->sessionPayload($session),
        ]);
    }

    /** AJAX: update a session */
    public function update(Request $request, Event $event, Session $session): JsonResponse
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string|max:2000',
            'room'          => 'nullable|string|max:100',
            'starts_at'     => 'required|date',
            'ends_at'       => 'required|date|after:starts_at',
            'type'          => 'required|in:talk,workshop,panel,break',
            'capacity'      => 'nullable|integer|min:1',
            'speaker_ids'   => 'nullable|array',
            'speaker_ids.*' => 'exists:speakers,id',
        ]);

        $speakerIds  = $validated['speaker_ids'] ?? [];
        $sessionData = collect($validated)->except('speaker_ids')->toArray();

        $session->update($sessionData);

        $sync = collect($speakerIds)->mapWithKeys(fn($id) => [$id => ['role' => 'speaker']]);
        $session->speakers()->sync($sync);
        $session->load('speakers');
        $session->loadCount('feedback');

        return response()->json([
            'ok'      => true,
            'session' => $this->sessionPayload($session),
        ]);
    }

    /** AJAX: delete a session */
    public function destroy(Event $event, Session $session): JsonResponse
    {
        $session->delete();
        return response()->json(['ok' => true]);
    }

    /** AJAX: duplicate a session */
    public function duplicate(Event $event, Session $session): JsonResponse
    {
        $copy = $session->replicate();
        $copy->title       = $session->title . ' (copy)';
        $copy->sort_order  = ($event->sessions()->max('sort_order') ?? 0) + 1;
        $copy->is_highlighted = false;
        $copy->save();

        $speakerSync = $session->speakers->mapWithKeys(fn($s) => [$s->id => ['role' => $s->pivot->role]]);
        $copy->speakers()->sync($speakerSync);
        $copy->load('speakers');
        $copy->loadCount('feedback');

        return response()->json([
            'ok'      => true,
            'session' => $this->sessionPayload($copy),
        ]);
    }

    /** AJAX: reorder sessions */
    public function reorder(Request $request, Event $event): JsonResponse
    {
        $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer|exists:event_sessions,id',
        ]);

        foreach ($request->order as $position => $sessionId) {
            $event->sessions()->where('id', $sessionId)->update(['sort_order' => $position + 1]);
        }

        return response()->json(['ok' => true]);
    }

    // ── Private ──────────────────────────────────────────────────────────────

    private function sessionPayload(Session $session): array
    {
        return [
            'id'          => $session->id,
            'title'       => $session->title,
            'description' => $session->description,
            'room'        => $session->room,
            'starts_at'   => $session->starts_at->format('Y-m-d\TH:i'),
            'starts_fmt'  => $session->starts_at->format('H:i'),
            'ends_at'     => $session->ends_at->format('Y-m-d\TH:i'),
            'ends_fmt'    => $session->ends_at->format('H:i'),
            'type'        => $session->type,
            'capacity'    => $session->capacity,
            'sort_order'  => $session->sort_order,
            'is_live'     => $session->isLive(),
            'feedback_count'      => $session->feedback_count ?? 0,
            'feedback_avg_rating' => round($session->feedback_avg_rating ?? 0, 1),
            'speaker_ids' => $session->speakers->pluck('id')->toArray(),
            'speakers'    => $session->speakers->map(fn($s) => [
                'id'    => $s->id,
                'name'  => $s->name,
                'title' => $s->title,
            ])->values()->toArray(),
        ];
    }
}
