<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Registration — Heidedal Scale Up</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex items-center justify-center py-12">
<div class="w-full max-w-md px-6">
    <div class="text-center mb-8">
        <div class="mx-auto w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">You're invited!</h1>
        <p class="text-sm text-gray-500 mt-1">Complete your Heidedal Scale Up account</p>
    </div>

    {{-- Welcome banner --}}
    <div class="mb-5 p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-800">
        Hi <strong>{{ $attendee->first_name }}</strong>! You're already registered as an attendee.
        Set a password below to activate your account and access the platform.
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-8">
        <form method="POST" action="{{ route('register.invited.store', $token) }}" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full name</label>
                <input id="name" type="text" name="name"
                       value="{{ old('name', $attendee->full_name) }}" required autofocus
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                <input id="email" type="email" name="email"
                       value="{{ old('email', $attendee->email) }}" required readonly
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-600 cursor-not-allowed">
                <p class="mt-1 text-xs text-gray-400">Pre-filled from your attendee registration — cannot be changed here.</p>
                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">I am joining as</label>
                <div class="space-y-2">
                    @foreach($roles as $role)
                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 hover:bg-blue-50 transition-colors has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                        <input type="radio" name="role_id" value="{{ $role->id }}"
                               {{ (old('role_id') == $role->id || ($loop->first && !old('role_id'))) ? 'checked' : '' }}
                               class="text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-800">{{ $role->display_name }}</span>
                    </label>
                    @endforeach
                </div>
                @error('role_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Choose a password</label>
                <input id="password" type="password" name="password" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-400 @enderror">
                @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit"
                class="w-full bg-blue-600 text-white py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                Activate my account →
            </button>
        </form>
    </div>

    <p class="mt-4 text-center text-xs text-gray-400">
        Already have an account? <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Sign in</a>
    </p>
</div>
</body>
</html>
