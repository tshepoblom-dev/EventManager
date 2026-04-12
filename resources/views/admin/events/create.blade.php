@extends('layouts.admin')
@section('title', 'New Event')
@section('page-title', 'Create Event')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Event details</h2>
        </div>
        <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data" class="px-6 py-5">
            @csrf
            @include('admin.events._form')
            <div class="mt-6 flex items-center gap-3">
                <button type="submit"
                        class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    Create Event
                </button>
                <a href="{{ route('admin.events.index') }}"
                   class="px-5 py-2 border border-gray-200 text-sm text-gray-600 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
