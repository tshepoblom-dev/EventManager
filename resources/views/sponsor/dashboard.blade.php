@extends('layouts.admin')
@section('title', 'Sponsor Dashboard')
@section('page-title', $sponsor->company_name . ' — Lead Dashboard')

@push('header-actions')
<a href="{{ route('sponsor.leads.create', $event) }}"
   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
    + Capture Lead
</a>
<a href="{{ route('sponsor.leads.export', $event) }}"
   class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-200 text-sm text-gray-600 rounded-lg hover:bg-gray-50">
    Export CSV
</a>
@endpush

@section('content')
@php
$stageColors = ['new'=>'bg-gray-100 text-gray-600','contacted'=>'bg-blue-100 text-blue-700','followed_up'=>'bg-amber-100 text-amber-700','paid'=>'bg-green-100 text-green-700'];
$interestColors = ['hot'=>'text-red-600','warm'=>'text-orange-500','cold'=>'text-blue-500'];
@endphp

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach([['Total Leads','total','text-gray-900'],['Hot','hot','text-red-700'],['Warm','warm','text-orange-600'],['Paid','paid','text-green-700']] as [$l,$k,$c])
    <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
        <p class="text-3xl font-bold {{ $c }} tabular-nums">{{ $stats[$k] }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ $l }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

{{-- Lead list --}}
<div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-900">All Leads</h2>
        <span id="live-badge" class="flex items-center gap-1.5 text-xs text-green-600 font-medium">
            <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>Live
        </span>
    </div>
    <ul id="lead-feed" class="divide-y divide-gray-50">
        @forelse($leads as $lead)
        <li class="flex items-start gap-3 px-5 py-3.5">
            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-700 flex-shrink-0 mt-0.5">
                {{ strtoupper(substr($lead->first_name,0,1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900">{{ $lead->first_name }} {{ $lead->last_name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ $lead->email }} @if($lead->company)· {{ $lead->company }}@endif</p>
            </div>
            <div class="flex-shrink-0 text-right">
                <span class="text-xs font-semibold capitalize {{ $interestColors[$lead->interest_level] ?? '' }}">{{ $lead->interest_level }}</span>
                <div class="mt-1">
                    <select onchange="updateStage({{ $lead->id }}, this.value)"
                            class="text-xs border border-gray-200 rounded px-1.5 py-0.5 bg-white focus:outline-none">
                        @foreach(['new'=>'New','contacted'=>'Contacted','followed_up'=>'Followed Up','paid'=>'Paid'] as $v=>$l)
                        <option value="{{ $v }}" {{ $lead->pipeline_stage===$v?'selected':'' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </li>
        @empty
        <li class="text-center py-12 text-sm text-gray-400" id="empty-leads">No leads yet. Capture your first lead!</li>
        @endforelse
    </ul>
</div>

{{-- Pipeline summary --}}
<div class="space-y-4">
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">Pipeline</h2>
        @foreach(['new'=>'New','contacted'=>'Contacted','followed_up'=>'Followed Up','paid'=>'Paid'] as $stage=>$label)
        <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
            <span class="text-xs px-2 py-0.5 rounded font-medium {{ $stageColors[$stage] }}">{{ $label }}</span>
            <span class="text-sm font-bold text-gray-900 tabular-nums">{{ $stats[$stage] }}</span>
        </div>
        @endforeach
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-sm font-semibold text-gray-900 mb-3">Quick Capture</h2>
        <a href="{{ route('sponsor.leads.create', $event) }}"
           class="block w-full text-center px-4 py-3 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
            + New Lead
        </a>
        <p class="text-xs text-gray-400 text-center mt-2">Or scan their QR badge</p>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name=csrf-token]').content;

async function updateStage(id, stage) {
    await fetch(`/sponsor/events/{{ $event->id }}/leads/${id}/stage`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ pipeline_stage: stage }),
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (!window.Echo) return;
    window.Echo.channel('sponsor.{{ $sponsor->id }}.leads')
        .listen('.lead.captured', data => {
            const empty = document.getElementById('empty-leads');
            if (empty) empty.remove();

            const li = document.createElement('li');
            li.className = 'flex items-start gap-3 px-5 py-3.5 bg-green-50';
            li.innerHTML = `
                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-xs font-bold text-green-700 flex-shrink-0 mt-0.5">
                    ${data.name.charAt(0).toUpperCase()}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900">${data.name}</p>
                    <p class="text-xs text-gray-400">${data.company || ''}</p>
                </div>
                <span class="text-xs font-semibold capitalize text-orange-500">${data.interest_level}</span>`;
            document.getElementById('lead-feed').prepend(li);
            setTimeout(() => li.classList.remove('bg-green-50'), 3000);
        });
});
</script>
@endpush
