@extends('layouts.admin')
@section('title', 'Users')
@section('page-title', 'User Management')

@push('header-actions')
<a href="{{ route('admin.users.create') }}"
   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
    </svg>
    Add User
</a>
@endpush

@section('content')
{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-3 mb-5">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email…"
        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    <select name="role" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="">All roles</option>
        @foreach($roles as $role)
        <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
            {{ $role->display_name }}
        </option>
        @endforeach
    </select>
    <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">Filter</button>
    @if(request()->hasAny(['search','role']))
    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-gray-500 text-sm hover:text-gray-700">Clear</a>
    @endif
</form>

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-100 bg-gray-50">
                <th class="text-left px-4 py-3 font-medium text-gray-600">Name</th>
                <th class="text-left px-4 py-3 font-medium text-gray-600 hidden md:table-cell">Email</th>
                <th class="text-left px-4 py-3 font-medium text-gray-600">Role</th>
                <th class="text-left px-4 py-3 font-medium text-gray-600 hidden md:table-cell">Joined</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($users as $user)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-xs font-semibold text-blue-700 flex-shrink-0">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <span class="font-medium text-gray-900">{{ $user->name }}</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-gray-600 hidden md:table-cell">{{ $user->email }}</td>
                <td class="px-4 py-3">
                    @php
                    $roleColors = [
                        'admin'    => 'bg-red-100 text-red-700',
                        'staff'    => 'bg-purple-100 text-purple-700',
                        'speaker'  => 'bg-blue-100 text-blue-700',
                        'sponsor'  => 'bg-amber-100 text-amber-700',
                        'attendee' => 'bg-green-100 text-green-700',
                    ];
                    $color = $roleColors[$user->role?->name] ?? 'bg-gray-100 text-gray-600';
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                        {{ $user->role?->display_name ?? 'No role' }}
                    </span>
                </td>
                <td class="px-4 py-3 text-gray-500 hidden md:table-cell">{{ $user->created_at->format('d M Y') }}</td>
                <td class="px-4 py-3">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.users.edit', $user) }}"
                           class="px-3 py-1.5 text-xs font-medium text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50">
                            Edit
                        </a>
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                              onsubmit="return confirm('Delete {{ addslashes($user->name) }}? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                class="px-3 py-1.5 text-xs font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50">
                                Delete
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-10 text-center text-gray-400 text-sm">No users found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($users->hasPages())
<div class="mt-4">{{ $users->links() }}</div>
@endif
@endsection
