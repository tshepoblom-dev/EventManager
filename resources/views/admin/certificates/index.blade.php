@extends('layouts.admin')
@section('title', 'Certificates — ' . $event->name)
@section('page-title', 'Certificates')

@push('header-actions')
<form method="POST" action="{{ route('admin.events.certificates.bulk', $event) }}">
    @csrf
    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
        Generate All
    </button>
</form>
@endpush

@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    @if($attendees->isEmpty())
    <div class="text-center py-16 text-sm text-gray-400">No checked-in attendees yet.</div>
    @else
    <table class="w-full text-sm">
        <thead><tr class="border-b border-gray-100">
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Attendee</th>
            <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Certificate</th>
            <th class="px-5 py-3"></th>
        </tr></thead>
        <tbody class="divide-y divide-gray-50">
        @foreach($attendees as $row)
        @php $a = $row['attendee']; $ready = $row['cert_exists']; @endphp
        <tr class="hover:bg-gray-50">
            <td class="px-5 py-3.5">
                <p class="font-medium text-gray-900">{{ $a->full_name }}</p>
                <p class="text-xs text-gray-400">{{ $a->email }} @if($a->company)· {{ $a->company }}@endif</p>
            </td>
            <td class="px-5 py-3.5 text-center">
                @if($ready)
                <span class="text-xs text-green-700 font-medium">✓ Ready</span>
                @else
                <span class="text-xs text-gray-400">Not generated</span>
                @endif
            </td>
            <td class="px-5 py-3.5">
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('admin.events.certificates.download', [$event, $a]) }}"
                       class="text-xs px-3 py-1.5 border border-blue-200 rounded-lg text-blue-600 hover:bg-blue-50">
                        Download PDF
                    </a>
                </div>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection
