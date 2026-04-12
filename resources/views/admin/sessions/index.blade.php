@extends('layouts.admin')
@section('title', 'Sessions — ' . $event->name)
@section('page-title', 'Programme Sessions')

@push('header-actions')
<a href="{{ route('admin.events.sessions.create', $event) }}"
   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Add Session
</a>
@endpush

@section('content')
@php
$typeColors = ['talk'=>'bg-blue-100 text-blue-700','workshop'=>'bg-purple-100 text-purple-700','panel'=>'bg-amber-100 text-amber-700','break'=>'bg-gray-100 text-gray-500'];
@endphp

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    @if($sessions->isEmpty())
    <div class="text-center py-20">
        <p class="text-gray-400 text-sm">No sessions yet. Add your programme.</p>
    </div>
    @else
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100">
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Session</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Time</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Room</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Feedback</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($sessions as $session)
            @php $live = $session->isLive(); @endphp
            <tr class="hover:bg-gray-50 transition-colors {{ $live ? 'bg-green-50' : '' }}">
                <td class="px-5 py-3.5">
                    <div class="flex items-center gap-2">
                        @if($live)<span class="w-2 h-2 bg-green-500 rounded-full animate-pulse flex-shrink-0"></span>@endif
                        <div>
                            <p class="font-medium text-gray-900">{{ $session->title }}</p>
                            @if($session->description)
                            <p class="text-xs text-gray-400 truncate max-w-xs">{{ $session->description }}</p>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3.5 text-gray-600 hidden md:table-cell whitespace-nowrap">
                    {{ $session->starts_at->format('H:i') }} – {{ $session->ends_at->format('H:i') }}
                </td>
                <td class="px-5 py-3.5 text-gray-500 hidden lg:table-cell">{{ $session->room ?: '—' }}</td>
                <td class="px-5 py-3.5">
                    <span class="text-xs px-2 py-0.5 rounded font-medium capitalize {{ $typeColors[$session->type] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ $session->type }}
                    </span>
                </td>
                <td class="px-5 py-3.5 text-right hidden lg:table-cell">
                    @if($session->feedback_count > 0)
                    <span class="text-gray-700">{{ $session->feedback_count }}</span>
                    <span class="text-gray-400 text-xs ml-1">· ★ {{ round($session->feedback_avg_rating ?? 0, 1) }}</span>
                    @else
                    <span class="text-gray-400">—</span>
                    @endif
                </td>
                <td class="px-5 py-3.5">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.events.sessions.edit', [$event, $session]) }}"
                           class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors">Edit</a>
                        <form method="POST" action="{{ route('admin.events.sessions.destroy', [$event, $session]) }}"
                              onsubmit="return confirm('Delete this session?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs px-3 py-1.5 border border-red-200 rounded-lg text-red-600 hover:bg-red-50 transition-colors">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
