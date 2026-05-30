<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'EduBridge' }} — EduBridge</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased">

<div class="min-h-screen flex flex-col">
    {{-- Top nav --}}
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex h-16 items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="{{ route('home') }}" class="text-xl font-extrabold text-green-700">EduBridge</a>
                <div class="hidden md:flex items-center gap-4 text-sm text-gray-600">
                    @auth
                        @if(auth()->user()->isStudent())
                            <a href="{{ route('student.dashboard') }}" class="hover:text-green-700 {{ request()->routeIs('student.*') ? 'text-green-700 font-semibold' : '' }}">Dashboard</a>
                            <a href="{{ route('student.companion.index') }}" class="hover:text-green-700 {{ request()->routeIs('student.companion*') ? 'text-green-700 font-semibold' : '' }}">AI Companion</a>
                        @elseif(auth()->user()->isTeacher())
                            <a href="{{ route('teacher.dashboard') }}" class="hover:text-green-700 {{ request()->routeIs('teacher.*') ? 'text-green-700 font-semibold' : '' }}">Dashboard</a>
                            <a href="{{ route('teacher.courses.index') }}" class="hover:text-green-700">My Courses</a>
                        @elseif(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="hover:text-green-700">Dashboard</a>
                            <a href="{{ route('admin.users.index') }}" class="hover:text-green-700">Users</a>
                        @endif
                    @endauth
                </div>
            </div>
            <div class="flex items-center gap-4">
                @auth
                    <span class="text-sm text-gray-500">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-sm text-red-500 hover:text-red-700">Log out</button>
                    </form>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="bg-green-50 border-b border-green-200 text-green-800 text-sm px-6 py-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border-b border-red-200 text-red-800 text-sm px-6 py-3">{{ session('error') }}</div>
    @endif

    {{-- Page content --}}
    <main class="flex-1">
        {{ $slot }}
    </main>
</div>

</body>
</html>
