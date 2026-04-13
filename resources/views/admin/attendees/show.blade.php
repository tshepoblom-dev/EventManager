@extends('layouts.admin')
@section('title', $attendee->full_name)
@section('page-title', $attendee->full_name)

@push('header-actions')
    <a href="{{ route('admin.events.attendees.index', $event) }}"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-200 text-sm text-gray-600 rounded-lg hover:bg-gray-50 transition-colors">
        ← Back to attendees
    </a>
@endpush

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: profile + QR --}}
    <div class="space-y-4">
        {{-- Profile card --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-4 mb-5">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-lg font-bold text-blue-700 flex-shrink-0">
                    {{ strtoupper(substr($attendee->first_name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">{{ $attendee->full_name }}</h2>
                    <p class="text-xs text-gray-500">{{ $attendee->email }}</p>
                </div>
            </div>

            <dl class="space-y-2.5 text-sm">
                @if($attendee->phone)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Phone</dt>
                    <dd class="text-gray-900 font-medium">{{ $attendee->phone }}</dd>
                </div>
                @endif
                @if($attendee->company)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Company</dt>
                    <dd class="text-gray-900 font-medium">{{ $attendee->company }}</dd>
                </div>
                @endif
                @if($attendee->job_title)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Role</dt>
                    <dd class="text-gray-900 font-medium">{{ $attendee->job_title }}</dd>
                </div>
                @endif
                <div class="flex justify-between">
                    <dt class="text-gray-500">Ticket</dt>
                    <dd><span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-700 capitalize">{{ $attendee->ticket_type }}</span></dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Source</dt>
                    <dd class="text-gray-700 capitalize">{{ $attendee->source }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Registered</dt>
                    <dd class="text-gray-700">{{ $attendee->created_at->format('d M Y') }}</dd>
                </div>
            </dl>
        </div>

        {{-- QR card --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">QR Code</p>
            @if($attendee->qr_image_path)
                <div class="flex justify-center mb-4">
                    <img src="{{ Storage::disk('public')->url($attendee->qr_image_path) }}"
                         alt="QR Code" class="w-36 h-36 border border-gray-200 rounded-lg p-1">
                </div>
                <p class="text-xs text-gray-400 text-center break-all font-mono mb-3">{{ substr($attendee->qr_code, 0, 18) }}…</p>
                <form method="POST" action="{{ route('admin.events.attendees.send-qr', [$event, $attendee]) }}">
                    @csrf
                    <button type="submit"
                            class="w-full px-4 py-2 border border-blue-200 text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-50 transition-colors">
                        {{ $attendee->qr_emailed ? 'Resend QR Email' : 'Send QR Email' }}
                    </button>
                </form>
            @else
                <div class="flex justify-center mb-3">
                    <div class="w-36 h-36 border border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                        <p class="text-xs text-gray-400 text-center px-2">QR generation<br>queued…</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Right: check-in status + actions --}}
    <div class="lg:col-span-2 space-y-4">
        {{-- Check-in status --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Check-in Status</p>

            @if($attendee->checkIn)
                <div class="flex items-center gap-4 p-4 bg-green-50 rounded-xl border border-green-200">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-green-800">Checked In</p>
                        <p class="text-xs text-green-600 mt-0.5">
                            {{ $attendee->checkIn->checked_in_at->format('d M Y \a\t H:i') }}
                            · {{ ucfirst($attendee->checkIn->method) }}
                            @if($attendee->checkIn->station)
                                · Station: {{ $attendee->checkIn->station }}
                            @endif
                        </p>
                        @if($attendee->checkIn->checkedInBy)
                            <p class="text-xs text-green-600">By: {{ $attendee->checkIn->checkedInBy->name }}</p>
                        @endif
                    </div>
                </div>
            @else
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-sm text-gray-500">Not yet checked in.</p>
                </div>
            @endif
        </div>


        {{-- Account / Invite --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-4">Platform Account</p>

            @if($attendee->user)
                <div class="flex items-center gap-3 p-3 bg-green-50 border border-green-200 rounded-lg mb-3">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-green-800">Account active</p>
                        <p class="text-xs text-green-700 truncate">{{ $attendee->user->name }} · {{ $attendee->user->role?->display_name }}</p>
                    </div>
                </div>
                <a href="{{ route('admin.users.edit', $attendee->user) }}"
                   class="block w-full text-center px-3 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">
                    Edit user account
                </a>
            @elseif($attendee->hasPendingInvite())
                <div class="flex items-center gap-3 p-3 bg-amber-50 border border-amber-200 rounded-lg mb-3">
                    <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-amber-800">Invite sent</p>
                        <p class="text-xs text-amber-700">{{ $attendee->invite_sent_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.attendees.invite', $attendee) }}">
                    @csrf
                    <button type="submit"
                        class="w-full px-3 py-2 text-sm text-amber-700 border border-amber-300 rounded-lg hover:bg-amber-50">
                        Resend invite
                    </button>
                </form>
            @else
                <p class="text-sm text-gray-500 mb-3">This attendee does not have a platform account yet.</p>
                <form method="POST" action="{{ route('admin.attendees.invite', $attendee) }}">
                    @csrf
                    <button type="submit"
                        class="w-full px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        Send account invite
                    </button>
                </form>
            @endif
        </div>

        {{-- Danger zone --}}
        <div class="bg-white rounded-xl border border-red-200 p-6">
            <p class="text-xs font-semibold text-red-500 uppercase tracking-wide mb-3">Danger Zone</p>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-900">Remove attendee</p>
                    <p class="text-xs text-gray-500 mt-0.5">Deletes their check-in and QR code.</p>
                </div>
                <form method="POST" action="{{ route('admin.events.attendees.destroy', [$event, $attendee]) }}"
                      onsubmit="return confirm('Remove {{ addslashes($attendee->full_name) }}?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                        Remove
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
