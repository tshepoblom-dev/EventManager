<!DOCTYPE html>
<html lang="en" class="bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Programme — {{ $event->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">

{{-- Header --}}
<div class="bg-white border-b border-gray-200 sticky top-0 z-10">
    <div class="max-w-3xl mx-auto px-4 h-14 flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold text-gray-900">{{ $event->name }}</p>
            <p class="text-xs text-gray-400">{{ $event->event_date->format('d F Y') }}
                @if($event->venue)· {{ $event->venue }}@endif</p>
        </div>
        @if($currentSession)
        <span class="inline-flex items-center gap-1.5 text-xs font-medium bg-green-100 text-green-700 px-2.5 py-1 rounded-full">
            <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
            Live now
        </span>
        @endif
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 py-6 space-y-3" id="programme">

@php
$typeColors = ['talk'=>'bg-blue-100 text-blue-700','workshop'=>'bg-purple-100 text-purple-700','panel'=>'bg-amber-100 text-amber-700','break'=>'bg-gray-100 text-gray-500'];
$now = now();
@endphp

@foreach($sessions as $session)
@php
$isLive  = $session->starts_at <= $now && $session->ends_at >= $now;
$isPast  = $session->ends_at < $now;
$avgRating = round($session->feedback_avg_rating ?? 0, 1);
@endphp

<div id="session-{{ $session->id }}"
     class="bg-white rounded-xl border transition-all duration-500
            {{ $isLive ? 'border-green-400 shadow-md shadow-green-100 ring-1 ring-green-300' : 'border-gray-200' }}
            {{ $isPast ? 'opacity-60' : '' }}">

    <div class="p-5">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 text-right w-14">
                <p class="text-sm font-semibold {{ $isLive ? 'text-green-700' : 'text-gray-700' }}">
                    {{ $session->starts_at->format('H:i') }}
                </p>
                <p class="text-xs text-gray-400">{{ $session->ends_at->format('H:i') }}</p>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    @if($isLive)
                    <span class="flex items-center gap-1 text-xs font-medium text-green-700">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>Live
                    </span>
                    @endif
                    <span class="text-xs px-1.5 py-0.5 rounded capitalize font-medium {{ $typeColors[$session->type] ?? '' }}">{{ $session->type }}</span>
                    @if($session->room)
                    <span class="text-xs text-gray-400">{{ $session->room }}</span>
                    @endif
                </div>
                <h3 class="text-base font-semibold text-gray-900 leading-snug">{{ $session->title }}</h3>
                @if($session->description)
                <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $session->description }}</p>
                @endif
                @if($session->speakers->isNotEmpty())
                <div class="flex items-center gap-2 mt-2">
                    @foreach($session->speakers as $speaker)
                    <span class="text-xs text-gray-500">
                        <span class="font-medium text-gray-700">{{ $speaker->name }}</span>
                        @if($speaker->company)· {{ $speaker->company }}@endif
                    </span>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Rating badge --}}
            @if($session->feedback_count > 0)
            <div class="flex-shrink-0 text-right">
                <p class="text-sm font-semibold text-amber-600">{{ $avgRating > 0 ? '★ '.$avgRating : '' }}</p>
                <p class="text-xs text-gray-400">{{ $session->feedback_count }} ratings</p>
            </div>
            @endif
        </div>

        {{-- Inline feedback form (past or live sessions, non-break) --}}
        @if(auth()->check() && ($isLive || $isPast) && $session->type !== 'break')
        <div class="mt-4 pt-4 border-t border-gray-100"
             x-data="{
                 submitted: false,
                 rating: 0,
                 loading: false,
                 async submit() {
                     if (this.rating === 0 || this.loading) return;
                     this.loading = true;
                     const csrf = document.querySelector('meta[name=csrf-token]').content;
                     const comment = this.$refs.comment.value;
                     try {
                         const res = await fetch('{{ route('programme.feedback', [$event, $session]) }}', {
                             method: 'POST',
                             headers: {
                                 'Content-Type': 'application/json',
                                 'X-CSRF-TOKEN': csrf,
                                 'Accept': 'application/json',
                             },
                             body: JSON.stringify({ rating: this.rating, comment }),
                         });
                         if (res.ok || res.status === 409) {
                             this.submitted = true;
                         }
                     } catch (e) {
                         console.error(e);
                     } finally {
                         this.loading = false;
                     }
                 }
             }">
            <form @submit.prevent="submit" x-show="!submitted">
                <p class="text-xs font-medium text-gray-600 mb-2">Rate this session</p>
                <div class="flex items-center gap-1 mb-3">
                    @for($i = 1; $i <= 5; $i++)
                    <button type="button" @click="rating = {{ $i }}"
                            :class="rating >= {{ $i }} ? 'text-amber-400' : 'text-gray-200'"
                            class="text-2xl leading-none transition-colors hover:text-amber-300">★</button>
                    @endfor
                </div>
                <textarea name="comment" placeholder="Optional comment…" rows="2" x-ref="comment"
                          class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none mb-2"></textarea>
                <button type="submit" :disabled="rating === 0 || loading"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-40 transition-colors">
                    <span x-show="!loading">Submit Feedback</span>
                    <span x-show="loading">Saving…</span>
                </button>
            </form>
            <p x-show="submitted" class="text-sm text-green-700 font-medium">✓ Thank you for your feedback!</p>
        </div>
        @endif
    </div>
</div>
@endforeach

@if($sessions->isEmpty())
<div class="text-center py-20 text-gray-400 text-sm">Programme not yet published.</div>
@endif
</div>

<script>
// Reverb: auto-highlight when session goes live
if (window.Echo) {
    window.Echo.channel('event.{{ $event->id }}.programme')
        .listen('.session.highlighted', data => {
            document.querySelectorAll('[id^="session-"]').forEach(el => {
                el.classList.remove('border-green-400','shadow-md','shadow-green-100','ring-1','ring-green-300');
                el.classList.add('border-gray-200');
            });
            if (data.session_id) {
                const live = document.getElementById('session-' + data.session_id);
                if (live) {
                    live.classList.remove('border-gray-200');
                    live.classList.add('border-green-400','shadow-md','shadow-green-100','ring-1','ring-green-300');
                    live.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
}
</script>
</body>
</html>
