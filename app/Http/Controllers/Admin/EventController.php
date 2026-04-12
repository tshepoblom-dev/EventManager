<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::withCount(['attendees', 'checkIns'])
                       ->latest('event_date')
                       ->paginate(15);

        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'venue'         => 'nullable|string|max:255',
            'event_date'    => 'required|date',
            'start_time'    => 'required',
            'end_time'      => 'required',
            'status'        => 'required|in:draft,published,live,closed',
            'primary_color' => 'nullable|string|max:7',
            'logo'          => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('events/logos', 'public');
        }

        $event = Event::create($validated);

        return redirect()->route('admin.events.show', $event)
                         ->with('success', "Event '{$event->name}' created successfully.");
    }

    public function show(Event $event)
    {
        $event->loadCount(['attendees', 'checkIns', 'sessions', 'leads', 'sponsors']);

        $recentAttendees = $event->attendees()->latest()->limit(5)->get();

        return view('admin.events.show', compact('event', 'recentAttendees'));
    }

    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'venue'         => 'nullable|string|max:255',
            'event_date'    => 'required|date',
            'start_time'    => 'required',
            'end_time'      => 'required',
            'status'        => 'required|in:draft,published,live,closed',
            'primary_color' => 'nullable|string|max:7',
            'logo'          => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($event->logo) {
                Storage::disk('public')->delete($event->logo);
            }
            $validated['logo'] = $request->file('logo')->store('events/logos', 'public');
        }

        $event->update($validated);

        return redirect()->route('admin.events.show', $event)
                         ->with('success', 'Event updated.');
    }

    public function destroy(Event $event)
    {
        if ($event->logo) {
            Storage::disk('public')->delete($event->logo);
        }

        $event->delete();

        return redirect()->route('admin.events.index')
                         ->with('success', 'Event deleted.');
    }
}
