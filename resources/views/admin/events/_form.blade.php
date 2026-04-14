{{-- resources/views/admin/events/_form.blade.php --}}
{{-- Usage: @include('admin.events._form', ['event' => $event ?? null]) --}}

@php $isEdit = isset($event) && $event->exists; @endphp

<div class="space-y-6">

    {{-- Name --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Event name <span class="text-red-500">*</span></label>
        <input type="text" name="name" value="{{ old('name', $event->name ?? '') }}"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                      @error('name') border-red-400 @enderror"
               placeholder="Heidedal Scale Up Entrepreneur's Day" required>
        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- Venue + Date row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Venue</label>
            <input type="text" name="venue" value="{{ old('venue', $event->venue ?? '') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   placeholder="e.g. Heidedal Community Hall">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Event date <span class="text-red-500">*</span></label>
            <input type="date" name="event_date" value="{{ old('event_date', isset($event) ? $event->event_date->format('Y-m-d') : '') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                          @error('event_date') border-red-400 @enderror"
                   required>
            @error('event_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- Times --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Start time <span class="text-red-500">*</span></label>
            <input type="time" name="start_time" value="{{ old('start_time', $event->start_time ?? '') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">End time <span class="text-red-500">*</span></label>
            <input type="time" name="end_time" value="{{ old('end_time', $event->end_time ?? '') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
        </div>
    </div>

    {{-- Description --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
        <textarea name="description" rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                  placeholder="Brief description of the event…">{{ old('description', $event->description ?? '') }}</textarea>
    </div>

    {{-- Status + Colour row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
            <select name="status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white">
                @foreach(['draft','published','live','closed'] as $s)
                    <option value="{{ $s }}" {{ old('status', $event->status ?? 'draft') === $s ? 'selected' : '' }}>
                        {{ ucfirst($s) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Brand colour</label>
            <div class="flex items-center gap-2">
                <input type="color" name="primary_color"
                       value="{{ old('primary_color', $event->primary_color ?? '#1a56db') }}"
                       class="w-10 h-10 border border-gray-300 rounded-lg cursor-pointer p-0.5">
                <input type="text" id="color-hex"
                       value="{{ old('primary_color', $event->primary_color ?? '#1a56db') }}"
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       maxlength="7" placeholder="#1a56db">
            </div>
        </div>
    </div>

    {{-- Logo --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
        @if($isEdit && $event->logo)
            <div class="mb-2">
                <img src="{{ Storage::url($event->logo) }}" alt="Current logo" class="h-12 object-contain rounded border border-gray-200 p-1">
            </div>
        @endif
        <input type="file" name="logo" accept="image/*"
               class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
        <p class="text-xs text-gray-400 mt-1">PNG or JPG, max 2 MB</p>
    </div>
</div>

<script>
    // Sync colour picker ↔ text input
    const picker = document.querySelector('input[type=color]');
    const hex    = document.getElementById('color-hex');
    if (picker && hex) {
        picker.addEventListener('input', () => hex.value = picker.value);
        hex.addEventListener('input',   () => { if (/^#[0-9a-f]{6}$/i.test(hex.value)) picker.value = hex.value; });
    }
</script>
