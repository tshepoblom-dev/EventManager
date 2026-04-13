{{--
  Shared speaker form. Variables expected:
    $speaker   — Speaker model (may be new/unsaved for create)
    $events    — collection of all events
    $attendees — collection of linkable attendees
    $users     — collection of linkable users
    $action    — POST action URL
    $method    — 'POST' or 'PATCH'
--}}
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if($method === 'PATCH') @method('PATCH') @endif

    {{-- Event --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Event <span class="text-red-500">*</span></label>
        <select name="event_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('event_id') border-red-400 @enderror">
            <option value="">— select an event —</option>
            @foreach($events as $event)
            <option value="{{ $event->id }}"
                {{ old('event_id', $speaker->event_id ?? $currentEvent->id ?? '') == $event->id ? 'selected' : '' }}>
                {{ $event->name }} ({{ $event->event_date->format('d M Y') }})
            </option>
            @endforeach
        </select>
        @error('event_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Link to existing attendee / user --}}
    <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl space-y-4">
        <p class="text-sm font-semibold text-blue-800">Link to existing record (optional)</p>
        <p class="text-xs text-blue-700">Linking to an attendee or user account means this speaker can also check in at the venue and log into the platform.</p>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Attendee record</label>
                <select name="attendee_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— none —</option>
                    @foreach($attendees as $attendee)
                    <option value="{{ $attendee->id }}"
                        {{ old('attendee_id', $speaker->attendee_id ?? '') == $attendee->id ? 'selected' : '' }}>
                        {{ $attendee->full_name }} ({{ $attendee->email }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">User account</label>
                <select name="user_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— none —</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}"
                        {{ old('user_id', $speaker->user_id ?? '') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }} ({{ $user->email }})
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Core profile --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Display name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $speaker->name ?? '') }}" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror">
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', $speaker->email ?? '') }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-400 @enderror">
            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title / Position</label>
            <input type="text" name="title" value="{{ old('title', $speaker->title ?? '') }}"
                placeholder="e.g. CEO at Acme Corp"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
        <textarea name="bio" rows="4"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('bio', $speaker->bio ?? '') }}</textarea>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">LinkedIn URL</label>
            <input type="url" name="linkedin" value="{{ old('linkedin', $speaker->linkedin ?? '') }}"
                placeholder="https://linkedin.com/in/…"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('linkedin')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Twitter / X handle</label>
            <input type="text" name="twitter" value="{{ old('twitter', $speaker->twitter ?? '') }}"
                placeholder="@handle"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Profile photo</label>
        @if(!empty($speaker->photo))
        <div class="mb-3 flex items-center gap-3">
            <img src="{{ asset('storage/' . $speaker->photo) }}" class="w-14 h-14 rounded-full object-cover border border-gray-200">
            <span class="text-xs text-gray-500">Upload a new photo to replace this one.</span>
        </div>
        @endif
        <input type="file" name="photo" accept="image/*"
            class="w-full text-sm text-gray-600 file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
        @error('photo')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit"
            class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            {{ isset($speaker->id) ? 'Save Changes' : 'Create Speaker' }}
        </button>
        <a href="{{ route('admin.speakers.index') }}" class="px-5 py-2 text-sm text-gray-600 hover:text-gray-800">
            Cancel
        </a>
    </div>
</form>
