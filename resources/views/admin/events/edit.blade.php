@extends('layouts.admin')
@section('title', 'Edit — ' . $event->name)
@section('page-title', 'Edit Event')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">{{ $event->name }}</h2>
        </div>
        <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data" class="px-6 py-5">
            @csrf
            @method('PATCH')
            @include('admin.events._form', ['event' => $event])
            <div class="mt-6 flex items-center gap-3">
                <button type="submit"
                        class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    Save Changes
                </button>
                <a href="{{ route('admin.events.show', $event) }}"
                   class="px-5 py-2 border border-gray-200 text-sm text-gray-600 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    {{-- Danger zone --}}
    <div class="mt-6 bg-white rounded-xl border border-red-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-red-100">
            <h2 class="text-sm font-semibold text-red-700">Danger Zone</h2>
        </div>
        <div class="px-6 py-5 flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-900">Delete this event</p>
                <p class="text-xs text-gray-500 mt-0.5">This will permanently delete all attendees, check-ins, and data.</p>
            </div>
            <form method="POST" action="{{ route('admin.events.destroy', $event) }}"
                  onsubmit="return confirm('Delete {{ addslashes($event->name) }}? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                    Delete Event
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
