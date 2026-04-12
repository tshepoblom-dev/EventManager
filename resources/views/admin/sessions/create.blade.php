@extends('layouts.admin')
@section('title', (isset($session) ? 'Edit' : 'New') . ' Session — ' . $event->name)
@section('page-title', isset($session) ? 'Edit Session' : 'Add Session')

@section('content')
<div class="max-w-2xl">
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100">
        <p class="text-xs text-gray-500">Event: <span class="font-medium text-gray-800">{{ $event->name }}</span></p>
    </div>
    <form method="POST"
          action="{{ isset($session) ? route('admin.events.sessions.update', [$event,$session]) : route('admin.events.sessions.store', $event) }}"
          class="px-6 py-5 space-y-5">
        @csrf
        @if(isset($session)) @method('PATCH') @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $session->title ?? '') }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-400 @enderror">
            @error('title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('description', $session->description ?? '') }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="starts_at"
                       value="{{ old('starts_at', isset($session) ? $session->starts_at->format('Y-m-d\TH:i') : '') }}"
                       required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('starts_at')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="ends_at"
                       value="{{ old('ends_at', isset($session) ? $session->ends_at->format('Y-m-d\TH:i') : '') }}"
                       required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('ends_at')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['talk','workshop','panel','break'] as $t)
                    <option value="{{ $t }}" {{ old('type', $session->type ?? 'talk') === $t ? 'selected':'' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Room</label>
                <input type="text" name="room" value="{{ old('room', $session->room ?? '') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Main Hall">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                <input type="number" name="capacity" value="{{ old('capacity', $session->capacity ?? '') }}" min="1"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Leave blank for unlimited">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sort order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $session->sort_order ?? 0) }}" min="0"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        @if($speakers->isNotEmpty())
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Speakers</label>
            <div class="space-y-2 max-h-40 overflow-y-auto border border-gray-200 rounded-lg p-3">
                @foreach($speakers as $speaker)
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="speaker_ids[]" value="{{ $speaker->id }}"
                           {{ in_array($speaker->id, old('speaker_ids', isset($session) ? $session->speakers->pluck('id')->toArray() : [])) ? 'checked':'' }}
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                    <span class="text-sm text-gray-700">{{ $speaker->name }}</span>
                    @if($speaker->company)<span class="text-xs text-gray-400">· {{ $speaker->company }}</span>@endif
                </label>
                @endforeach
            </div>
        </div>
        @endif

        <div class="pt-2 flex gap-3">
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                {{ isset($session) ? 'Save Changes' : 'Add Session' }}
            </button>
            <a href="{{ route('admin.events.sessions.index', $event) }}"
               class="px-5 py-2 border border-gray-200 text-sm text-gray-600 rounded-lg hover:bg-gray-50 transition-colors">Cancel</a>
        </div>
    </form>
</div>
</div>
@endsection
