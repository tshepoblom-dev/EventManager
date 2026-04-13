<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Attendee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Standard registration — users choose from attendee, speaker, sponsor.
     * Admin and staff roles are never available here.
     */
    public function create(): View
    {
        $roles = Role::whereNotIn('name', ['admin', 'staff'])
            ->orderBy('display_name')
            ->get();

        return view('auth.register', compact('roles'));
    }

    /**
     * Invite-based registration: pre-filled from the attendee record tied
     * to the token. Admin or staff must send this link first.
     */
    public function createFromInvite(string $token): View|RedirectResponse
    {
        $attendee = Attendee::where('invite_token', $token)->first();

        if (! $attendee) {
            return redirect()->route('login')
                ->with('error', 'This invite link is invalid or has already been used.');
        }

        if ($attendee->invite_sent_at && $attendee->invite_sent_at->addHours(72)->isPast()) {
            return redirect()->route('login')
                ->with('error', 'This invite link has expired. Ask an admin to resend it.');
        }

        $roles = Role::whereNotIn('name', ['admin', 'staff'])
            ->orderBy('display_name')
            ->get();

        return view('auth.register-invited', compact('attendee', 'token', 'roles'));
    }

    /** Handle standard registration. */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'role_id'  => ['required', 'exists:roles,id'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $role = Role::findOrFail($request->role_id);
        if (in_array($role->name, ['admin', 'staff'])) {
            return back()->withErrors(['role_id' => 'That role cannot be self-assigned.']);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'role_id'  => $request->role_id,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('dashboard');
    }

    /** Handle invite-based registration. */
    public function storeFromInvite(Request $request, string $token): RedirectResponse
    {
        $attendee = Attendee::where('invite_token', $token)->first();

        if (! $attendee) {
            return redirect()->route('login')
                ->with('error', 'Invalid or already used invite link.');
        }

        if ($attendee->invite_sent_at && $attendee->invite_sent_at->addHours(72)->isPast()) {
            return redirect()->route('login')
                ->with('error', 'Invite link expired. Please request a new one.');
        }

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'role_id'  => ['required', 'exists:roles,id'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $role = Role::findOrFail($request->role_id);
        if (in_array($role->name, ['admin', 'staff'])) {
            return back()->withErrors(['role_id' => 'That role cannot be self-assigned.']);
        }

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'role_id'  => $request->role_id,
            'password' => Hash::make($request->password),
        ]);

        // Link the new user back to the attendee and clear the one-time token
        $attendee->update(['user_id' => $user->id]);
        $attendee->clearInviteToken();

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Welcome! Your account is linked to your attendee profile.');
    }
}
