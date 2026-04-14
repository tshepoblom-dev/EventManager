@extends('layouts.admin')
@section('title', 'Check-in — ' . $event->name)
@section('page-title', 'Check-in Scanner')

@push('head')
{{-- jsQR: pure-JS QR decoder, no native dependencies --}}
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>
@endpush

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- ── Left: Scanner ────────────────────────────────────────────────── --}}
    <div class="space-y-4">

        {{-- Live stats strip --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap items-center gap-4">
            <div class="text-center">
                <p class="text-2xl font-bold text-green-700 tabular-nums" id="stat-checked">{{ $stats['checked_in'] }}</p>
                <p class="text-xs text-gray-500">Checked in</p>
            </div>
            <div class="h-8 w-px bg-gray-200"></div>
            <div class="text-center">
                <p class="text-2xl font-bold text-gray-800 tabular-nums" id="stat-total">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-500">Registered</p>
            </div>
            <div class="flex-1 min-w-48">
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>Progress</span>
                    <span id="stat-pct">
                        {{ $stats['total'] > 0 ? round($stats['checked_in'] / $stats['total'] * 100, 1) : 0 }}%
                    </span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full transition-all duration-500" id="stat-bar"
                         style="width: {{ $stats['total'] > 0 ? round($stats['checked_in'] / $stats['total'] * 100) : 0 }}%"></div>
                </div>
            </div>
        </div>

        {{-- Tab bar: Camera / Manual --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="flex border-b border-gray-100" id="tabs">
                <button onclick="switchTab('camera')" id="tab-camera"
                        class="flex-1 py-3 text-sm font-medium border-b-2 border-blue-600 text-blue-700 transition-colors">
                    📷 Scan QR
                </button>
                <button onclick="switchTab('manual')" id="tab-manual"
                        class="flex-1 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-colors">
                    🔍 Search
                </button>
            </div>

            {{-- Camera panel --}}
            <div id="panel-camera" class="p-4">
                <div class="relative bg-black rounded-xl overflow-hidden" style="aspect-ratio:4/3">
                    <video id="qr-video" class="w-full h-full object-cover" playsinline muted></video>
                    <canvas id="qr-canvas" class="hidden"></canvas>
                    {{-- Finder overlay --}}
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="w-48 h-48 border-2 border-white rounded-2xl opacity-70"></div>
                    </div>
                    {{-- Camera status --}}
                    <div id="camera-status" class="absolute bottom-3 left-0 right-0 flex justify-center">
                        <span class="text-xs text-white bg-black/50 rounded-full px-3 py-1" id="camera-status-text">
                            Starting camera…
                        </span>
                    </div>
                </div>

                {{-- Feedback banner --}}
                <div id="scan-feedback" class="hidden mt-3 p-3 rounded-xl text-sm font-medium text-center"></div>

                <p class="text-xs text-gray-400 text-center mt-2">Point camera at attendee's QR code</p>
            </div>

            {{-- Manual search panel --}}
            <div id="panel-manual" class="hidden p-4">
                <div class="flex gap-2 mb-3">
                    <input type="text" id="search-input" placeholder="Name, email, or company…"
                           class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button onclick="doSearch()" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        Search
                    </button>
                </div>
                <div id="search-results" class="space-y-2"></div>
            </div>
        </div>

    </div>

    {{-- ── Right: Live feed ─────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900">Recent Check-ins</h2>
            <span class="flex items-center gap-1.5 text-xs text-green-600 font-medium">
                <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                Live
            </span>
        </div>
        <ul id="checkin-feed" class="divide-y divide-gray-50 max-h-[600px] overflow-y-auto">
            @forelse($recent as $ci)
            <li class="flex items-center gap-3 px-5 py-3">
                <div class="w-9 h-9 rounded-full bg-green-100 flex items-center justify-center text-sm font-bold text-green-700 flex-shrink-0">
                    {{ strtoupper(substr($ci->attendee->first_name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $ci->attendee->full_name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $ci->attendee->company }}</p>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-xs text-gray-500">{{ $ci->checked_in_at->format('H:i') }}</p>
                    <span class="text-xs capitalize text-gray-400">{{ $ci->method }}</span>
                </div>
            </li>
            @empty
            <li class="text-center py-12 text-sm text-gray-400" id="empty-feed">No check-ins yet</li>
            @endforelse
        </ul>
    </div>

</div>
@endsection

@push('scripts')
<script>
const EVENT_ID  = {{ $event->id }};
const SCAN_URL  = '{{ route('staff.checkin.scan', $event) }}';
const SEARCH_URL= '{{ route('staff.checkin.search', $event) }}';
const CSRF      = document.querySelector('meta[name=csrf-token]').content;

// ── Tabs ────────────────────────────────────────────────────────────────
function switchTab(tab) {
    ['camera','manual'].forEach(t => {
        document.getElementById(`panel-${t}`).classList.toggle('hidden', t !== tab);
        const btn = document.getElementById(`tab-${t}`);
        btn.classList.toggle('border-blue-600', t === tab);
        btn.classList.toggle('text-blue-700', t === tab);
        btn.classList.toggle('border-transparent', t !== tab);
        btn.classList.toggle('text-gray-500', t !== tab);
    });
    if (tab === 'camera') startCamera();
    else stopCamera();
}

// ── Camera / QR scanner ─────────────────────────────────────────────────
let videoStream = null;
let scanLoop    = null;
let scanning    = true;

async function startCamera() {
    const video  = document.getElementById('qr-video');
    const status = document.getElementById('camera-status-text');

    try {
        videoStream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 720 } }
        });
        video.srcObject = videoStream;
        await video.play();
        status.textContent = 'Camera ready — hold QR code in frame';
        startScanLoop();
    } catch (err) {
        status.textContent = 'Camera unavailable: ' + err.message;
    }
}

function stopCamera() {
    if (scanLoop)    { cancelAnimationFrame(scanLoop); scanLoop = null; }
    if (videoStream) { videoStream.getTracks().forEach(t => t.stop()); videoStream = null; }
}

function startScanLoop() {
    const video  = document.getElementById('qr-video');
    const canvas = document.getElementById('qr-canvas');
    const ctx    = canvas.getContext('2d');

    function tick() {
        if (!scanning || video.readyState !== video.HAVE_ENOUGH_DATA) {
            scanLoop = requestAnimationFrame(tick);
            return;
        }
        canvas.width  = video.videoWidth;
        canvas.height = video.videoHeight;
        ctx.drawImage(video, 0, 0);
        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
        const code = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: 'dontInvert' });

        if (code) {
            scanning = false;
            processToken(code.data);
        }

        scanLoop = requestAnimationFrame(tick);
    }
    scanLoop = requestAnimationFrame(tick);
}

// ── Token → check-in API ────────────────────────────────────────────────
async function processToken(token) {
    try {
        const res  = await fetch(SCAN_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ token }),
        });
        const data = await res.json();
        showFeedback(data, res.status);
    } catch (e) {
        showFeedback({ status: 'error', message: 'Network error' }, 500);
    }
}

