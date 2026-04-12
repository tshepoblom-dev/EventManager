@extends('layouts.admin')
@section('title', 'Import Attendees')
@section('page-title', 'Import Attendees via CSV')

@section('content')
<div class="max-w-2xl">

    {{-- Instructions --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-5">
        <h3 class="text-sm font-semibold text-blue-900 mb-2">CSV format</h3>
        <p class="text-xs text-blue-700 mb-3">The first row must be a header row with these columns:</p>
        <div class="bg-white rounded-lg border border-blue-200 overflow-x-auto">
            <table class="text-xs w-full">
                <thead>
                    <tr class="border-b border-blue-100">
                        <th class="text-left px-3 py-2 font-semibold text-blue-800">Column</th>
                        <th class="text-left px-3 py-2 font-semibold text-blue-800">Required</th>
                        <th class="text-left px-3 py-2 font-semibold text-blue-800">Example</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50">
                    @foreach([
                        ['first_name','Yes','Thabo'],
                        ['last_name', 'Yes','Nkosi'],
                        ['email',     'Yes','thabo@example.co.za'],
                        ['phone',     'No', '0821234567'],
                        ['company',   'No', 'Scale Up SA'],
                        ['job_title', 'No', 'Founder'],
                        ['ticket_type','No','general / vip / speaker / sponsor'],
                    ] as [$col,$req,$ex])
                    <tr>
                        <td class="px-3 py-2 font-mono text-blue-700">{{ $col }}</td>
                        <td class="px-3 py-2 {{ $req==='Yes'?'text-red-600 font-medium':'text-gray-400' }}">{{ $req }}</td>
                        <td class="px-3 py-2 text-gray-500">{{ $ex }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p class="text-xs text-blue-600 mt-2">Duplicate emails (already registered for this event) are automatically skipped.</p>
    </div>

    {{-- Upload form --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Upload file</h2>
            <p class="text-xs text-gray-500 mt-0.5">Event: {{ $event->name }}</p>
        </div>
        <form method="POST"
              action="{{ route('admin.events.attendees.import.store', $event) }}"
              enctype="multipart/form-data"
              class="px-6 py-5 space-y-4"
              id="import-form">
            @csrf

            <div x-data="{ file: null }"
                 class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-400 transition-colors"
                 @dragover.prevent
                 @drop.prevent="file = $event.dataTransfer.files[0]; $refs.fileInput.files = $event.dataTransfer.files">
                <input type="file" name="csv_file" accept=".csv,.txt" required
                       x-ref="fileInput"
                       @change="file = $event.target.files[0]"
                       class="hidden" id="csv-file">
                <label for="csv-file" class="cursor-pointer">
                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-700" x-text="file ? file.name : 'Click to select or drag your CSV here'"></p>
                    <p class="text-xs text-gray-400 mt-1">Max 5 MB · .csv or .txt</p>
                </label>
            </div>

            @error('csv_file')<p class="text-xs text-red-600">{{ $message }}</p>@enderror

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        id="import-btn"
                        class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    Import Attendees
                </button>
                <a href="{{ route('admin.events.attendees.index', $event) }}"
                   class="px-5 py-2 border border-gray-200 text-sm text-gray-600 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.getElementById('import-form').addEventListener('submit', function() {
        const btn = document.getElementById('import-btn');
        btn.disabled = true;
        btn.textContent = 'Importing…';
    });
</script>
@endpush
