@extends('layouts.admin')
@section('title', 'My Sessions')
@section('page-title', 'My Sessions')

@section('content')
@php
$typeColors = ['talk'=>'bg-blue-100 text-blue-700','workshop'=>'bg-purple-100 text-purple-700','panel'=>'bg-amber-100 text-amber-700','break'=>'bg-gray-100 text-gray-500'];
@endphp

@if($sessions->isEmpty())
<div class="bg-white rounded-xl border border-gray-200 p-16 text-center">
    <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
    </svg>
    <p class="text-gray-400 text-sm">No sessions assigned to you yet.</p>
</div>
@else
<div class="space-y-4">
@foreach($sessions as $session)
@php $isLive = $session->isLive(); @endphp
<div class="bg-white rounded-xl border {{ $isLive ? 'border-green-400 ring-1 ring-green-300' : 'border-gray-200' }} overflow-hidden">
    <div class="p-5 flex items-start gap-4">
        <div class="flex-shrink-0 text-right w-14">
            <p class="text-sm font-semibold {{ $isLive ? 'text-green-700' : 'text-gray-700' }}">
                {{ $session->starts_at->format('H:i') }}
            </p>
            <p class="text-xs text-gray-400">{{ $session->ends_at->format('H:i') }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $session->starts_at->format('d M') }}</p>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1 flex-wrap">
                @if($isLive)
                    <span class="flex items-center gap-1 text-xs font-medium text-green-700">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>Live now
                    </span>
                @endif
                <span class="text-xs px-1.5 py-0.5 rounded capitalize font-medium {{ $typeColors[$session->type] ?? '' }}">{{ $session->type }}</span>
                @if($session->room)<span class="text-xs text-gray-400">{{ $session->room }}</span>@endif
            </div>
            <h3 class="text-base font-semibold text-gray-900">{{ $session->title }}</h3>
            @if($session->description)
                <p class="text-sm text-gray-500 mt-1">{{ $session->description }}</p>
            @endif
        </div>
        <div class="flex-shrink-0 text-right">
            @if($session->feedback_count > 0)
                <p class="text-lg font-bold text-amber-500">{{ $session->feedback_avg_rating ? number_format($session->feedback_avg_rating, 1) : '—' }}</p>
                <p class="text-xs text-gray-400">★ avg</p>
                <p class="text-xs text-gray-400">{{ $session->feedback_count }} reviews</p>
            @else
                <p class="text-xs text-gray-400">No feedback yet</p>
            @endif
        </div>
    </div>
    <div class="px-5 pb-4">
        <a href="{{ route('speaker.sessions.show', [$event, $session]) }}"
           class="inline-flex items-center gap-1 text-xs px-3 py-1.5 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 transition-colors">
            View feedback →
        </a>
    </div>
</div>
@endforeach
</div>
@endif
@endsection
