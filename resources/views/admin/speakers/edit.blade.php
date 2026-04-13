@extends('layouts.admin')
@section('title', 'Edit Speaker')
@section('page-title', 'Edit Speaker')

@php $event = $speaker->event ?? ($currentEvent ?? null); @endphp

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        @include('admin.speakers._form', [
            'action' => route('admin.speakers.update', $speaker),
            'method' => 'PATCH',
        ])
    </div>
</div>
@endsection
