<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SponsorController extends Controller
{
    public function index(Event $event)
    {
        $sponsors = $event->sponsors()->withCount('leads')->get();
        return view('admin.sponsors.index', compact('event', 'sponsors'));
    }

    public function create(Event $event)
    {
        $users = User::whereHas('role', fn($q) => $q->where('name', 'sponsor'))->get();
        return view('admin.sponsors.create', compact('event', 'users'));
    }

    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'tier'         => 'required|in:platinum,gold,silver,bronze',
            'website'      => 'nullable|url|max:255',
            'booth_number' => 'nullable|string|max:20',
            'description'  => 'nullable|string',
            'user_id'      => 'nullable|exists:users,id',
            'logo'         => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('sponsors/logos', 'public');
        }

        $event->sponsors()->create($validated);

        return redirect()->route('admin.events.sponsors.index', $event)
            ->with('success', "{$validated['company_name']} added as sponsor.");
    }

    public function edit(Event $event, Sponsor $sponsor)
    {
        $users = User::whereHas('role', fn($q) => $q->where('name', 'sponsor'))->get();
        return view('admin.sponsors.edit', compact('event', 'sponsor', 'users'));
    }

    public function update(Request $request, Event $event, Sponsor $sponsor)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'tier'         => 'required|in:platinum,gold,silver,bronze',
            'website'      => 'nullable|url|max:255',
            'booth_number' => 'nullable|string|max:20',
            'description'  => 'nullable|string',
            'user_id'      => 'nullable|exists:users,id',
            'logo'         => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($sponsor->logo) Storage::disk('public')->delete($sponsor->logo);
            $validated['logo'] = $request->file('logo')->store('sponsors/logos', 'public');
        }

        $sponsor->update($validated);

        return redirect()->route('admin.events.sponsors.index', $event)
            ->with('success', 'Sponsor updated.');
    }

    public function destroy(Event $event, Sponsor $sponsor)
    {
        if ($sponsor->logo) Storage::disk('public')->delete($sponsor->logo);
        $sponsor->delete();
        return redirect()->route('admin.events.sponsors.index', $event)
            ->with('success', 'Sponsor removed.');
    }
}
