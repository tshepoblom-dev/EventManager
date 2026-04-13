<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AttendeeInviteMail;
use App\Models\Attendee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /** List all users with their roles. */
    public function index(Request $request)
    {
        $users = User::with('role')
            ->when($request->search, fn($q, $s) =>
                $q->where(fn($q2) =>
                    $q2->where('name', 'like', "%{$s}%")
                       ->orWhere('email', 'like', "%{$s}%")
                )
            )
            ->when($request->role, fn($q, $r) =>
                $q->whereHas('role', fn($q2) => $q2->where('name', $r))
            )
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $roles = Role::orderBy('display_name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /** Show the create-user form. */
    public function create()
    {
        $roles = Role::orderBy('display_name')->get();
        return view('admin.users.create', compact('roles'));
    }

    /** Manually create a user account (not invite-based). */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'role_id'  => 'required|exists:roles,id',
            'password' => 'required|min:8|confirmed',
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'role_id'  => $validated['role_id'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.users.index')
                         ->with('success', 'User created successfully.');
    }

    /** Edit a user. */
    public function edit(User $user)
    {
        $roles = Role::orderBy('display_name')->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /** Update a user's name, email, and role. */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role_id' => 'required|exists:roles,id',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.index')
                         ->with('success', 'User updated.');
    }

    /** Delete a user account. */
    public function destroy(User $user)
    {
        // Detach from attendee records
        $user->attendees()->update(['user_id' => null]);
        $user->delete();

        return redirect()->route('admin.users.index')
                         ->with('success', 'User deleted.');
    }

    // ── Invite Flow ────────────────────────────────────────────────────────

    /**
     * Send an invite email to a specific attendee so they can create a user account.
     * Called from the attendee show/index page.
     */
    public function inviteAttendee(Request $request, Attendee $attendee)
    {
        if ($attendee->hasAccount()) {
            return back()->with('error', "{$attendee->full_name} already has an account.");
        }

        $token     = $attendee->generateInviteToken();
        $inviteUrl = route('register.invited', ['token' => $token]);

        Mail::to($attendee->email)->queue(new AttendeeInviteMail($attendee, $inviteUrl));

        return back()->with('success', "Invite sent to {$attendee->email}.");
    }

    /**
     * Bulk-invite multiple attendees at once (called from the attendees list).
     */
    public function inviteAttendeeBulk(Request $request)
    {
        $request->validate([
            'attendee_ids'   => 'required|array',
            'attendee_ids.*' => 'integer|exists:attendees,id',
        ]);

        $sent = 0;
        $skipped = 0;

        Attendee::whereIn('id', $request->attendee_ids)->each(function (Attendee $attendee) use (&$sent, &$skipped) {
            if ($attendee->hasAccount()) {
                $skipped++;
                return;
            }

            $token     = $attendee->generateInviteToken();
            $inviteUrl = route('register.invited', ['token' => $token]);
            Mail::to($attendee->email)->queue(new AttendeeInviteMail($attendee, $inviteUrl));
            $sent++;
        });

        $message = "Invites sent: {$sent}.";
        if ($skipped) {
            $message .= " Skipped {$skipped} (already have accounts).";
        }

        return back()->with('success', $message);
    }
}
