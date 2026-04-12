@extends('layouts.admin')
@section('title', 'Capture Lead')
@section('page-title', 'Capture Lead')

@push('header-actions')
<a href="{{ route('sponsor.dashboard', $event) }}"
   class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-200 text-sm text-gray-600 rounded-lg hover:bg-gray-50">
    ← Dashboard
</a>
@endpush

@section('content')
<div class="max-w-xl">
<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-100">
        <p class="text-sm font-medium text-gray-700">{{ $sponsor->company_name }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ $event->name }}</p>
    </div>

    <form method="POST" action="{{ route('sponsor.leads.store', $event) }}" class="px-6 py-5 space-y-4">
        @csrf

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">First name <span class="text-red-500">*</span></label>
                <input type="text" name="first_name" value="{{ old('first_name') }}" required autofocus
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('first_name') border-red-400 @enderror">
                @error('first_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Last name <span class="text-red-500">*</span></label>
                <input type="text" name="last_name" value="{{ old('last_name') }}" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('last_name') border-red-400 @enderror">
                @error('last_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-400 @enderror">
            @error('email')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input type="tel" name="phone" value="{{ old('phone') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Interest level <span class="text-red-500">*</span></label>
                <select name="interest_level" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="hot"  {{ old('interest_level')==='hot'  ?'selected':'' }}>🔥 Hot</option>
                    <option value="warm" {{ old('interest_level','warm')==='warm'?'selected':'' }}>🌤 Warm</option>
                    <option value="cold" {{ old('interest_level')==='cold' ?'selected':'' }}>❄️ Cold</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                <input type="text" name="company" value="{{ old('company') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Business type</label>
                <input type="text" name="business_type" value="{{ old('business_type') }}"
                       placeholder="e.g. Retail, Tech, NGO"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea name="notes" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                      placeholder="What they're interested in, follow-up action…">{{ old('notes') }}</textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="flex-1 px-5 py-3 bg-green-600 text-white text-sm font-semibold rounded-lg hover:bg-green-700 transition-colors">
                ✓ Save Lead
            </button>
            <a href="{{ route('sponsor.dashboard', $event) }}"
               class="px-5 py-3 border border-gray-200 text-sm text-gray-600 rounded-lg hover:bg-gray-50 transition-colors text-center">
                Cancel
            </a>
        </div>
    </form>
</div>
</div>
@endsection
