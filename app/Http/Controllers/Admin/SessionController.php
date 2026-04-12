<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Session;
use App\Models\User;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function index(Event $event)
    {
        // Fix #15: withAvg prevents N+1 when view calls averageRating()
        $sessions = $event->sessions()
            ->withCount('feedback')
            ->withAvg('feedback', 'rating')
            ->get();

        return view('admin.sessions.index', compact('event', 'sessions'));
    }

    public function create(Event $event)
    {
        $speakers = User::whereHas('role', fn($q) => $q->where('name', 'speaker'))->get();
        return view('admin.sessions.create', compact('event', 'speakers'));
    }

    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'room'          => 'nullable|string|max:100',
            'starts_at'     => 'required|date',
            'ends_at'       => 'required|date|after:starts_at',
            'type'          => 'required|in:talk,workshop,panel,break',
            'capacity'      => 'nullable|integer|min:1',
            'sort_order'    => 'nullable|integer',
            'speaker_ids'   => 'nullable|array',
            'speaker_ids.*' => 'exists:users,id',
        ]);

        // Fix #4: strip speaker_ids — it is not a column on event_sessions
        $speakerIds = $validated['speaker_ids'] ?? [];
        $sessionData = collect($validated)->except('speaker_ids')->toArray();

        $session = $event->sessions()->create($sessionData);

        if ($speakerIds) {
            $sync = collect($speakerIds)->mapWithKeys(fn($id) => [$id => ['role' => 'speaker']]);
            $session->speakers()->sync($sync);
        }

        return redirect()->route('admin.events.sessions.index', $event)
            ->with('success', "Session '{$session->title}' created.");
    }

    public function edit(Event $event, Session $session)
    {
        $speakers = User::whereHas('role', fn($q) => $q->where('name', 'speaker'))->get();
        $session->load('speakers');
        return view('admin.sessions.edit', compact('event', 'session', 'speakers'));
    }

    public function update(Request $request, Event $event, Session $session)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'room'          => 'nullable|string|max:100',
            'starts_at'     => 'required|date',
            'ends_at'       => 'required|date|after:starts_at',
            'type'          => 'required|in:talk,workshop,panel,break',
            'capacity'      => 'nullable|integer|min:1',
            'sort_order'    => 'nullable|integer',
            'speaker_ids'   => 'nullable|array',
            'speaker_ids.*' => 'exists:users,id',
        ]);

        // Fix #4: strip speaker_ids before mass-assignment
        $speakerIds = $validated['speaker_ids'] ?? [];
        $sessionData = collect($validated)->except('speaker_ids')->toArray();

        $session->update($sessionData);

        $sync = collect($speakerIds)->mapWithKeys(fn($id) => [$id => ['role' => 'speaker']]);
        $session->speakers()->sync($sync);

        return redirect()->route('admin.events.sessions.index', $event)
            ->with('success', 'Session updated.');
    }

    public function destroy(Event $event, Session $session)
    {
        $session->delete();
        return redirect()->route('admin.events.sessions.index', $event)
            ->with('success', 'Session deleted.');
    }
}
