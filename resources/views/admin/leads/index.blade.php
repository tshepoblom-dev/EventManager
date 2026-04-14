@extends('layouts.admin')
@section('title', 'Leads — ' . $event->name)
@section('page-title', 'Lead Pipeline')

@push('header-actions')
<a href="{{ route('admin.events.leads.export', $event) }}"
   class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-200 text-sm text-gray-600 rounded-lg hover:bg-gray-50">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
    Export CSV
</a>
@endpush

@section('content')
@php
$stages = ['new'=>'New','contacted'=>'Contacted','followed_up'=>'Followed Up','paid'=>'Paid'];
$stageColors = ['new'=>'bg-gray-100 text-gray-600','contacted'=>'bg-blue-100 text-blue-700','followed_up'=>'bg-amber-100 text-amber-700','paid'=>'bg-green-100 text-green-700'];
$interestColors = ['hot'=>'bg-red-100 text-red-700','warm'=>'bg-orange-100 text-orange-700','cold'=>'bg-blue-100 text-blue-600'];
@endphp

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
    @foreach([['Total','total','text-gray-900'],['Hot','hot','text-red-700'],['New','new','text-blue-700'],['Paid','paid','text-green-700']] as [$l,$k,$c])
    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
        <p class="text-2xl font-bold {{ $c }} tabular-nums">{{ $stats[$k] }}</p>
        <p class="text-xs text-gray-500 mt-0.5">{{ $l }}</p>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-4 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-40">
        <label class="block text-xs font-medium text-gray-600 mb-1">Search</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, company…"
               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Stage</label>
        <select name="stage" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All stages</option>
            @foreach($stages as $v=>$l)<option value="{{ $v }}" {{ request('stage')===$v?'selected':'' }}>{{ $l }}</option>@endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Interest</label>
        <select name="level" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">All</option>
            @foreach(['hot','warm','cold'] as $l)<option value="{{ $l }}" {{ request('level')===$l?'selected':'' }}>{{ ucfirst($l) }}</option>@endforeach
        </select>
    </div>
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">Filter</button>
    @if(request()->hasAny(['search','stage','level']))
    <a href="{{ route('admin.events.leads.index', $event) }}" class="px-4 py-2 border border-gray-200 text-sm text-gray-600 rounded-lg hover:bg-gray-50">Clear</a>
    @endif
</form>

{{-- Table --}}
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    @if($leads->isEmpty())
    <div class="text-center py-16 text-sm text-gray-400">No leads yet.</div>
    @else
    <table class="w-full text-sm">
        <thead><tr class="border-b border-gray-100">
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Lead</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Sponsor</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Interest</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Stage</th>
            <th class="px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">Date</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-50">
        @foreach($leads as $lead)
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-5 py-3.5">
                <p class="font-medium text-gray-900">{{ $lead->first_name }} {{ $lead->last_name }}</p>
                <p class="text-xs text-gray-400">{{ $lead->email }} @if($lead->company)· {{ $lead->company }}@endif</p>
            </td>
            <td class="px-5 py-3.5 text-gray-600 hidden md:table-cell">{{ $lead->sponsor?->company_name ?? '—' }}</td>
            <td class="px-5 py-3.5">
                <span class="text-xs px-2 py-0.5 rounded font-medium capitalize {{ $interestColors[$lead->interest_level] ?? '' }}">{{ $lead->interest_level }}</span>
            </td>
            <td class="px-5 py-3.5">
                {{-- Fix #10: data-url uses named route helper so it stays correct if prefix changes --}}
                <select onchange="updateStage({{ $lead->id }}, this.value)"
                        data-url="{{ route('admin.events.leads.stage', [$event, $lead]) }}"
                        class="text-xs px-2 py-1 border border-gray-200 rounded-lg bg-white cursor-pointer focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($stages as $v=>$l)
                    <option value="{{ $v }}" {{ $lead->pipeline_stage===$v?'selected':'' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </td>
            <td class="px-5 py-3.5 text-xs text-gray-400 hidden lg:table-cell">{{ $lead->created_at->format('d M H:i') }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @if($leads->hasPages())
    <div class="px-5 py-4 border-t border-gray-100">{{ $leads->links() }}</div>
    @endif
    @endif
</div>
@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name=csrf-token]').content;

// Fix #10: read URL from data attribute (set by named route helper) rather than hardcoding it
async function updateStage(id, stage) {
    const select = document.querySelector(`select[onchange*="updateStage(${id},"]`);
    const url    = select?.dataset.url;
    if (!url) return;

    const res = await fetch(url, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ pipeline_stage: stage }),
    });

    if (!res.ok) {
        console.error('Stage update failed', await res.text());
    }
}

// Real-time new lead toast via Reverb
document.addEventListener('DOMContentLoaded', () => {
    if (!window.Echo) return;
    window.Echo.channel('event.{{ $event->id }}.dashboard')
        .listen('.lead.captured', data => {
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-6 right-6 bg-green-700 text-white text-sm px-4 py-3 rounded-xl shadow-lg z-50';
            toast.textContent = `New lead: ${data.name} (${data.interest_level})`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 5000);
        });
});
</script>
@endpush