function showFeedback(data, status) {
    const el = document.getElementById('scan-feedback');
    el.classList.remove('hidden', 'bg-green-100', 'text-green-800', 'bg-red-100', 'text-red-800',
                                  'bg-yellow-100', 'text-yellow-800');

    if (status === 200) {
        el.classList.add('bg-green-100', 'text-green-800');
        el.innerHTML = `<strong>✓ Welcome!</strong> ${data.attendee.name}<br>
            <span class="font-normal text-xs">${data.attendee.company || ''} · ${data.attendee.ticket_type}</span>`;
        addToFeed(data.attendee, data.checked_in_at || new Date().toISOString(), 'qr');
        updateStats(data.stats);
    } else if (status === 409) {
        el.classList.add('bg-yellow-100', 'text-yellow-800');
        el.innerHTML = `<strong>Already checked in</strong> ${data.attendee.name} at ${data.attendee.checked_in_at}`;
    } else {
        el.classList.add('bg-red-100', 'text-red-800');
        el.textContent = data.message || 'Check-in failed.';
    }

    el.classList.remove('hidden');
    // Re-enable scanning after 3s
    setTimeout(() => { scanning = true; el.classList.add('hidden'); }, 3000);
}

// ── Manual search ───────────────────────────────────────────────────────
let searchTimer = null;
document.getElementById('search-input')?.addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(doSearch, 400);
});

