@extends('layouts.admin')
@section('title', 'Events')
@section('page-title', 'Events')

@push('header-actions')
    <a href="{{ route('admin.events.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        New Event
    </a>
@endpush

@section('content')
@php
    $statusColors = [
        'draft'     => 'bg-gray-100 text-gray-600',
        'published' => 'bg-blue-100 text-blue-700',
        'live'      => 'bg-green-100 text-green-700',
        'closed'    => 'bg-red-100 text-red-600',
    ];
@endphp

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    @if($events->isEmpty())
        <div class="text-center py-24">
            <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-gray-500 text-sm">No events yet.</p>
        </div>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Event</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden sm:table-cell">Date</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Attendees</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Checked In</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($events as $event)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-4">
                        <p class="font-medium text-gray-900">{{ $event->name }}</p>
                        @if($event->venue)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $event->venue }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-gray-600 hidden sm:table-cell">
                        {{ $event->event_date->format('d M Y') }}
                    </td>
                    <td class="px-5 py-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium {{ $statusColors[$event->status] ?? 'bg-gray-100 text-gray-600' }}">
                            @if($event->status === 'live')
                                <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5 animate-pulse"></span>
                            @endif
                            {{ ucfirst($event->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-right tabular-nums text-gray-700 hidden md:table-cell">{{ number_format($event->attendees_count) }}</td>
                    <td class="px-5 py-4 text-right tabular-nums text-gray-700 hidden md:table-cell">{{ number_format($event->check_ins_count) }}</td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.events.show', $event) }}"
                               class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors">
                                View
                            </a>
                            <a href="{{ route('admin.events.edit', $event) }}"
                               class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors">
                                Edit
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($events->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $events->links() }}
            </div>
        @endif
    @endif
</div>
@endsection
