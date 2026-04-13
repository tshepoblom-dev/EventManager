@extends('layouts.admin')
@section('title', 'Add Speaker')
@section('page-title', 'Add Speaker')

@php $event = $currentEvent ?? null; @endphp

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        @include('admin.speakers._form', [
            'speaker' => new \App\Models\Speaker(),
            'action'  => route('admin.speakers.store'),
            'method'  => 'POST',
        ])
    </div>
</div>
@endsection
