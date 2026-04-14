<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Heidedal Scale Up</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="h-full overflow-hidden bg-gray-50"
      x-data="{
          sidebarOpen: JSON.parse(localStorage.getItem('sidebar') ?? 'true'),
          mobileOpen: false,
          toggleSidebar() {
              this.sidebarOpen = !this.sidebarOpen;
              localStorage.setItem('sidebar', JSON.stringify(this.sidebarOpen));
          },
      }"
      @keydown.escape.window="mobileOpen = false">

{{-- Mobile backdrop --}}
<div x-cloak x-show="mobileOpen"
     class="fixed inset-0 bg-black/50 z-40 lg:hidden"
     x-transition:enter="transition-opacity ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="mobileOpen = false"></div>

<div class="flex h-full overflow-hidden">

{{-- ── Sidebar ─────────────────────────────────────────────────────── --}}
<aside x-cloak
       class="fixed lg:relative inset-y-0 left-0 z-50 lg:z-auto
              w-64 flex flex-col bg-white border-r border-gray-200 flex-shrink-0
              transition-all duration-300 ease-in-out overflow-hidden"
       :class="{
           '-translate-x-full lg:translate-x-0': !mobileOpen,
           'translate-x-0': mobileOpen,
           'lg:w-64': sidebarOpen,
           'lg:w-16': !sidebarOpen,
       }">

    {{-- Sidebar header --}}
    <div class="h-16 flex items-center gap-3 border-b border-gray-100 flex-shrink-0 px-3">
        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>
        {{-- Name + collapse button — visible when expanded --}}
        <div class="flex flex-1 items-center justify-between min-w-0" :class="!sidebarOpen ? 'lg:hidden' : ''">
            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900 leading-none">Heidedal</p>
                <p class="text-xs text-gray-400 mt-0.5">Scale Up Day</p>
            </div>
            <button @click="toggleSidebar()"
                    class="hidden lg:flex w-7 h-7 items-center justify-center rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors flex-shrink-0"
                    title="Collapse sidebar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
            </button>
        </div>
        {{-- Expand button — desktop collapsed only --}}
        <button @click="toggleSidebar()"
                class="hidden flex-shrink-0 w-7 h-7 items-center justify-center rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                :class="!sidebarOpen ? 'lg:flex' : 'lg:hidden'"
                title="Expand sidebar">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
            </svg>
        </button>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 py-4 space-y-0.5 overflow-y-auto overflow-x-hidden"
         :class="sidebarOpen ? 'px-3' : 'px-3 lg:px-2'">

        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 py-2 px-3 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
           :class="!sidebarOpen ? 'lg:px-0 lg:justify-center' : ''"
           :title="!sidebarOpen ? 'Dashboard' : ''">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span class="truncate" :class="!sidebarOpen ? 'lg:hidden' : ''">Dashboard</span>
        </a>

        <a href="{{ route('admin.events.index') }}"
           class="flex items-center gap-3 py-2 px-3 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.events.*') && !isset($event) ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
           :class="!sidebarOpen ? 'lg:px-0 lg:justify-center' : ''"
           :title="!sidebarOpen ? 'Events' : ''">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span class="truncate" :class="!sidebarOpen ? 'lg:hidden' : ''">Events</span>
        </a>

        <a href="{{ route('admin.users.index') }}"
           class="flex items-center gap-3 py-2 px-3 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
           :class="!sidebarOpen ? 'lg:px-0 lg:justify-center' : ''"
           :title="!sidebarOpen ? 'Users' : ''">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <span class="truncate" :class="!sidebarOpen ? 'lg:hidden' : ''">Users</span>
        </a>

        @if(isset($event))
        <div class="pt-3 pb-1">
            <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider truncate"
               :class="!sidebarOpen ? 'lg:hidden' : ''">{{ $event->name }}</p>
            <div class="hidden mx-2 border-t border-gray-200" :class="!sidebarOpen ? 'lg:block' : ''"></div>
        </div>

        @php
        $eNav = [
            ['Attendees',    'admin.events.attendees.index',   'admin.events.attendees.*', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            ['Sessions',     'admin.events.sessions.index',    'admin.events.sessions.*',  'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['Forms',        'admin.events.forms.index',       'admin.events.forms.*',     'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
            ['Sponsors',     'admin.events.sponsors.index',    'admin.events.sponsors.*',  'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['Leads',        'admin.events.leads.index',       'admin.events.leads.*',     'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
            ['Certificates', 'admin.events.certificates.index','admin.events.certificates.*','M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
        ];
        @endphp

        @foreach($eNav as [$label, $route, $match, $icon])
        <a href="{{ route($route, $event) }}"
           class="flex items-center gap-3 py-2 px-3 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs($match) ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
           :class="!sidebarOpen ? 'lg:px-0 lg:justify-center' : ''"
           :title="!sidebarOpen ? '{{ $label }}' : ''">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $icon }}"/></svg>
            <span class="truncate" :class="!sidebarOpen ? 'lg:hidden' : ''">{{ $label }}</span>
        </a>
        @endforeach

        <a href="{{ route('admin.speakers.index', ['event_id' => $event->id]) }}"
           class="flex items-center gap-3 py-2 px-3 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('admin.speakers.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
           :class="!sidebarOpen ? 'lg:px-0 lg:justify-center' : ''"
           :title="!sidebarOpen ? 'Speakers' : ''">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
            <span class="truncate" :class="!sidebarOpen ? 'lg:hidden' : ''">Speakers</span>
        </a>

        <a href="{{ route('staff.checkin.index', $event) }}"
           class="flex items-center gap-3 py-2 px-3 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('staff.checkin.*') ? 'bg-green-50 text-green-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}"
           :class="!sidebarOpen ? 'lg:px-0 lg:justify-center' : ''"
           :title="!sidebarOpen ? 'Check-in' : ''">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="truncate" :class="!sidebarOpen ? 'lg:hidden' : ''">Check-in</span>
        </a>

        <a href="{{ route('programme.index', $event) }}" target="_blank"
           class="flex items-center gap-3 py-2 px-3 rounded-lg text-sm font-medium transition-colors text-gray-600 hover:bg-gray-50 hover:text-gray-900"
           :class="!sidebarOpen ? 'lg:px-0 lg:justify-center' : ''"
           :title="!sidebarOpen ? 'Live Programme' : ''">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 10l4.553-2.069A1 1 0 0121 8.876V15.12a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/></svg>
            <span class="truncate" :class="!sidebarOpen ? 'lg:hidden' : ''">Live Programme ↗</span>
        </a>
        @endif

    </nav>

    {{-- User footer --}}
    <div class="border-t border-gray-100 p-3 flex-shrink-0">
        <div class="flex items-center gap-3 px-2 py-1 mb-1" :class="!sidebarOpen ? 'lg:hidden' : ''">
            <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center text-xs font-semibold text-blue-700 flex-shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-medium text-gray-800 truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ auth()->user()->role?->display_name }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="w-full flex items-center gap-3 py-1.5 rounded-lg text-xs text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition-colors"
                    :class="sidebarOpen ? 'px-3' : 'px-3 lg:px-0 lg:justify-center'"
                    :title="!sidebarOpen ? 'Sign out' : ''">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span :class="!sidebarOpen ? 'lg:hidden' : ''">Sign out</span>
            </button>
        </form>
    </div>

</aside>

{{-- ── Main content ─────────────────────────────────────────────────── --}}
<div class="flex-1 flex flex-col overflow-hidden min-w-0">

    <header class="h-16 bg-white border-b border-gray-200 flex items-center px-4 lg:px-6 gap-3 flex-shrink-0">
        <button @click="mobileOpen = !mobileOpen"
                class="lg:hidden flex items-center justify-center w-9 h-9 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <h1 class="text-sm font-semibold text-gray-900 flex-1 truncate min-w-0">@yield('page-title', 'Dashboard')</h1>
        <div class="flex items-center gap-2 flex-shrink-0 flex-wrap justify-end">
            @stack('header-actions')
        </div>
    </header>

    <div class="px-4 lg:px-6 pt-4 space-y-2">
        @foreach(['success'=>'green','warning'=>'yellow','error'=>'red'] as $type => $color)
        @if(session($type))
        <div class="flex items-start gap-3 p-3 bg-{{ $color }}-50 border border-{{ $color }}-200 rounded-lg text-sm text-{{ $color }}-800">
            {{ session($type) }}
        </div>
        @endif
        @endforeach
        @if(session('import_errors'))
        <ul class="px-5 text-xs text-yellow-700 space-y-0.5">
            @foreach(array_slice(session('import_errors'),0,5) as $e)<li>{{ $e }}</li>@endforeach
        </ul>
        @endif
    </div>

    <main class="flex-1 overflow-y-auto p-4 lg:p-6">
        @yield('content')
    </main>
</div>

</div>
@stack('scripts')
</body>
</html>
