@extends('layouts.admin')
@section('title', $session->title . ' — Feedback')
@section('page-title', 'Session Feedback')

@push('header-actions')
<a href="{{ route('speaker.sessions.index', $event) }}"
   class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-200 text-sm text-gray-600 rounded-lg hover:bg-gray-50">
    ← My Sessions
</a>
@endpush

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

{{-- Stats --}}
<div class="space-y-4">
    <div class="bg-white rounded-xl border border-gray-200 p-6 text-center">
        <p class="text-5xl font-bold text-amber-500 tabular-nums" id="avg-rating">
            {{ $stats['average'] > 0 ? number_format($stats['average'], 1) : '—' }}
        </p>
        <div class="flex justify-center gap-0.5 mt-2 text-xl" id="star-display">
            @for($i = 1; $i <= 5; $i++)
                <span class="{{ $i <= round($stats['average']) ? 'text-amber-400' : 'text-gray-200' }}">★</span>
            @endfor
        </div>
        <p class="text-sm text-gray-500 mt-2" id="feedback-count">{{ $stats['count'] }} response{{ $stats['count'] !== 1 ? 's' : '' }}</p>
    </div>

    {{-- Rating breakdown --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">Breakdown</h3>
        @for($i = 5; $i >= 1; $i--)
        @php $count = $stats['by_rating'][$i] ?? 0; $pct = $stats['count'] > 0 ? round($count / $stats['count'] * 100) : 0; @endphp
        <div class="flex items-center gap-2 mb-2">
            <span class="text-xs text-amber-500 w-4 text-right">{{ $i }}★</span>
            <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                <div class="bg-amber-400 h-2 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
            </div>
            <span class="text-xs text-gray-500 w-6 text-right tabular-nums">{{ $count }}</span>
        </div>
        @endfor
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Session</p>
        <p class="text-sm font-semibold text-gray-900">{{ $session->title }}</p>
        <p class="text-xs text-gray-400 mt-1">{{ $session->starts_at->format('d M Y · H:i') }} – {{ $session->ends_at->format('H:i') }}</p>
        @if($session->room)<p class="text-xs text-gray-400">{{ $session->room }}</p>@endif
    </div>
</div>

{{-- Comments feed --}}
<div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-900">Comments</h2>
        <span class="flex items-center gap-1.5 text-xs text-green-600 font-medium">
            <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>Live
        </span>
    </div>
    <ul id="feedback-list" class="divide-y divide-gray-50 max-h-[600px] overflow-y-auto">
        @forelse($session->feedback->whereNotNull('comment') as $fb)
        <li class="px-5 py-4">
            <div class="flex items-start gap-3">
                <div class="text-amber-400 text-sm flex-shrink-0">
                    {{ str_repeat('★', $fb->rating ?? 0) }}<span class="text-gray-200">{{ str_repeat('★', 5 - ($fb->rating ?? 0)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-700">{{ $fb->comment }}</p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ $fb->attendee?->full_name ?? 'Anonymous' }} · {{ $fb->created_at->format('H:i') }}
                    </p>
                </div>
            </div>
        </li>
        @empty
        <li class="text-center py-12 text-sm text-gray-400" id="empty-feedback">No comments yet.</li>
        @endforelse
    </ul>
</div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (!window.Echo) return;
    // Listen on the programme channel for new feedback (future enhancement)
    window.Echo.channel('event.{{ $event->id }}.programme')
        .listen('.session.highlighted', () => {});
});
</script>
@endpush
