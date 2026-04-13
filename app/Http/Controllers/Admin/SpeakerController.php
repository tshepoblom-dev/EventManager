<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendee;
use App\Models\Speaker;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SpeakerController extends Controller
{
    public function index(Request $request)
    {
        $speakers = Speaker::with(['user', 'attendee'])
            ->when($request->search, fn($q, $s) =>
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('title', 'like', "%{$s}%")
            )
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.speakers.index', compact('speakers'));
    }

    public function create()
    {
        // Attendees who don't yet have a speaker profile
        $attendees = Attendee::doesntHave('speaker')
            ->orderBy('first_name')
            ->get();

        // Users with speaker role who don't yet have a speaker profile
        $users = User::whereHas('role', fn($q) => $q->where('name', 'speaker'))
            ->doesntHave('speaker')
            ->orderBy('name')
            ->get();

        return view('admin.speakers.create', compact('attendees', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'title'       => 'nullable|string|max:255',
            'bio'         => 'nullable|string|max:5000',
            'linkedin'    => 'nullable|url|max:255',
            'twitter'     => 'nullable|string|max:255',
            'photo'       => 'nullable|image|max:2048',
            'attendee_id' => 'nullable|exists:attendees,id',
            'user_id'     => 'nullable|exists:users,id',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('speakers', 'public');
        }

        // Auto-fill name/email from linked attendee or user if not provided
        if (empty($validated['name']) || empty($validated['email'])) {
            if ($validated['attendee_id'] ?? null) {
                $attendee = Attendee::find($validated['attendee_id']);
                $validated['name']  ??= $attendee->full_name;
                $validated['email'] ??= $attendee->email;
            } elseif ($validated['user_id'] ?? null) {
                $user = User::find($validated['user_id']);
                $validated['name']  ??= $user->name;
                $validated['email'] ??= $user->email;
            }
        }

        Speaker::create($validated);

        return redirect()->route('admin.speakers.index')
                         ->with('success', 'Speaker profile created.');
    }

    public function edit(Speaker $speaker)
    {
        $attendees = Attendee::doesntHave('speaker')
            ->orWhere('id', $speaker->attendee_id)
            ->orderBy('first_name')
            ->get();

        $users = User::whereHas('role', fn($q) => $q->where('name', 'speaker'))
            ->where(fn($q) =>
                $q->doesntHave('speaker')
                  ->orWhere('id', $speaker->user_id)
            )
            ->orderBy('name')
            ->get();

        return view('admin.speakers.edit', compact('speaker', 'attendees', 'users'));
    }

    public function update(Request $request, Speaker $speaker)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'nullable|email|max:255',
            'title'       => 'nullable|string|max:255',
            'bio'         => 'nullable|string|max:5000',
            'linkedin'    => 'nullable|url|max:255',
            'twitter'     => 'nullable|string|max:255',
            'photo'       => 'nullable|image|max:2048',
            'attendee_id' => 'nullable|exists:attendees,id',
            'user_id'     => 'nullable|exists:users,id',
        ]);

        if ($request->hasFile('photo')) {
            if ($speaker->photo) {
                Storage::disk('public')->delete($speaker->photo);
            }
            $validated['photo'] = $request->file('photo')->store('speakers', 'public');
        }

        $speaker->update($validated);

        return redirect()->route('admin.speakers.index')
                         ->with('success', 'Speaker profile updated.');
    }

    public function destroy(Speaker $speaker)
    {
        if ($speaker->photo) {
            Storage::disk('public')->delete($speaker->photo);
        }

        $speaker->delete();

        return redirect()->route('admin.speakers.index')
                         ->with('success', 'Speaker removed.');
    }

    /** AJAX: return all speakers as JSON (for session builder dropdowns). */
    public function list()
    {
        $speakers = Speaker::with('attendee')
            ->orderBy('name')
            ->get()
            ->map(fn($s) => [
                'id'      => $s->id,
                'name'    => $s->name,
                'title'   => $s->title,
                'display' => $s->display_name,
                'photo'   => $s->photo ? asset('storage/' . $s->photo) : null,
            ]);

        return response()->json($speakers);
    }
}
