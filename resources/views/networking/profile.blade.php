<!DOCTYPE html>
<html lang="en" class="bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $attendee->full_name }} — {{ $event->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">

{{-- Header --}}
<div class="bg-white border-b border-gray-200 sticky top-0 z-10">
    <div class="max-w-xl mx-auto px-4 h-14 flex items-center gap-3">
        <a href="{{ route('networking.index', $event) }}"
           class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <p class="text-sm font-semibold text-gray-900">Attendee Profile</p>
    </div>
</div>

<div class="max-w-xl mx-auto px-4 py-6">

    {{-- Profile card --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-4">
        <div class="h-20 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
        <div class="px-6 pb-6">
            <div class="-mt-8 mb-4">
                <div class="w-16 h-16 rounded-full bg-blue-100 border-4 border-white flex items-center justify-center text-blue-700 text-xl font-bold shadow-sm">
                    {{ strtoupper(substr($attendee->first_name, 0, 1) . substr($attendee->last_name, 0, 1)) }}
                </div>
            </div>

            <h1 class="text-xl font-bold text-gray-900">{{ $attendee->full_name }}</h1>

            @if($attendee->job_title || $attendee->company)
            <p class="text-sm text-gray-500 mt-0.5">
                {{ collect([$attendee->job_title, $attendee->company])->filter()->implode(' · ') }}
            </p>
            @endif

            @if($attendee->user?->bio)
            <p class="text-sm text-gray-600 mt-3 leading-relaxed">{{ $attendee->user->bio }}</p>
            @endif

            <div class="mt-4 flex flex-wrap gap-2">
                @if($attendee->ticket_type)
                <span class="inline-flex items-center text-xs px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 font-medium capitalize">
                    {{ $attendee->ticket_type }}
                </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Connect action --}}
    @if(auth()->check() && auth()->user()->email !== $attendee->email)
    <div class="bg-white rounded-2xl border border-gray-200 p-5"
         x-data="{
             status: 'idle',
             message: '',
             async connect() {
                 this.status = 'loading';
                 const csrf = document.querySelector('meta[name=csrf-token]').content;
                 try {
                     const res = await fetch('{{ route('networking.connect', $event) }}', {
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/json',
                             'X-CSRF-TOKEN': csrf,
                             'Accept': 'application/json',
                         },
                         body: JSON.stringify({ qr_code: '{{ $attendee->qr_code }}' }),
                     });
                     const data = await res.json();
                     this.status = res.ok || res.status === 409 ? 'done' : 'error';
                     this.message = data.message;
                 } catch (e) {
                     this.status = 'error';
                     this.message = 'Connection failed. Please try again.';
                 }
             }
         }">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">Connect with {{ $attendee->first_name }}</h2>

        <div x-show="status === 'idle' || status === 'loading'">
            <button @click="connect" :disabled="status === 'loading'"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-xl hover:bg-blue-700 disabled:opacity-50 transition-colors">
                <span x-show="status !== 'loading'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </span>
                <span x-show="status === 'loading'" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                <span x-text="status === 'loading' ? 'Connecting…' : 'Connect'"></span>
            </button>
        </div>

        <div x-show="status === 'done'" class="flex items-center gap-2 text-sm text-green-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span x-text="message"></span>
        </div>

        <div x-show="status === 'error'" class="flex items-center gap-2 text-sm text-red-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span x-text="message"></span>
        </div>
    </div>
    @endif

</div>
</body>
</html>
