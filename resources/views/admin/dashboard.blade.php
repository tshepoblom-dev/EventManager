@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Live Dashboard')

@push('header-actions')
    @if($event)
        <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full
            {{ $event->status === 'live' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
            @if($event->status === 'live')
                <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
            @endif
            {{ $event->name }}
        </span>
        @if(count($events) > 1)
        <select onchange="location = this.value"
                class="text-xs px-2 py-1.5 border border-gray-200 rounded-lg bg-white focus:outline-none">
            @foreach($events as $e)
            <option value="{{ route('dashboard') }}?event={{ $e->id }}" {{ $event->id===$e->id?'selected':'' }}>
                {{ $e->name }}
            </option>
            @endforeach
        </select>
        @endif
    @endif
@endpush

@section('content')

@if(!$event)
<div class="text-center py-24">
    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
    </svg>
    <p class="text-gray-500 text-sm mb-4">No published events yet.</p>
    <a href="{{ route('admin.events.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
        Create your first event
    </a>
</div>
@else

{{-- Phase 5: Live stats row --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Checked In</p>
        <p class="text-3xl font-bold text-gray-900 tabular-nums" id="stat-checked-in">{{ $stats['checked_in'] }}</p>
        <div class="mt-2 flex items-center gap-2">
            <div class="flex-1 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-blue-500 h-1.5 rounded-full transition-all duration-500"
                     id="stat-checkin-bar" style="width: {{ $stats['check_in_pct'] }}%"></div>
            </div>
            <span class="text-xs text-gray-500 tabular-nums" id="stat-checkin-pct">{{ $stats['check_in_pct'] }}%</span>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Registered</p>
        <p class="text-3xl font-bold text-gray-900 tabular-nums">{{ $stats['total_attendees'] }}</p>
        <p class="text-xs text-gray-400 mt-2">Total attendees</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Leads</p>
        <p class="text-3xl font-bold text-gray-900 tabular-nums" id="stat-leads">{{ $stats['total_leads'] }}</p>
        <p class="text-xs text-gray-400 mt-2">Captured today</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Live Now</p>
        @if($stats['active_session'])
            <p class="text-sm font-semibold text-green-700 leading-snug" id="live-session-title">{{ $stats['active_session']->title }}</p>
            <p class="text-xs text-gray-400 mt-1">
                {{ $stats['active_session']->starts_at->format('H:i') }} –
                {{ $stats['active_session']->ends_at->format('H:i') }}
            </p>
        @else
            <p class="text-sm text-gray-400" id="live-session-title">No active session</p>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Live check-in feed --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900">Recent Check-ins</h2>
            <span class="flex items-center gap-1.5 text-xs text-green-600 font-medium">
                <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>Live
            </span>
        </div>
        <ul id="checkin-feed" class="divide-y divide-gray-50">
            @forelse($stats['recent_checkins'] as $ci)
            <li class="flex items-center gap-3 px-5 py-3">
                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-xs font-semibold text-blue-700 flex-shrink-0">
                    {{ strtoupper(substr($ci->attendee->first_name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $ci->attendee->full_name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $ci->attendee->company }}</p>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-xs text-gray-500">{{ $ci->checked_in_at->format('H:i') }}</p>
                    <p class="text-xs text-gray-400 capitalize">{{ $ci->method }}</p>
                </div>
            </li>
            @empty
            <li class="px-5 py-8 text-center text-sm text-gray-400" id="empty-feed">No check-ins yet</li>
            @endforelse
        </ul>
    </div>

    {{-- Quick actions --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Quick Actions</h2>
            <div class="space-y-2">
                <a href="{{ route('staff.checkin.index', $event) }}"
                   class="flex items-center gap-3 p-3 rounded-lg border border-green-200 bg-green-50 text-green-800 hover:bg-green-100 transition-colors text-sm font-medium">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Open Check-in Scanner
                </a>
                <a href="{{ route('admin.events.attendees.index', $event) }}"
                   class="flex items-center gap-3 p-3 rounded-lg border border-blue-200 bg-blue-50 text-blue-800 hover:bg-blue-100 transition-colors text-sm font-medium">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Manage Attendees
                </a>
                <a href="{{ route('admin.events.leads.index', $event) }}"
                   class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors text-sm font-medium">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    View Lead Pipeline
                </a>
                <a href="{{ route('programme.index', $event) }}" target="_blank"
                   class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 text-gray-700 hover:bg-gray-50 transition-colors text-sm font-medium">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 10l4.553-2.069A1 1 0 0121 8.876V15.12a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
                    Live Programme ↗
                </a>
            </div>
        </div>
    </div>
</div>

@endif
@endsection

@push('scripts')
@if($event)
<script>
window.eventId = {{ $event->id }};

document.addEventListener('DOMContentLoaded', function () {
    if (!window.Echo) return;

    window.Echo.channel(`event.${window.eventId}.dashboard`)
        .listen('.attendee.checked_in', function (data) {
            // Update counters
            document.getElementById('stat-checked-in').textContent = data.total_checked_in;
            const pct = data.total_attendees > 0
                ? Math.round(data.total_checked_in / data.total_attendees * 1000) / 10
                : 0;
            document.getElementById('stat-checkin-pct').textContent = pct + '%';
            document.getElementById('stat-checkin-bar').style.width = pct + '%';

            // Prepend to feed
            const feed  = document.getElementById('checkin-feed');
            const empty = document.getElementById('empty-feed');
            if (empty) empty.remove();

            const time = new Date(data.checked_in_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const li   = document.createElement('li');
            li.className = 'flex items-center gap-3 px-5 py-3 bg-green-50';
            li.innerHTML = `
                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-xs font-semibold text-green-700 flex-shrink-0">
                    ${data.attendee.name.charAt(0).toUpperCase()}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">${data.attendee.name}</p>
                    <p class="text-xs text-gray-400 truncate">${data.attendee.company || ''}</p>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-xs text-gray-500">${time}</p>
                    <p class="text-xs text-gray-400 capitalize">${data.method}</p>
                </div>`;
            feed.insertBefore(li, feed.firstChild);
            setTimeout(() => li.classList.remove('bg-green-50'), 3000);
            while (feed.children.length > 10) feed.removeChild(feed.lastChild);
        })
        .listen('.lead.captured', function (data) {
            const el = document.getElementById('stat-leads');
            if (el) el.textContent = parseInt(el.textContent) + 1;

            // Toast
            const t = document.createElement('div');
            t.className = 'fixed bottom-6 right-6 bg-green-700 text-white text-sm px-4 py-3 rounded-xl shadow-lg z-50';
            t.textContent = `New lead: ${data.name} (${data.interest_level})`;
            document.body.appendChild(t);
            setTimeout(() => t.remove(), 5000);
        });

    window.Echo.channel(`event.${window.eventId}.programme`)
        .listen('.session.highlighted', function (data) {
            const el = document.getElementById('live-session-title');
            if (el) {
                el.textContent = data.title;
                el.className = 'text-sm font-semibold text-green-700 leading-snug';
            }
        });
});
</script>
@endif
@endpush