async function doSearch() {
    const q = document.getElementById('search-input').value.trim();
    if (q.length < 2) { document.getElementById('search-results').innerHTML = ''; return; }

    const res  = await fetch(`${SEARCH_URL}?query=${encodeURIComponent(q)}`, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    });
    const data = await res.json();
    renderSearchResults(data.attendees || []);
}

function renderSearchResults(attendees) {
    const el = document.getElementById('search-results');
    if (!attendees.length) {
        el.innerHTML = '<p class="text-xs text-gray-400 text-center py-4">No attendees found.</p>';
        return;
    }
    el.innerHTML = attendees.map(a => `
        <div class="flex items-center gap-3 p-3 border border-gray-200 rounded-xl hover:bg-gray-50 transition-colors">
            <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-sm font-bold text-blue-700 flex-shrink-0">
                ${a.name.charAt(0).toUpperCase()}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">${a.name}</p>
                <p class="text-xs text-gray-400 truncate">${a.email} · ${a.company || '—'}</p>
            </div>
            ${a.checked_in
                ? `<span class="text-xs text-green-600 font-medium flex-shrink-0">✓ In</span>`
                : `<button onclick="checkInById(${a.id}, this)"
                           class="flex-shrink-0 px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors">
                       Check in
                   </button>`
            }
        </div>`).join('');
}

async function checkInById(attendeeId, btn) {
    btn.disabled = true;
    btn.textContent = '…';

    const url  = `{{ url('staff/events/' . $event->id . '/checkin') }}/${attendeeId}`;
    const res  = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
    });
    const data = await res.json();

    if (res.status === 200) {
        btn.textContent  = '✓';
        btn.className = 'flex-shrink-0 px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg';
        addToFeed(data.attendee, new Date().toISOString(), 'manual');
        updateStats(data.stats);
    } else if (res.status === 409) {
        btn.textContent = '✓ Already in';
        btn.className = 'flex-shrink-0 px-3 py-1.5 bg-yellow-500 text-white text-xs font-medium rounded-lg';
    } else {
        btn.textContent = 'Error';
        btn.disabled = false;
    }
}

// ── Feed + stats helpers ────────────────────────────────────────────────
function addToFeed(attendee, timestamp, method) {
    const feed  = document.getElementById('checkin-feed');
    const empty = document.getElementById('empty-feed');
    if (empty) empty.remove();

    const time = new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    const li   = document.createElement('li');
    li.className = 'flex items-center gap-3 px-5 py-3 bg-green-50';
    li.innerHTML = `
        <div class="w-9 h-9 rounded-full bg-green-100 flex items-center justify-center text-sm font-bold text-green-700 flex-shrink-0">
            ${(attendee.name || '?').charAt(0).toUpperCase()}
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">${attendee.name}</p>
            <p class="text-xs text-gray-400 truncate">${attendee.company || ''}</p>
        </div>
        <div class="text-right flex-shrink-0">
            <p class="text-xs text-gray-500">${time}</p>
            <span class="text-xs capitalize text-gray-400">${method}</span>
        </div>`;

    feed.insertBefore(li, feed.firstChild);
    setTimeout(() => li.classList.remove('bg-green-50'), 3000);
    while (feed.children.length > 15) feed.removeChild(feed.lastChild);
}

function updateStats(stats) {
    if (!stats) return;
    document.getElementById('stat-checked').textContent = stats.total_checked_in;
    document.getElementById('stat-total').textContent   = stats.total_attendees;
    const pct = stats.total_attendees > 0
        ? Math.round(stats.total_checked_in / stats.total_attendees * 100 * 10) / 10
        : 0;
    document.getElementById('stat-pct').textContent  = pct + '%';
    document.getElementById('stat-bar').style.width  = pct + '%';
}

// ── Reverb real-time feed ───────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.Echo !== 'undefined') {
        window.Echo.channel(`event.${EVENT_ID}.checkins`)
            .listen('.attendee.checked_in', function(data) {
                addToFeed(data.attendee, data.checked_in_at, data.method);
                updateStats({ total_checked_in: data.total_checked_in, total_attendees: data.total_attendees });
            });
    }

    // Auto-start camera on load
    startCamera();
});

window.addEventListener('beforeunload', stopCamera);
</script>
@endpush
