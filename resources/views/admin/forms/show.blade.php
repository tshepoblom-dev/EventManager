@extends('layouts.admin')
@section('title', 'Responses — ' . $form->title)
@section('page-title', $form->title . ' — Responses')

@section('content')
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold text-gray-900">{{ $form->responses->count() }} response{{ $form->responses->count()!==1?'s':'' }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ str_replace('_',' ',ucfirst($form->type)) }} · {{ $form->fields->count() }} fields</p>
        </div>
        <a href="{{ route('admin.events.forms.edit', [$event, $form]) }}"
           class="text-xs px-3 py-1.5 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">← Edit Form</a>
    </div>

    @if($form->responses->isEmpty())
    <div class="text-center py-16 text-sm text-gray-400">No responses yet.</div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead><tr class="border-b border-gray-100">
                <th class="text-left px-4 py-3 font-semibold text-gray-500 uppercase tracking-wide">Submitted</th>
                <th class="text-left px-4 py-3 font-semibold text-gray-500 uppercase tracking-wide">Attendee</th>
                @foreach($form->fields as $field)
                <th class="text-left px-4 py-3 font-semibold text-gray-500 uppercase tracking-wide max-w-32 truncate">{{ $field->label }}</th>
                @endforeach
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
            @foreach($form->responses as $response)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $response->submitted_at->format('d M H:i') }}</td>
                <td class="px-4 py-3 text-gray-700">{{ $response->attendee?->full_name ?? 'Anonymous' }}</td>
                @foreach($form->fields as $field)
                <td class="px-4 py-3 text-gray-700 max-w-48">
                    @php $val = $response->responses[$field->id] ?? null; @endphp
                    @if($field->type === 'rating' && $val)
                        <span class="text-amber-500">{{ str_repeat('★', (int)$val) }}{{ str_repeat('☆', 5-(int)$val) }}</span>
                    @elseif(is_array($val))
                        {{ implode(', ', $val) }}
                    @else
                        <span class="truncate block max-w-xs">{{ $val ?? '—' }}</span>
                    @endif
                </td>
                @endforeach
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
