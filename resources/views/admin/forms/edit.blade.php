@extends('layouts.admin')
@section('title', 'Edit Form — ' . $form->title)
@section('page-title', 'Form Builder')

@push('header-actions')
<a href="{{ route('admin.events.forms.show', [$event, $form]) }}"
   class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-200 text-sm text-gray-600 rounded-lg hover:bg-gray-50">
    Responses ({{ $form->fields->count() > 0 ? '…' : '0' }})
</a>
<a href="{{ route('forms.show', $form) }}" target="_blank"
   class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-200 text-sm text-gray-600 rounded-lg hover:bg-gray-50">
    Preview ↗
</a>
@endpush

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6" x-data="formBuilder()" x-init="init()">

{{-- Left: Form settings --}}
<div class="lg:col-span-2 space-y-4">
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Form settings</h2>
        </div>
        <form method="POST" action="{{ route('admin.events.forms.update', [$event, $form]) }}" class="px-5 py-4 space-y-3">
            @csrf @method('PATCH')
            <input type="hidden" name="fields" :value="JSON.stringify(fields)">

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Title</label>
                <input type="text" name="title" value="{{ old('title', $form->title) }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                <textarea name="description" rows="2"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('description', $form->description) }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['feedback'=>'Feedback','lead_capture'=>'Lead Capture','survey'=>'Survey','sponsor_interest'=>'Sponsor Interest'] as $v=>$l)
                    <option value="{{ $v }}" {{ $form->type===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 text-xs text-gray-700 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ $form->is_active?'checked':'' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                    Active
                </label>
                <label class="flex items-center gap-2 text-xs text-gray-700 cursor-pointer">
                    <input type="checkbox" name="allow_anonymous" value="1" {{ $form->allow_anonymous?'checked':'' }} class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                    Allow anonymous
                </label>
            </div>
            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                Save Form &amp; Fields
            </button>
        </form>
    </div>

    {{-- Add field panel --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Add field</h2>
        </div>
        <div class="px-5 py-4 space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Label</label>
                <input type="text" x-model="newField.label" placeholder="e.g. Your name"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                <select x-model="newField.type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="text">Text</option>
                    <option value="textarea">Paragraph</option>
                    <option value="email">Email</option>
                    <option value="phone">Phone</option>
                    <option value="number">Number</option>
                    <option value="rating">Rating (1–5)</option>
                    <option value="dropdown">Dropdown</option>
                    <option value="checkbox">Checkboxes</option>
                </select>
            </div>
            <div x-show="['dropdown','checkbox'].includes(newField.type)">
                <label class="block text-xs font-medium text-gray-600 mb-1">Options (one per line)</label>
                <textarea x-model="newField.optionsRaw" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                          placeholder="Option A&#10;Option B&#10;Option C"></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Placeholder</label>
                <input type="text" x-model="newField.placeholder"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                <input type="checkbox" x-model="newField.required" class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                Required
            </label>
            <button @click="addField" type="button"
                    class="w-full px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded-lg hover:bg-gray-900 transition-colors">
                + Add Field
            </button>
        </div>
    </div>
</div>

{{-- Right: field list --}}
<div class="lg:col-span-3">
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900">Fields</h2>
            <span class="text-xs text-gray-400" x-text="fields.length + ' field' + (fields.length!==1?'s':'')"></span>
        </div>

        <div x-show="fields.length === 0" class="text-center py-12 text-sm text-gray-400">
            No fields yet — add your first field on the left.
        </div>

        <ul class="divide-y divide-gray-50" id="field-list">
            <template x-for="(field, index) in fields" :key="field._key">
                <li class="flex items-start gap-3 px-5 py-4">
                    {{-- Reorder arrows --}}
                    <div class="flex flex-col gap-1 pt-0.5 flex-shrink-0">
                        <button @click="moveUp(index)" type="button" :disabled="index===0"
                                class="w-5 h-5 flex items-center justify-center rounded text-gray-400 hover:text-gray-600 disabled:opacity-20">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                        </button>
                        <button @click="moveDown(index)" type="button" :disabled="index===fields.length-1"
                                class="w-5 h-5 flex items-center justify-center rounded text-gray-400 hover:text-gray-600 disabled:opacity-20">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>

                    {{-- Field info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-medium text-gray-900" x-text="field.label"></span>
                            <span x-show="field.required" class="text-red-500 text-xs">*</span>
                            <span class="text-xs px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 capitalize" x-text="field.type"></span>
                        </div>
                        <div x-show="field.options && field.options.length" class="mt-1 flex flex-wrap gap-1">
                            <template x-for="opt in (field.options||[])">
                                <span class="text-xs px-2 py-0.5 rounded bg-blue-50 text-blue-700" x-text="opt"></span>
                            </template>
                        </div>
                    </div>

                    <button @click="removeField(index)" type="button"
                            class="flex-shrink-0 text-gray-300 hover:text-red-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </li>
            </template>
        </ul>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script>
function formBuilder() {
    return {
        fields: @json($form->fields->map(fn($f) => [
            'id'          => $f->id,
            'label'       => $f->label,
            'type'        => $f->type,
            'required'    => $f->required,
            'placeholder' => $f->placeholder,
            'options'     => $f->options ?? [],
            '_key'        => $f->id,
        ])),
        newField: { label: '', type: 'text', required: false, placeholder: '', optionsRaw: '' },
        _counter: 9000,

        init() {},

        addField() {
            if (!this.newField.label.trim()) { alert('Please enter a field label.'); return; }
            const opts = ['dropdown','checkbox'].includes(this.newField.type)
                ? this.newField.optionsRaw.split('\n').map(s=>s.trim()).filter(Boolean)
                : [];
            this.fields.push({
                id: null,
                label: this.newField.label.trim(),
                type: this.newField.type,
                required: this.newField.required,
                placeholder: this.newField.placeholder,
                options: opts,
                _key: ++this._counter,
            });
            this.newField = { label: '', type: 'text', required: false, placeholder: '', optionsRaw: '' };
        },

        removeField(i) { this.fields.splice(i, 1); },
        moveUp(i)      { if (i > 0) [this.fields[i-1], this.fields[i]] = [this.fields[i], this.fields[i-1]]; },
        moveDown(i)    { if (i < this.fields.length-1) [this.fields[i], this.fields[i+1]] = [this.fields[i+1], this.fields[i]]; },
    };
}
</script>
@endpush
