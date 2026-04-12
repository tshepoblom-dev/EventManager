@extends('layouts.admin')
@section('title', 'Programme — ' . $event->name)
@section('page-title', 'Programme Builder')

@push('header-actions')
<a href="{{ route('programme.index', $event) }}" target="_blank"
   class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-200 text-xs text-gray-600 rounded-lg hover:bg-gray-50 transition-colors">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
    Live view
</a>
@endpush

@section('content')

{{-- Data bootstrap (passed to Alpine) --}}
<script>
const ROUTES = {
    store:   '{{ route('admin.events.sessions.store',   $event) }}',
    reorder: '{{ route('admin.events.sessions.reorder', $event) }}',
    update:  (id) => `{{ url('admin/events/'.$event->id.'/sessions') }}/${id}`,
    destroy: (id) => `{{ url('admin/events/'.$event->id.'/sessions') }}/${id}`,
    dup:     (id) => `{{ url('admin/events/'.$event->id.'/sessions') }}/${id}/duplicate`,
};
const CSRF   = document.querySelector('meta[name=csrf-token]')?.content;
const ALL_SPEAKERS = @json($speakersData ?? []);
const INITIAL_SESSIONS = @json($sessionsData ?? []);
const EVENT_DATE = '{{ $event->event_date->format('Y-m-d') }}';
</script>

<div x-data="programmBuilder()" x-init="init()" class="flex gap-5 h-full" style="min-height:0">

