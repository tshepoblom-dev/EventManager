@extends('layouts.admin')
@section('title', 'Speakers')
@section('page-title', 'Speakers' . ($currentEvent ? ' — ' . $currentEvent->name : ''))

@php $event = $currentEvent ?? null; @endphp

@push('header-actions')
<a href="{{ route('admin.speakers.create', $currentEvent ? ['event_id' => $currentEvent->id] : []) }}"
   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Add Speaker
</a>
@endpush

@section('content')
{{-- Search --}}
<form method="GET" class="flex gap-3 mb-5">
    @if($currentEvent)
    <input type="hidden" name="event_id" value="{{ $currentEvent->id }}">
    @endif
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email or title…"
        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Search</button>
    @if(request('search'))
    <a href="{{ route('admin.speakers.index') }}" class="px-4 py-2 text-gray-500 text-sm hover:text-gray-700">Clear</a>
    @endif
</form>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @forelse($speakers as $speaker)
    <div class="bg-white rounded-xl border border-gray-200 p-5 flex flex-col gap-3">
        <div class="flex items-start gap-3">
            @if($speaker->photo)
            <img src="{{ asset('storage/' . $speaker->photo) }}" alt="{{ $speaker->name }}"
                 class="w-12 h-12 rounded-full object-cover flex-shrink-0">
            @else
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white font-semibold text-lg flex-shrink-0">
                {{ strtoupper(substr($speaker->name, 0, 1)) }}
            </div>
            @endif
            <div class="min-w-0">
                <p class="font-semibold text-gray-900 text-sm truncate">{{ $speaker->name }}</p>
                @if($speaker->title)
                <p class="text-xs text-gray-500 truncate">{{ $speaker->title }}</p>
                @endif
                @if($speaker->email)
                <p class="text-xs text-gray-400 truncate">{{ $speaker->email }}</p>
                @endif
            </div>
        </div>

        {{-- Links badges --}}
        <div class="flex flex-wrap gap-1.5">
            @if($speaker->event)
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                {{ $speaker->event->name }}
            </span>
            @endif
            @if($speaker->attendee)
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Attendee
            </span>
            @endif
            @if($speaker->user)
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Has Account
            </span>
            @endif
            @if($speaker->linkedin)
            <a href="{{ $speaker->linkedin }}" target="_blank"
               class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200">
                LinkedIn ↗
            </a>
            @endif
        </div>

        <div class="flex items-center gap-2 pt-1 border-t border-gray-100">
            <a href="{{ route('admin.speakers.edit', $speaker) }}"
               class="flex-1 text-center px-3 py-1.5 text-xs font-medium text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50">
                Edit
            </a>
            <form method="POST" action="{{ route('admin.speakers.destroy', $speaker) }}"
                  onsubmit="return confirm('Remove {{ addslashes($speaker->name) }} as a speaker?')">
                @csrf @method('DELETE')
                <button type="submit"
                    class="px-3 py-1.5 text-xs font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50">
                    Remove
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="col-span-3 py-16 text-center text-gray-400">
        <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <p class="text-sm">No speakers yet. <a href="{{ route('admin.speakers.create') }}" class="text-blue-600 hover:underline">Add one →</a></p>
    </div>
    @endforelse
</div>

@if($speakers->hasPages())
<div class="mt-6">{{ $speakers->links() }}</div>
@endif
@endsection
