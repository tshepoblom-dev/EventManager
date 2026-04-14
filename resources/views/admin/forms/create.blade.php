@extends('layouts.admin')
@section('title', 'New Form')
@section('page-title', 'Create Form')

@section('content')
<div class="max-w-xl">
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100">
        <p class="text-xs text-gray-500">Event: <span class="font-medium text-gray-800">{{ $event->name }}</span></p>
    </div>
    <form method="POST" action="{{ route('admin.events.forms.store', $event) }}" class="px-6 py-5 space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="e.g. Session Feedback">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="2"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                      placeholder="Optional intro shown above the form">{{ old('description') }}</textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['feedback'=>'Feedback','lead_capture'=>'Lead Capture','survey'=>'Survey','sponsor_interest'=>'Sponsor Interest'] as $v => $l)
                    <option value="{{ $v }}" {{ old('type') === $v ? 'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col gap-3 pt-6">
                <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                    Active (accepting responses)
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                    <input type="checkbox" name="allow_anonymous" value="1" class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                    Allow anonymous
                </label>
            </div>
        </div>

        <div class="pt-2 flex gap-3">
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                Create &amp; Add Fields →
            </button>
            <a href="{{ route('admin.events.forms.index', $event) }}"
               class="px-5 py-2 border border-gray-200 text-sm text-gray-600 rounded-lg hover:bg-gray-50 transition-colors">Cancel</a>
        </div>
    </form>
</div>
</div>
@endsection
