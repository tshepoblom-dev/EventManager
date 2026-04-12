@extends('layouts.admin')
@section('title', (isset($sponsor)?'Edit':'Add') . ' Sponsor')
@section('page-title', isset($sponsor) ? 'Edit Sponsor' : 'Add Sponsor')

@section('content')
<div class="max-w-xl">
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <form method="POST"
          action="{{ isset($sponsor) ? route('admin.events.sponsors.update',[$event,$sponsor]) : route('admin.events.sponsors.store',$event) }}"
          enctype="multipart/form-data"
          class="px-6 py-5 space-y-4">
        @csrf
        @if(isset($sponsor)) @method('PATCH') @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Company name <span class="text-red-500">*</span></label>
            <input type="text" name="company_name" value="{{ old('company_name', $sponsor->company_name ?? '') }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tier</label>
                <select name="tier" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach(['platinum','gold','silver','bronze'] as $t)
                    <option value="{{ $t }}" {{ old('tier',$sponsor->tier??'bronze')===$t?'selected':'' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Booth number</label>
                <input type="text" name="booth_number" value="{{ old('booth_number', $sponsor->booth_number ?? '') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="e.g. B12">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
            <input type="url" name="website" value="{{ old('website', $sponsor->website ?? '') }}"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="https://example.co.za">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="2"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('description', $sponsor->description ?? '') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Link to user account (sponsor login)</label>
            <select name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">— None —</option>
                @foreach($users as $u)
                <option value="{{ $u->id }}" {{ old('user_id',$sponsor->user_id??'')==$u->id?'selected':'' }}>{{ $u->name }} ({{ $u->email }})</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
            @if(isset($sponsor) && $sponsor->logo)
            <img src="{{ Storage::url($sponsor->logo) }}" class="h-10 mb-2 rounded border border-gray-100 p-0.5">
            @endif
            <input type="file" name="logo" accept="image/*"
                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                {{ isset($sponsor)?'Save Changes':'Add Sponsor' }}
            </button>
            <a href="{{ route('admin.events.sponsors.index', $event) }}"
               class="px-5 py-2 border border-gray-200 text-sm text-gray-600 rounded-lg hover:bg-gray-50 transition-colors">Cancel</a>
        </div>
    </form>
</div>
</div>
@endsection
