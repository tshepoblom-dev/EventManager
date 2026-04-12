@extends('layouts.admin')
@section('title', $event->name)
@section('page-title', $event->name)

@push('header-actions')
    <a href="{{ route('admin.events.edit', $event) }}"
       class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-200 text-sm text-gray-600 rounded-lg hover:bg-gray-50 transition-colors">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        Edit
    </a>
@endpush

@section('content')
@php
    $statusColors = ['draft'=>'bg-gray-100 text-gray-600','published'=>'bg-blue-100 text-blue-700','live'=>'bg-green-100 text-green-700','closed'=>'bg-red-100 text-red-600'];
@endphp

{{-- Event header card --}}
<div class="bg-white rounded-xl border border-gray-200 p-6 mb-6 flex items-start gap-5">
    @if($event->logo)
        <img src="{{ Storage::url($event->logo) }}" alt="Logo" class="w-16 h-16 object-contain rounded-lg border border-gray-100 flex-shrink-0">
    @else
        <div class="w-16 h-16 rounded-lg bg-blue-600 flex items-center justify-center flex-shrink-0">
            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
    @endif
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-3 flex-wrap mb-1">
            <h2 class="text-lg font-semibold text-gray-900">{{ $event->name }}</h2>
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium {{ $statusColors[$event->status] ?? '' }}">
                @if($event->status === 'live')<span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1 animate-pulse"></span>@endif
                {{ ucfirst($event->status) }}
            </span>
        </div>
        <p class="text-sm text-gray-500">
            {{ $event->event_date->format('l, d F Y') }}
            &nbsp;·&nbsp;
            {{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($event->end_time)->format('H:i') }}
            @if($event->venue)&nbsp;·&nbsp;{{ $event->venue }}@endif
        </p>
        @if($event->description)
            <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ $event->description }}</p>
        @endif
    </div>
</div>

{{-- Stats row --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @php
        $tiles = [
            ['label'=>'Attendees',  'value'=>number_format($event->attendees_count),  'color'=>'text-blue-700'],
            ['label'=>'Checked In', 'value'=>number_format($event->check_ins_count),  'color'=>'text-green-700'],
            ['label'=>'Sessions',   'value'=>number_format($event->sessions_count),   'color'=>'text-purple-700'],
            ['label'=>'Sponsors',   'value'=>number_format($event->sponsors_count),   'color'=>'text-amber-700'],
        ];
    @endphp
    @foreach($tiles as $tile)
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">{{ $tile['label'] }}</p>
        <p class="text-3xl font-bold {{ $tile['color'] }} tabular-nums">{{ $tile['value'] }}</p>
    </div>
    @endforeach
</div>

{{-- Action cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    <a href="{{ route('admin.events.attendees.index', $event) }}"
       class="bg-white rounded-xl border border-gray-200 p-5 hover:border-blue-300 hover:shadow-sm transition-all group">
        <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center mb-3 group-hover:bg-blue-100 transition-colors">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <p class="text-sm font-semibold text-gray-900">Attendees</p>
        <p class="text-xs text-gray-500 mt-0.5">Manage, import, send QR codes</p>
    </a>

    <a href="{{ route('staff.checkin.index', $event) }}"
       class="bg-white rounded-xl border border-gray-200 p-5 hover:border-green-300 hover:shadow-sm transition-all group">
        <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center mb-3 group-hover:bg-green-100 transition-colors">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <p class="text-sm font-semibold text-gray-900">Check-in Scanner</p>
        <p class="text-xs text-gray-500 mt-0.5">QR scan &amp; manual check-in</p>
    </a>

    <a href="{{ route('admin.events.attendees.import', $event) }}"
       class="bg-white rounded-xl border border-gray-200 p-5 hover:border-gray-300 hover:shadow-sm transition-all group">
        <div class="w-9 h-9 rounded-lg bg-gray-50 flex items-center justify-center mb-3 group-hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
            </svg>
        </div>
        <p class="text-sm font-semibold text-gray-900">Import CSV</p>
        <p class="text-xs text-gray-500 mt-0.5">Bulk upload attendees</p>
    </a>
</div>

{{-- Recent attendees --}}
@if($recentAttendees->isNotEmpty())
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-900">Recently Added Attendees</h3>
        <a href="{{ route('admin.events.attendees.index', $event) }}" class="text-xs text-blue-600 hover:underline">View all</a>
    </div>
    <ul class="divide-y divide-gray-50">
        @foreach($recentAttendees as $attendee)
        <li class="flex items-center gap-3 px-5 py-3">
            <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-xs font-semibold text-gray-600 flex-shrink-0">
                {{ strtoupper(substr($attendee->first_name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-900 font-medium truncate">{{ $attendee->full_name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ $attendee->email }}</p>
            </div>
            <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-600 capitalize">{{ $attendee->ticket_type }}</span>
        </li>
        @endforeach
    </ul>
</div>
@endif
@endsection
