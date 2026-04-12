<!DOCTYPE html>
<html lang="en" class="bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Networking — {{ $event->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
</head>
<body class="min-h-screen pb-10">

{{-- Header --}}
<div class="bg-white border-b border-gray-200 sticky top-0 z-10">
    <div class="max-w-2xl mx-auto px-4 h-14 flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold text-gray-900">Networking</p>
            <p class="text-xs text-gray-400">{{ $event->name }}</p>
        </div>
        <span class="text-xs text-gray-500">{{ $connections->count() }} connection{{ $connections->count()!==1?'s':'' }}</span>
    </div>
</div>

<div class="max-w-2xl mx-auto px-4 py-5 space-y-6">

{{-- Scan-to-connect --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden" x-data="{ scanning: false }">
    <div class="px-5 py-4 flex items-center justify-between border-b border-gray-100">
        <h2 class="text-sm font-semibold text-gray-900">Scan to Connect</h2>
        <button @click="scanning = !scanning; scanning ? startScan() : stopScan()"
                :class="scanning ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'"
                class="px-3 py-1.5 text-white text-xs font-medium rounded-lg transition-colors">
            <span x-text="scanning ? '⏹ Stop' : '📷 Scan QR'"></span>
        </button>
    </div>
    <div x-show="scanning" class="p-4">
        <div class="relative bg-black rounded-xl overflow-hidden" style="aspect-ratio:4/3;max-height:260px">
            <video id="net-video" class="w-full h-full object-cover" playsinline muted></video>
            <canvas id="net-canvas" class="hidden"></canvas>
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="w-40 h-40 border-2 border-white rounded-2xl opacity-70"></div>
            </div>
        </div>
        <div id="scan-result" class="hidden mt-3 p-3 rounded-xl text-sm font-medium text-center"></div>
    </div>
</div>

{{-- My connections --}}
@if($connections->isNotEmpty())
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <h2 class="text-sm font-semibold text-gray-900">My Connections ({{ $connections->count() }})</h2>
    </div>
    <ul class="divide-y divide-gray-50" id="connections-list">
        @foreach($connections as $conn)
        @php $other = $conn->requester_id === $me->id ? $conn->receiver : $conn->requester; @endphp
        <li class="flex items-center gap-3 px-5 py-3.5">
            <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-sm font-bold text-blue-700 flex-shrink-0">
                {{ strtoupper(substr($other->first_name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">{{ $other->full_name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ $other->company }}@if($other->job_title) · {{ $other->job_title }}@endif</p>
            </div>
            @if($other->phone)
            <a href="tel:{{ $other->phone }}" class="text-xs text-blue-600 hover:underline flex-shrink-0">Call</a>
            @endif
        </li>
        @endforeach
    </ul>
</div>
@endif

{{-- Attendee directory --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <h2 class="text-sm font-semibold text-gray-900">Attendee Directory</h2>
        <p class="text-xs text-gray-400 mt-0.5">Showing opt-in profiles</p>
    </div>
    @if($attendees->isEmpty())
    <div class="text-center py-10 text-sm text-gray-400">No public profiles yet.</div>
    @else
    <ul class="divide-y divide-gray-50">
        @foreach($attendees as $attendee)
        <li class="flex items-center gap-3 px-5 py-3.5">
            <div class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center text-sm font-bold text-gray-500 flex-shrink-0">
                {{ strtoupper(substr($attendee->first_name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">{{ $attendee->full_name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ $attendee->company }}@if($attendee->job_title) · {{ $attendee->job_title }}@endif</p>
            </div>
            <span class="text-xs px-2 py-0.5 rounded bg-gray-100 text-gray-500 capitalize flex-shrink-0">{{ $attendee->ticket_type }}</span>
        </li>
        @endforeach
    </ul>
    @endif
</div>

</div>

<script>
const CONNECT_URL = '{{ route('networking.connect', $event) }}';
const CSRF        = document.querySelector('meta[name=csrf-token]').content;
let videoStream   = null;
let scanLoop      = null;
let scanning      = false;

async function startScan() {
    scanning = true;
    const video = document.getElementById('net-video');
    try {
        videoStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
        video.srcObject = videoStream;
        await video.play();
        tick();
    } catch(e) {
        showResult('Camera unavailable: ' + e.message, 'error');
    }
}

function stopScan() {
    scanning = false;
    if (videoStream) { videoStream.getTracks().forEach(t => t.stop()); videoStream = null; }
    if (scanLoop)    { cancelAnimationFrame(scanLoop); scanLoop = null; }
}

function tick() {
    const video  = document.getElementById('net-video');
    const canvas = document.getElementById('net-canvas');
    if (!scanning || !canvas) return;
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);
        const img  = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(img.data, img.width, img.height, { inversionAttempts: 'dontInvert' });
        if (code) { scanning = false; processQr(code.data); return; }
    }
    scanLoop = requestAnimationFrame(tick);
}

async function processQr(token) {
    const res  = await fetch(CONNECT_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ qr_code: token }),
    });
    const data = await res.json();
    if (res.status === 200) {
        showResult('✓ Connected with ' + data.target.name + (data.target.company ? ' · ' + data.target.company : ''), 'success');
    } else {
        showResult(data.message || 'Could not connect.', 'error');
    }
    setTimeout(() => { scanning = true; tick(); }, 3000);
}

function showResult(msg, type) {
    const el = document.getElementById('scan-result');
    el.className = 'mt-3 p-3 rounded-xl text-sm font-medium text-center ' +
        (type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800');
    el.textContent = msg;
    el.classList.remove('hidden');
    setTimeout(() => el.classList.add('hidden'), 4000);
}

// Real-time: notify when someone connects with you
document.addEventListener('DOMContentLoaded', () => {
    if (!window.Echo || {{ $me->id }} === undefined) return;
    window.Echo.channel('attendee.{{ $me->id }}.connections')
        .listen('.connection.made', data => {
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-6 left-1/2 -translate-x-1/2 bg-blue-700 text-white text-sm px-5 py-3 rounded-xl shadow-lg z-50';
            toast.textContent = (data.from_name || 'Someone') + ' connected with you!';
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 5000);
        });
});
</script>
</body>
</html>