{{-- ── Left: session list ─────────────────────────────────────────────── --}}
<div class="flex-1 flex flex-col min-w-0" style="min-height:0">

    {{-- Stats bar --}}
    <div class="grid grid-cols-5 gap-3 mb-4">
        @foreach([
            ['Total',    $stats['total'],    'text-gray-900',   'bg-white'],
            ['Talks',    $stats['talk'],     'text-blue-700',   'bg-blue-50'],
            ['Workshops',$stats['workshop'], 'text-purple-700', 'bg-purple-50'],
            ['Panels',   $stats['panel'],    'text-amber-700',  'bg-amber-50'],
            ['Breaks',   $stats['break'],    'text-gray-500',   'bg-gray-100'],
        ] as [$label, $count, $text, $bg])
        <div class="rounded-xl border border-gray-200 {{ $bg }} px-4 py-3 text-center">
            <p class="text-xl font-bold tabular-nums {{ $text }}">{{ $count }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    {{-- Filter tabs + Add button --}}
    <div class="flex items-center justify-between mb-3 gap-3">
        <div class="flex items-center gap-1 bg-white border border-gray-200 rounded-lg p-1">
            <template x-for="tab in ['all','talk','workshop','panel','break']" :key="tab">
                <button @click="filter = tab"
                        :class="filter === tab
                            ? 'bg-blue-600 text-white shadow-sm'
                            : 'text-gray-500 hover:text-gray-800'"
                        class="px-3 py-1.5 rounded-md text-xs font-medium capitalize transition-all"
                        x-text="tab === 'all' ? 'All types' : tab"></button>
            </template>
        </div>
        <button @click="openPanel()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Session
        </button>
    </div>

    {{-- Session list --}}
    <div class="flex-1 overflow-y-auto space-y-2" id="session-list">

        <template x-if="filteredSessions.length === 0">
            <div class="text-center py-20 bg-white rounded-xl border border-gray-200 border-dashed">
                <svg class="w-10 h-10 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <p class="text-gray-400 text-sm font-medium">No sessions yet</p>
                <p class="text-gray-400 text-xs mt-1">Click <strong>Add Session</strong> to build your programme</p>
            </div>
        </template>

        <template x-for="session in filteredSessions" :key="session.id">
            <div
                class="group bg-white rounded-xl border transition-all duration-200 cursor-grab active:cursor-grabbing select-none"
                :class="{
                    'border-green-400 shadow-sm shadow-green-100 ring-1 ring-green-300': session.is_live,
                    'border-gray-200 hover:border-gray-300 hover:shadow-sm': !session.is_live,
                    'opacity-50': dragSrc === session.id,
                }"
                draggable="true"
                @dragstart="dragStart($event, session.id)"
                @dragover.prevent="dragOver($event, session.id)"
                @dragleave="dragLeave($event)"
                @drop.prevent="drop($event, session.id)"
                @dragend="dragEnd()">

                <div class="flex items-stretch">
                    {{-- Type colour bar --}}
                    <div class="w-1.5 rounded-l-xl flex-shrink-0"
                         :class="{
                            'bg-blue-500':   session.type === 'talk',
                            'bg-purple-500': session.type === 'workshop',
                            'bg-amber-500':  session.type === 'panel',
                            'bg-gray-300':   session.type === 'break',
                         }"></div>

                    {{-- Drag handle --}}
                    <div class="flex items-center px-2 text-gray-300 group-hover:text-gray-400 transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7 2a2 2 0 110 4 2 2 0 010-4zm6 0a2 2 0 110 4 2 2 0 010-4zM7 8a2 2 0 110 4 2 2 0 010-4zm6 0a2 2 0 110 4 2 2 0 010-4zM7 14a2 2 0 110 4 2 2 0 010-4zm6 0a2 2 0 110 4 2 2 0 010-4z"/>
                        </svg>
                    </div>

                    {{-- Main content --}}
                    <div class="flex-1 px-4 py-3.5 min-w-0">
                        <div class="flex items-start gap-3">
                            {{-- Time --}}
                            <div class="flex-shrink-0 text-right w-16 pt-0.5">
                                <p class="text-sm font-semibold tabular-nums"
                                   :class="session.is_live ? 'text-green-700' : 'text-gray-700'"
                                   x-text="session.starts_fmt"></p>
                                <p class="text-xs text-gray-400 tabular-nums" x-text="session.ends_fmt"></p>
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap mb-0.5">
                                    <template x-if="session.is_live">
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>Live
                                        </span>
                                    </template>
                                    <span class="text-xs px-2 py-0.5 rounded font-medium capitalize"
                                          :class="{
                                             'bg-blue-100 text-blue-700':   session.type === 'talk',
                                             'bg-purple-100 text-purple-700': session.type === 'workshop',
                                             'bg-amber-100 text-amber-700':  session.type === 'panel',
                                             'bg-gray-100 text-gray-500':    session.type === 'break',
                                          }"
                                          x-text="session.type"></span>
                                    <span x-show="session.room" class="text-xs text-gray-400" x-text="session.room"></span>
                                    <span x-show="session.capacity" class="text-xs text-gray-400"
                                          x-text="'Cap: ' + session.capacity"></span>
                                </div>
                                <p class="text-sm font-semibold text-gray-900 leading-snug" x-text="session.title"></p>
                                <p x-show="session.description"
                                   class="text-xs text-gray-400 mt-0.5 line-clamp-1"
                                   x-text="session.description"></p>

                                {{-- Speakers --}}
                                <div x-show="session.speakers && session.speakers.length > 0" class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                                    <template x-for="sp in session.speakers" :key="sp.id">
                                        <span class="inline-flex items-center gap-1 text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-full">
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span x-text="sp.name"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            {{-- Feedback stats --}}
                            <div x-show="session.feedback_count > 0" class="flex-shrink-0 text-right">
                                <p class="text-sm font-semibold text-amber-600" x-text="'★ ' + session.feedback_avg_rating"></p>
                                <p class="text-xs text-gray-400" x-text="session.feedback_count + ' ratings'"></p>
                            </div>

                            {{-- Actions --}}
                            <div class="flex-shrink-0 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button @click.stop="editSession(session)"
                                        title="Edit"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button @click.stop="duplicateSession(session.id)"
                                        title="Duplicate"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-purple-600 hover:bg-purple-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>
                                <button @click.stop="deleteSession(session.id)"
                                        title="Delete"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

