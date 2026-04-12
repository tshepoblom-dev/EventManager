@extends('layouts.admin')
@section('title', 'Sponsors — ' . $event->name)
@section('page-title', 'Sponsors')

@push('header-actions')
<a href="{{ route('admin.events.sponsors.create', $event) }}"
   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Add Sponsor
</a>
@endpush

@section('content')
@php $tierColors = ['platinum'=>'bg-slate-100 text-slate-700','gold'=>'bg-yellow-100 text-yellow-700','silver'=>'bg-gray-100 text-gray-600','bronze'=>'bg-orange-100 text-orange-700']; @endphp

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($sponsors as $sponsor)
    <div class="bg-white rounded-xl border border-gray-200 p-5 flex flex-col gap-3">
        <div class="flex items-start gap-3">
            @if($sponsor->logo)
            <img src="{{ Storage::url($sponsor->logo) }}" alt="" class="w-10 h-10 object-contain rounded border border-gray-100 flex-shrink-0">
            @else
            <div class="w-10 h-10 rounded bg-gray-100 flex items-center justify-center text-gray-400 flex-shrink-0 text-lg font-bold">
                {{ strtoupper(substr($sponsor->company_name,0,1)) }}
            </div>
            @endif
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate">{{ $sponsor->company_name }}</p>
                @if($sponsor->booth_number)<p class="text-xs text-gray-400">Booth {{ $sponsor->booth_number }}</p>@endif
            </div>
            <span class="text-xs px-2 py-0.5 rounded font-medium capitalize {{ $tierColors[$sponsor->tier] ?? '' }}">{{ $sponsor->tier }}</span>
        </div>

        <div class="flex items-center justify-between text-xs text-gray-500">
            <span>{{ $sponsor->leads_count }} leads</span>
            @if($sponsor->website)
            <a href="{{ $sponsor->website }}" target="_blank" class="text-blue-600 hover:underline truncate max-w-32">{{ parse_url($sponsor->website, PHP_URL_HOST) }}</a>
            @endif
        </div>

        <div class="flex gap-2 pt-1 border-t border-gray-50">
            <a href="{{ route('admin.events.sponsors.edit', [$event, $sponsor]) }}"
               class="flex-1 text-center text-xs px-3 py-1.5 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">Edit</a>
            <form method="POST" action="{{ route('admin.events.sponsors.destroy', [$event, $sponsor]) }}"
                  onsubmit="return confirm('Remove {{ addslashes($sponsor->company_name) }}?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs px-3 py-1.5 border border-red-200 rounded-lg text-red-600 hover:bg-red-50">Remove</button>
            </form>
        </div>
    </div>
    @empty
    <div class="col-span-3 text-center py-16 text-sm text-gray-400">No sponsors yet.</div>
    @endforelse
</div>
@endsection
