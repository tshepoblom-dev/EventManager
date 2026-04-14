@extends('layouts.admin')
@section('title', 'Forms — ' . $event->name)
@section('page-title', 'Dynamic Forms')

@push('header-actions')
<a href="{{ route('admin.events.forms.create', $event) }}"
   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    New Form
</a>
@endpush

@section('content')
@php $typeColors = ['feedback'=>'bg-blue-100 text-blue-700','lead_capture'=>'bg-green-100 text-green-700','survey'=>'bg-purple-100 text-purple-700','sponsor_interest'=>'bg-amber-100 text-amber-700']; @endphp

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    @if($forms->isEmpty())
    <div class="text-center py-20">
        <p class="text-gray-400 text-sm mb-2">No forms yet.</p>
        <a href="{{ route('admin.events.forms.create', $event) }}" class="text-blue-600 text-sm hover:underline">Create your first form →</a>
    </div>
    @else
    <table class="w-full text-sm">
        <thead><tr class="border-b border-gray-100">
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Form</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden sm:table-cell">Type</th>
            <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden sm:table-cell">Responses</th>
            <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Active</th>
            <th class="px-5 py-3"></th>
        </tr></thead>
        <tbody class="divide-y divide-gray-50">
        @foreach($forms as $form)
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-5 py-3.5">
                <p class="font-medium text-gray-900">{{ $form->title }}</p>
                @if($form->description)<p class="text-xs text-gray-400 truncate max-w-xs">{{ $form->description }}</p>@endif
            </td>
            <td class="px-5 py-3.5 hidden sm:table-cell">
                <span class="text-xs px-2 py-0.5 rounded font-medium {{ $typeColors[$form->type] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ str_replace('_',' ',ucfirst($form->type)) }}
                </span>
            </td>
            <td class="px-5 py-3.5 text-center tabular-nums text-gray-700 hidden sm:table-cell">{{ $form->responses_count }}</td>
            <td class="px-5 py-3.5 text-center hidden md:table-cell">
                @if($form->is_active)
                <span class="inline-flex items-center gap-1 text-xs text-green-700"><span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>Active</span>
                @else
                <span class="text-xs text-gray-400">Inactive</span>
                @endif
            </td>
            <td class="px-5 py-3.5">
                <div class="flex items-center justify-end gap-2 flex-wrap">
                    <a href="{{ route('forms.show', $form) }}" target="_blank"
                       class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">Preview</a>
                    <a href="{{ route('admin.events.forms.show', [$event, $form]) }}"
                       class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">Responses</a>
                    <a href="{{ route('admin.events.forms.edit', [$event, $form]) }}"
                       class="text-xs px-3 py-1.5 border border-blue-200 rounded-lg text-blue-600 hover:bg-blue-50">Edit</a>
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