{{-- ── Right: slide-over panel ────────────────────────────────────────── --}}
<div class="flex-shrink-0 transition-all duration-300 ease-in-out"
     :class="panelOpen ? 'w-96' : 'w-0 overflow-hidden'">
    <div class="w-96 bg-white border border-gray-200 rounded-xl h-full flex flex-col shadow-sm" style="min-height:0">

        {{-- Panel header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 flex-shrink-0">
            <h2 class="text-sm font-semibold text-gray-900" x-text="editing ? 'Edit Session' : 'New Session'"></h2>
            <button @click="closePanel()" class="p-1.5 rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Error banner --}}
        <div x-show="errorMsg" class="mx-5 mt-3 px-3 py-2 bg-red-50 border border-red-200 rounded-lg text-xs text-red-700" x-text="errorMsg"></div>

        {{-- Form --}}
        <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">

            {{-- Title --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" x-model="form.title" placeholder="e.g. Opening Keynote"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       :class="errors.title ? 'border-red-400' : ''">
                <p x-show="errors.title" class="text-xs text-red-600 mt-1" x-text="errors.title"></p>
            </div>

            {{-- Type --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1.5">Type</label>
                <div class="grid grid-cols-4 gap-1.5">
                    <template x-for="t in ['talk','workshop','panel','break']" :key="t">
                        <button type="button" @click="form.type = t"
                                :class="form.type === t
                                    ? {'talk':'bg-blue-600 text-white border-blue-600','workshop':'bg-purple-600 text-white border-purple-600','panel':'bg-amber-500 text-white border-amber-500','break':'bg-gray-500 text-white border-gray-500'}[t]
                                    : 'bg-white text-gray-600 border-gray-200 hover:border-gray-300'"
                                class="px-2 py-2 border rounded-lg text-xs font-medium capitalize transition-all"
                                x-text="t"></button>
                    </template>
                </div>
            </div>

            {{-- Time row --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Start <span class="text-red-500">*</span></label>
                    <input type="datetime-local" x-model="form.starts_at"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           :class="errors.starts_at ? 'border-red-400' : ''">
                    <p x-show="errors.starts_at" class="text-xs text-red-600 mt-1" x-text="errors.starts_at"></p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">End <span class="text-red-500">*</span></label>
                    <input type="datetime-local" x-model="form.ends_at"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           :class="errors.ends_at ? 'border-red-400' : ''">
                    <p x-show="errors.ends_at" class="text-xs text-red-600 mt-1" x-text="errors.ends_at"></p>
                </div>
            </div>

            {{-- Room + Capacity --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Room</label>
                    <input type="text" x-model="form.room" placeholder="Main Hall"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Capacity</label>
                    <input type="number" x-model="form.capacity" placeholder="Unlimited" min="1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                <textarea x-model="form.description" rows="3" placeholder="Brief description for attendees…"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
            </div>

            {{-- Speakers --}}
            <div x-show="ALL_SPEAKERS.length > 0">
                <label class="block text-xs font-medium text-gray-700 mb-2">Speakers</label>
                <div class="space-y-1.5 max-h-40 overflow-y-auto border border-gray-200 rounded-lg p-2.5">
                    <template x-for="sp in ALL_SPEAKERS" :key="sp.id">
                        <label class="flex items-center gap-2.5 cursor-pointer py-1 px-1 rounded-md hover:bg-gray-50 transition-colors">
                            <input type="checkbox" :value="sp.id"
                                   :checked="form.speaker_ids.includes(sp.id)"
                                   @change="toggleSpeaker(sp.id)"
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-gray-800" x-text="sp.name"></p>
                                <p x-show="sp.company" class="text-xs text-gray-400" x-text="sp.company"></p>
                            </div>
                        </label>
                    </template>
                </div>
            </div>

        </div>

        {{-- Panel footer --}}
        <div class="px-5 py-4 border-t border-gray-100 flex gap-2.5 flex-shrink-0">
            <button @click="saveSession()"
                    :disabled="saving"
                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-60 transition-colors">
                <span x-show="saving" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                <span x-text="saving ? 'Saving…' : (editing ? 'Save Changes' : 'Add Session')"></span>
            </button>
            <button @click="closePanel()"
                    class="px-4 py-2.5 border border-gray-200 text-sm text-gray-600 rounded-lg hover:bg-gray-50 transition-colors">
                Cancel
            </button>
        </div>
    </div>
</div>

</div>{{-- end x-data --}}

<script>
function programmBuilder() {
    return {
        sessions:    [...INITIAL_SESSIONS].sort((a,b) => a.sort_order - b.sort_order || a.starts_at.localeCompare(b.starts_at)),
        filter:      'all',
        panelOpen:   false,
        editing:     null,   // session id being edited, or null for new
        saving:      false,
        errorMsg:    '',
        errors:      {},
        dragSrc:     null,
        dragTarget:  null,

        // form state
        form: {
            title:       '',
            type:        'talk',
            starts_at:   '',
            ends_at:     '',
            room:        '',
            capacity:    '',
            description: '',
            speaker_ids: [],
        },

        get filteredSessions() {
            const s = this.filter === 'all'
                ? this.sessions
                : this.sessions.filter(s => s.type === this.filter);
            return [...s].sort((a,b) => a.sort_order - b.sort_order || a.starts_at.localeCompare(b.starts_at));
        },

        init() {
            // Refresh "is_live" flag every minute
            setInterval(() => {
                const now = new Date();
                this.sessions = this.sessions.map(s => ({
                    ...s,
                    is_live: new Date(s.starts_at) <= now && new Date(s.ends_at) >= now,
                }));
            }, 60000);
        },

        openPanel(defaults = {}) {
            this.form = {
                title:       defaults.title       ?? '',
                type:        defaults.type        ?? 'talk',
                starts_at:   defaults.starts_at   ?? (EVENT_DATE + 'T09:00'),
                ends_at:     defaults.ends_at     ?? (EVENT_DATE + 'T10:00'),
                room:        defaults.room        ?? '',
                capacity:    defaults.capacity    ?? '',
                description: defaults.description ?? '',
                speaker_ids: defaults.speaker_ids ?? [],
            };
            this.editing  = defaults.id ?? null;
            this.errors   = {};
            this.errorMsg = '';
            this.panelOpen = true;
        },

        editSession(session) {
            this.openPanel(session);
        },

        closePanel() {
            this.panelOpen = false;
            this.editing   = null;
            this.errors    = {};
            this.errorMsg  = '';
        },

        toggleSpeaker(id) {
            const idx = this.form.speaker_ids.indexOf(id);
            if (idx === -1) {
                this.form.speaker_ids.push(id);
            } else {
                this.form.speaker_ids.splice(idx, 1);
            }
        },

        async saveSession() {
            this.saving   = true;
            this.errors   = {};
            this.errorMsg = '';

            const url    = this.editing ? ROUTES.update(this.editing) : ROUTES.store;
            const method = this.editing ? 'PATCH' : 'POST';

            try {
                const res = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: JSON.stringify({
                        ...this.form,
                        capacity: this.form.capacity !== '' ? parseInt(this.form.capacity) : null,
                    }),
                });

                const data = await res.json();

                if (res.status === 422) {
                    // Validation errors
                    const errs = data.errors ?? {};
                    this.errors = {};
                    for (const [key, msgs] of Object.entries(errs)) {
                        this.errors[key] = Array.isArray(msgs) ? msgs[0] : msgs;
                    }
                    this.errorMsg = 'Please fix the errors below.';
                    return;
                }

                if (!res.ok) {
                    this.errorMsg = data.message ?? 'Something went wrong.';
                    return;
                }

                if (this.editing) {
                    const idx = this.sessions.findIndex(s => s.id === this.editing);
                    if (idx !== -1) this.sessions.splice(idx, 1, data.session);
                } else {
                    this.sessions.push(data.session);
                }

                this.closePanel();
            } catch (e) {
                this.errorMsg = 'Network error. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        async deleteSession(id) {
            if (!confirm('Delete this session? This cannot be undone.')) return;

            try {
                const res = await fetch(ROUTES.destroy(id), {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                });

                if (res.ok) {
                    this.sessions = this.sessions.filter(s => s.id !== id);
                    if (this.editing === id) this.closePanel();
                }
            } catch (e) {
                alert('Could not delete session. Please try again.');
            }
        },

        async duplicateSession(id) {
            try {
                const res = await fetch(ROUTES.dup(id), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                });
                const data = await res.json();
                if (data.ok) {
                    this.sessions.push(data.session);
                    // Auto-open the duplicate for editing so the user can adjust the time
                    this.editSession(data.session);
                }
            } catch (e) {
                alert('Could not duplicate session.');
            }
        },

        // ── Drag & drop reordering ──────────────────────────────────────

        dragStart(e, id) {
            this.dragSrc = id;
            e.dataTransfer.effectAllowed = 'move';
        },

        dragOver(e, id) {
            if (this.dragSrc === id) return;
            this.dragTarget = id;
            e.currentTarget.classList.add('bg-blue-50');
        },

        dragLeave(e) {
            e.currentTarget.classList.remove('bg-blue-50');
        },

        async drop(e, targetId) {
            e.currentTarget.classList.remove('bg-blue-50');
            if (!this.dragSrc || this.dragSrc === targetId) return;

            // Reorder in local state
            const srcIdx = this.sessions.findIndex(s => s.id === this.dragSrc);
            const tgtIdx = this.sessions.findIndex(s => s.id === targetId);
            const [moved] = this.sessions.splice(srcIdx, 1);
            this.sessions.splice(tgtIdx, 0, moved);

            // Re-assign sort_order
            this.sessions.forEach((s, i) => { s.sort_order = i + 1; });

            // Persist to server
            await fetch(ROUTES.reorder, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ order: this.sessions.map(s => s.id) }),
            });
        },

        dragEnd() {
            this.dragSrc    = null;
            this.dragTarget = null;
            document.querySelectorAll('#session-list .bg-blue-50').forEach(el => el.classList.remove('bg-blue-50'));
        },
    };
}
</script>

@endsection
