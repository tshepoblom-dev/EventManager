@extends('layouts.admin')
@section('title', 'Attendees — ' . $event->name)
@section('page-title', 'Attendees')

@push('header-actions')
    <form method="POST" action="{{ route('admin.attendees.invite-bulk') }}" id="bulk-invite-form">
        @csrf
        <div id="bulk-invite-ids"></div>
        <button type="submit" id="bulk-invite-btn"
            class="hidden inline-flex items-center gap-2 px-4 py-2 border border-blue-300 text-blue-700 text-sm font-medium rounded-lg hover:bg-blue-50 transition-colors">
            Send invites to selected
        </button>
    </form>
    <a href="{{ route('admin.events.attendees.import', $event) }}"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-200 text-sm text-gray-600 rounded-lg hover:bg-gray-50 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
        </svg>
        Import CSV
    </a>
    <a href="{{ route('admin.events.attendees.create', $event) }}"
       class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Attendee
    </a>
@endpush

@section('content')

{{-- Stats strip --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ number_format($stats['total']) }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Registered</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-2xl font-bold text-green-700 tabular-nums">{{ number_format($stats['checked_in']) }}</p>
        <p class="text-xs text-gray-500 mt-0.5">Checked In</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-2xl font-bold text-blue-700 tabular-nums">{{ number_format($stats['qr_sent']) }}</p>
        <p class="text-xs text-gray-500 mt-0.5">QR Emailed</p>
    </div>
</div>

{{-- Filter bar --}}
<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-4 flex flex-wrap gap-3 items-end">
    <div class="w-full sm:flex-1 sm:min-w-48">
        <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Name, email, company…"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Ticket type</label>
        <select name="ticket_type" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All</option>
            @foreach(['general','vip','speaker','sponsor'] as $t)
                <option value="{{ $t }}" {{ request('ticket_type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Check-in</label>
        <select name="checked_in" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All</option>
            <option value="yes" {{ request('checked_in') === 'yes' ? 'selected' : '' }}>Checked in</option>
            <option value="no"  {{ request('checked_in') === 'no'  ? 'selected' : '' }}>Not checked in</option>
        </select>
    </div>
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
        Filter
    </button>
    @if(request()->hasAny(['search','ticket_type','checked_in']))
        <a href="{{ route('admin.events.attendees.index', $event) }}" class="px-4 py-2 border border-gray-200 text-sm text-gray-600 rounded-lg hover:bg-gray-50 transition-colors">
            Clear
        </a>
    @endif
</form>

{{-- Bulk QR send --}}
@if($stats['total'] > $stats['qr_sent'])
<form method="POST" action="{{ route('admin.events.attendees.send-qr-bulk', $event) }}" class="mb-4">
    @csrf
    <button type="submit"
            onclick="return confirm('Send QR codes to all attendees who haven\'t received one yet?')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
        Send QR to unsent ({{ $stats['total'] - $stats['qr_sent'] }})
    </button>
</form>
@endif

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    @if($attendees->isEmpty())
        <div class="text-center py-16">
            <p class="text-gray-400 text-sm">No attendees match your filters.</p>
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Company</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Ticket</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">QR</th>
                    <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($attendees as $attendee)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3.5">
                        <p class="font-medium text-gray-900">{{ $attendee->full_name }}</p>
                        <p class="text-xs text-gray-400">{{ $attendee->email }}</p>
                    </td>
                    <td class="px-5 py-3.5 text-gray-600 hidden md:table-cell">{{ $attendee->company ?: '—' }}</td>
                    <td class="px-5 py-3.5 hidden lg:table-cell">
                        <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-600 capitalize">{{ $attendee->ticket_type }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        @if($attendee->qr_emailed)
                            <span class="inline-flex items-center gap-1 text-xs text-green-600">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                Sent
                            </span>
                        @elseif($attendee->qr_code)
                            <form method="POST" action="{{ route('admin.events.attendees.send-qr', [$event, $attendee]) }}">
                                @csrf
                                <button type="submit" class="text-xs text-blue-600 hover:underline">Send</button>
                            </form>
                        @else
                            <span class="text-xs text-gray-400">Pending</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        @if($attendee->check_in_exists)
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded-full">
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                Checked In
                            </span>
                        @else
                            <span class="text-xs text-gray-400">Not yet</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        <a href="{{ route('admin.events.attendees.show', [$event, $attendee]) }}"
                           class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors">
                            View
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($attendees->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $attendees->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
