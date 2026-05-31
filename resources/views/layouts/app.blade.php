<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' — ' : '' }}EduBridge</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="h-full bg-slate-50 antialiased" x-data="{ sidebarOpen: false }">
<div class="flex min-h-screen">

    {{-- ═══ SIDEBAR ═══ --}}
    <aside
        class="fixed inset-y-0 left-0 z-50 flex flex-col w-64 bg-white border-r border-slate-100 shadow-sm transition-transform duration-300 ease-in-out"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    >
        {{-- Logo row --}}
        <div class="flex items-center gap-3 h-16 px-5 border-b border-slate-100 flex-shrink-0">
            <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <a href="{{ route('home') }}" class="font-extrabold text-lg text-slate-900 tracking-tight">EduBridge</a>
            <button @click="sidebarOpen = false" class="ml-auto p-1 rounded text-slate-400 hover:text-slate-600 lg:hidden">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-0.5">
            @auth
            @php $role = auth()->user()->role; @endphp

            {{-- STUDENT --}}
            @if($role === 'student')
                <p class="section-label px-3 pb-2 pt-1">Learning</p>
                <x-sidebar-link href="{{ route('student.dashboard') }}" :active="request()->routeIs('student.dashboard')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></x-slot>
                    Dashboard
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('courses.index') }}" :active="request()->routeIs('courses.*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></x-slot>
                    Browse Courses
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('student.companion.index') }}" :active="request()->routeIs('student.companion*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></x-slot>
                    AI Companion
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('student.achievements') }}" :active="request()->routeIs('student.achievements')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0"/></x-slot>
                    Achievements
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('student.leaderboard') }}" :active="request()->routeIs('student.leaderboard')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></x-slot>
                    Leaderboard
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('student.transactions.index') }}" :active="request()->routeIs('student.transactions*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></x-slot>
                    Payment History
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('subjects.index') }}" :active="request()->routeIs('subjects.*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/></x-slot>
                    Subjects
                </x-sidebar-link>

            {{-- TEACHER --}}
            @elseif($role === 'teacher')
                <p class="section-label px-3 pb-2 pt-1">Teaching</p>
                <x-sidebar-link href="{{ route('teacher.dashboard') }}" :active="request()->routeIs('teacher.dashboard')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></x-slot>
                    Dashboard
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('teacher.courses.index') }}" :active="request()->routeIs('teacher.courses.*') || request()->routeIs('teacher.lessons.*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></x-slot>
                    My Courses
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('teacher.live-sessions.create') }}" :active="request()->routeIs('teacher.live-sessions.*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z"/></x-slot>
                    Live Sessions
                </x-sidebar-link>

                <p class="section-label px-3 pb-2 pt-3">Tools</p>
                <x-sidebar-link href="{{ route('teacher.ai-tools.index') }}" :active="request()->routeIs('teacher.ai-tools*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></x-slot>
                    AI Tools
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('subjects.index') }}" :active="request()->routeIs('subjects.*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/></x-slot>
                    Subject Library
                </x-sidebar-link>

                <p class="section-label px-3 pb-2 pt-3">Finance</p>
                <x-sidebar-link href="{{ route('teacher.verification.index') }}" :active="request()->routeIs('teacher.verification*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></x-slot>
                    KYC Verification
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('teacher.settlements.index') }}" :active="request()->routeIs('teacher.settlements*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></x-slot>
                    Settlements
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('teacher.equipment.index') }}" :active="request()->routeIs('teacher.equipment*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3"/></x-slot>
                    Equipment & Loans
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('teacher.transactions.index') }}" :active="request()->routeIs('teacher.transactions*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/></x-slot>
                    Transactions
                </x-sidebar-link>

            {{-- ADMIN --}}
            @elseif($role === 'admin')
                <p class="section-label px-3 pb-2 pt-1">Platform</p>
                <x-sidebar-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></x-slot>
                    Dashboard
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('admin.users.index') }}" :active="request()->routeIs('admin.users.*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></x-slot>
                    Users
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('admin.courses.index') }}" :active="request()->routeIs('admin.courses.*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></x-slot>
                    Courses
                </x-sidebar-link>

                <p class="section-label px-3 pb-2 pt-3">Finance</p>
                <x-sidebar-link href="{{ route('admin.verifications.index') }}" :active="request()->routeIs('admin.verifications.*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></x-slot>
                    KYC Verifications
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('admin.settlements.index') }}" :active="request()->routeIs('admin.settlements.index') || request()->routeIs('admin.settlements.show')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z"/></x-slot>
                    Settlements
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('admin.settlements.reconciliation') }}" :active="request()->routeIs('admin.settlements.reconciliation')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185ZM9.75 9h.008v.008H9.75V9Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 4.5h.008v.008h-.008V13.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></x-slot>
                    Reconciliation
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('admin.equipment.index') }}" :active="request()->routeIs('admin.equipment.*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3"/></x-slot>
                    Equipment Loans
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('admin.teacher-payments.index') }}" :active="request()->routeIs('admin.teacher-payments.*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></x-slot>
                    Teacher Payments
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('admin.settings.pricing') }}" :active="request()->routeIs('admin.settings.*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></x-slot>
                    Pricing Settings
                </x-sidebar-link>

                <p class="section-label px-3 pb-2 pt-3">Intelligence</p>
                <x-sidebar-link href="{{ route('admin.ai-tools') }}" :active="request()->routeIs('admin.ai-tools*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></x-slot>
                    AI Tools
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('admin.session-reports.index') }}" :active="request()->routeIs('admin.session-reports.*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605"/></x-slot>
                    Session Reports
                </x-sidebar-link>
            @endif

            {{-- Common --}}
            <div class="pt-3 mt-3 border-t border-slate-100 space-y-0.5">
                <p class="section-label px-3 pb-2 pt-1">Account</p>
                <x-sidebar-link href="{{ route('profile.edit') }}" :active="request()->routeIs('profile.*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></x-slot>
                    Profile
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('curriculum.guide') }}" :active="request()->routeIs('curriculum.*')">
                    <x-slot name="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></x-slot>
                    Curriculum Guide
                </x-sidebar-link>
            </div>
            @endauth
        </nav>

        {{-- User footer --}}
        @auth
        <div class="px-4 py-3.5 border-t border-slate-100 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full overflow-hidden flex-shrink-0 shadow-sm">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar_url }}" class="w-full h-full object-cover" alt="{{ auth()->user()->name }}">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-emerald-400 to-emerald-600 text-white text-sm font-bold flex items-center justify-center uppercase">{{ substr(auth()->user()->name, 0, 1) }}</div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-400 truncate capitalize">
                        @if(auth()->user()->role === 'admin') ⚡ Administrator
                        @elseif(auth()->user()->role === 'teacher') 🎓 Teacher
                        @else 📚 Student @endif
                    </p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Sign out" class="p-1.5 rounded-md text-slate-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                    </button>
                </form>
            </div>
        </div>
        @endauth
    </aside>

    {{-- ═══ MAIN CONTENT ═══ --}}
    <div class="flex flex-col flex-1 min-w-0 lg:pl-64">

        {{-- Sticky top bar (mobile + desktop) --}}
        <header class="sticky top-0 z-40 flex items-center gap-3 h-14 bg-white/95 backdrop-blur border-b border-slate-100 px-4 sm:px-6 flex-shrink-0">
            {{-- Hamburger (mobile only) --}}
            <button @click="sidebarOpen = true" class="p-2 -ml-1 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors lg:hidden">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
            </button>

            {{-- Mobile logo --}}
            <a href="{{ route('home') }}" class="flex items-center gap-2 lg:hidden">
                <div class="w-6 h-6 rounded bg-emerald-600 flex items-center justify-center">
                    <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <span class="font-extrabold text-slate-900 text-sm">EduBridge</span>
            </a>

            {{-- Page title slot (desktop) --}}
            <div class="hidden lg:block flex-1 truncate">
                <p class="text-sm font-medium text-slate-500 truncate">
                    @yield('page-title', isset($title) ? $title : '')
                </p>
            </div>

            <div class="ml-auto flex items-center gap-2">
                {{-- Notification bell --}}
                @auth
                @php
                    $unreadCount = auth()->user()->unreadNotifications()->count();
                @endphp
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            class="relative p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
                        @if($unreadCount > 0)
                        <span class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-0.5">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                        </span>
                        @endif
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak
                         class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-50">
                        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                            <p class="font-semibold text-slate-800 text-sm">Notifications</p>
                            @if($unreadCount > 0)
                            <form method="POST" action="{{ route('notifications.read-all') }}" x-data>
                                @csrf
                                <button class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">Mark all read</button>
                            </form>
                            @endif
                        </div>
                        <div class="max-h-72 overflow-y-auto divide-y divide-slate-50">
                            @forelse(auth()->user()->notifications()->latest()->take(10)->get() as $notif)
                            <div class="px-4 py-3 hover:bg-slate-50 transition-colors {{ $notif->read_at ? 'opacity-60' : '' }}">
                                <p class="text-sm text-slate-700 leading-snug">{{ $notif->data['message'] ?? $notif->data['body'] ?? 'New notification' }}</p>
                                <p class="text-xs text-slate-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                            </div>
                            @empty
                            <div class="px-4 py-8 text-center">
                                <p class="text-sm text-slate-400">No notifications yet</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Profile avatar (desktop) --}}
                <a href="{{ route('profile.edit') }}"
                   class="hidden lg:flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-full hover:bg-slate-100 transition-colors">
                    <div class="w-7 h-7 rounded-full overflow-hidden flex-shrink-0 shadow-sm">
                        @if(auth()->user()->avatar)
                            <img src="{{ auth()->user()->avatar_url }}" class="w-full h-full object-cover" alt="{{ auth()->user()->name }}">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-emerald-400 to-emerald-600 text-white text-xs font-bold flex items-center justify-center uppercase">{{ substr(auth()->user()->name, 0, 1) }}</div>
                        @endif
                    </div>
                    <span class="text-sm font-medium text-slate-700 max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                </a>
                @endauth
            </div>
        </header>

        {{-- Flash messages --}}
        @if(session('success') || session('error') || session('info') || session('warning'))
        <div class="px-4 pt-4 sm:px-6 space-y-2" x-data x-init="setTimeout(() => $el.remove(), 6000)">
            @if(session('success'))
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
                <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                <span class="flex-1">{{ session('success') }}</span>
                <button onclick="this.closest('div').remove()" class="text-emerald-400 hover:text-emerald-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            @endif
            @if(session('error'))
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm">
                <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                <span class="flex-1">{{ session('error') }}</span>
                <button onclick="this.closest('div').remove()" class="text-red-400 hover:text-red-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            @endif
            @if(session('info'))
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-sm">
                <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                <span class="flex-1">{{ session('info') }}</span>
                <button onclick="this.closest('div').remove()" class="text-blue-400 hover:text-blue-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            @endif
            @if(session('warning'))
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                <span class="flex-1">{{ session('warning') }}</span>
                <button onclick="this.closest('div').remove()" class="text-amber-400 hover:text-amber-600"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            @endif
        </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 px-4 py-6 sm:px-6 sm:py-8 max-w-7xl mx-auto w-full">
            {{ $slot }}
        </main>

        {{-- Footer --}}
        <footer class="px-6 py-4 border-t border-slate-100 flex-shrink-0">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-slate-400 max-w-7xl mx-auto w-full">
                <p>© {{ date('Y') }} EduBridge Zimbabwe — Empowering Every Learner</p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('curriculum.guide') }}" class="hover:text-slate-600 transition-colors">Curriculum</a>
                    <a href="{{ route('subjects.index') }}" class="hover:text-slate-600 transition-colors">Subjects</a>
                    <a href="{{ route('about') }}" class="hover:text-slate-600 transition-colors">About</a>
                </div>
            </div>
        </footer>
    </div>

    {{-- Mobile backdrop --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 lg:hidden"
        style="display:none;"
    ></div>
</div>
@stack('scripts')
</body>
</html>
