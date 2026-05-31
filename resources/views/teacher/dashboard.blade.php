<x-app-layout>
    <x-slot name="title">Teacher Dashboard</x-slot>

    {{-- Flash --}}
    @if(session('success'))
    <div class="mb-6 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
        <svg class="w-4 h-4 text-emerald-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    {{-- Page Header ──────────────────────────────────────────────────────── --}}
    <div class="page-header flex flex-wrap items-start justify-between gap-3 mb-6">
        <div>
            @php $hour = now()->hour; @endphp
            <h1 class="page-title">{{ $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening') }}, {{ explode(' ', auth()->user()->name)[0] }} 🎓</h1>
            <p class="page-subtitle">Manage your courses, schedule live classes, and track your students.</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('teacher.live-sessions.create') }}"
               class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                Schedule Class
            </a>
            <a href="{{ route('teacher.courses.browse') }}" class="btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776"/></svg>
                Browse Courses
            </a>
        </div>
    </div>

    {{-- KPI Stats ─────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="stat-card border-l-4 border-emerald-500">
            <p class="stat-value text-emerald-700">{{ $courses->count() }}</p>
            <p class="stat-label">Courses</p>
        </div>
        <div class="stat-card border-l-4 border-blue-400">
            <p class="stat-value text-blue-700">{{ $totalStudents }}</p>
            <p class="stat-label">Total students</p>
        </div>
        <div class="stat-card border-l-4 border-violet-400">
            <p class="stat-value text-violet-700">${{ number_format($earnings, 2) }}</p>
            <p class="stat-label">Earnings (USD)</p>
        </div>
        <div class="stat-card border-l-4 border-amber-400">
            <p class="stat-value text-amber-600">{{ $upcoming->count() }}</p>
            <p class="stat-label">Upcoming sessions</p>
        </div>
    </div>

    {{-- Quick Action Hub ──────────────────────────────────────────────────── --}}
    <h2 class="section-title mb-3">Quick Actions</h2>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8">

        {{-- Schedule Class --}}
        <a href="{{ route('teacher.live-sessions.create') }}"
           class="group card card-hover p-4 flex flex-col items-center gap-2.5 text-center">
            <div class="w-11 h-11 rounded-2xl bg-blue-100 flex items-center justify-center group-hover:bg-blue-600 transition-colors">
                <svg class="w-5 h-5 text-blue-600 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-sm text-slate-800">Schedule Class</p>
                <p class="text-xs text-slate-400 mt-0.5">Zoom · Meet · Calendly</p>
            </div>
        </a>

        {{-- Lesson Planner --}}
        <a href="{{ route('teacher.courses.index') }}"
           class="group card card-hover p-4 flex flex-col items-center gap-2.5 text-center">
            <div class="w-11 h-11 rounded-2xl bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-600 transition-colors">
                <svg class="w-5 h-5 text-emerald-600 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-sm text-slate-800">Lesson Planner</p>
                <p class="text-xs text-slate-400 mt-0.5">Create &amp; organise</p>
            </div>
        </a>

        {{-- Browse Courses --}}
        <a href="{{ route('teacher.courses.browse') }}"
           class="group card card-hover p-4 flex flex-col items-center gap-2.5 text-center">
            <div class="w-11 h-11 rounded-2xl bg-violet-100 flex items-center justify-center group-hover:bg-violet-600 transition-colors">
                <svg class="w-5 h-5 text-violet-600 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-sm text-slate-800">Browse Courses</p>
                <p class="text-xs text-slate-400 mt-0.5">Choose your courses</p>
            </div>
        </a>

        {{-- ZIMSEC Guide --}}
        <a href="{{ route('curriculum.guide') }}"
           class="group card card-hover p-4 flex flex-col items-center gap-2.5 text-center">
            <div class="w-11 h-11 rounded-2xl bg-amber-100 flex items-center justify-center group-hover:bg-amber-500 transition-colors">
                <svg class="w-5 h-5 text-amber-500 group-hover:text-white transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
                </svg>
            </div>
            <div>
                <p class="font-semibold text-sm text-slate-800">ZIMSEC Guide</p>
                <p class="text-xs text-slate-400 mt-0.5">Curriculum benchmarks</p>
            </div>
        </a>
    </div>

    {{-- Instant Launch + Calendly ────────────────────────────────────────── --}}
    <div class="grid sm:grid-cols-2 gap-4 mb-8">

        {{-- Instant launch --}}
        <div class="card p-5">
            <p class="font-semibold text-slate-800 mb-3 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse inline-block"></span>
                Start a meeting now
            </p>
            <div class="flex flex-col gap-2">
                <a href="https://meet.google.com/new" target="_blank" rel="noopener"
                   class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-green-400 hover:bg-green-50 transition-all group">
                    <span class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center text-sm flex-shrink-0 group-hover:bg-green-500 transition-colors">
                        <svg class="w-4 h-4 text-green-600 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M22 7.99L18 11V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4l4 3.01V8a.5.5 0 0 0-.5-.5.49.49 0 0 0-.25.06L22 7.99z"/></svg>
                    </span>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-slate-700 group-hover:text-green-700">Google Meet</p>
                        <p class="text-xs text-slate-400">Instant — no download required</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                </a>
                <a href="https://zoom.us/start/videomeeting" target="_blank" rel="noopener"
                   class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-blue-400 hover:bg-blue-50 transition-all group">
                    <span class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-sm flex-shrink-0 group-hover:bg-blue-500 transition-colors">
                        <svg class="w-4 h-4 text-blue-600 group-hover:text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12c0 6.627-5.373 12-12 12S0 18.627 0 12 5.373 0 12 0s12 5.373 12 12zm-6.5-5.5H8a1.5 1.5 0 0 0-1.5 1.5v5a1.5 1.5 0 0 0 1.5 1.5h7a1.5 1.5 0 0 0 1.5-1.5v-1.25l2.5 1.75V9l-2.5 1.75V8A1.5 1.5 0 0 0 17.5 6.5z"/></svg>
                    </span>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-slate-700 group-hover:text-blue-700">Zoom</p>
                        <p class="text-xs text-slate-400">Start an instant meeting</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-300 group-hover:text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                </a>
            </div>
        </div>

        {{-- Calendly integration (Alpine + localStorage) --}}
        <div class="card p-5"
             x-data="{
                 url: localStorage.getItem('eb_calendly_url') || '',
                 save() { localStorage.setItem('eb_calendly_url', this.url) },
                 open() { if(this.url) window.open(this.url, '_blank') }
             }">
            <p class="font-semibold text-slate-800 mb-1 flex items-center gap-2">
                <svg class="w-4 h-4 text-teal-500" fill="currentColor" viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5C3.9 4 3 4.9 3 6v14a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/></svg>
                Calendly Booking Page
            </p>
            <p class="text-xs text-slate-400 mb-3">Let students book 1-on-1 sessions with you.</p>
            <div class="flex gap-2">
                <input type="url" x-model="url" @input.debounce.500ms="save()"
                       placeholder="https://calendly.com/your-name/session"
                       class="form-input text-sm flex-1 min-w-0" />
                <button @click="open()" :disabled="!url"
                        class="flex-shrink-0 px-3 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-semibold transition-colors">
                    Open
                </button>
            </div>
            <p class="text-xs text-slate-400 mt-2">Saved locally in your browser — paste your Calendly event URL above.</p>
        </div>
    </div>

    {{-- Upcoming Live Sessions ────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-4">
        <h2 class="section-title">Scheduled Live Sessions</h2>
        <a href="{{ route('teacher.live-sessions.create') }}"
           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors">
            + Schedule
        </a>
    </div>

    @if($upcoming->isEmpty())
    <div class="card mb-8 p-6 flex flex-col sm:flex-row items-center gap-5 bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-100">
        <div class="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-7 h-7 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
        </div>
        <div class="flex-1 text-center sm:text-left">
            <p class="font-semibold text-slate-800">No live sessions scheduled yet</p>
            <p class="text-sm text-slate-500 mt-0.5">Schedule a Google Meet or Zoom session — your students will get a Join button in their dashboard.</p>
        </div>
        <a href="{{ route('teacher.live-sessions.create') }}"
           class="flex-shrink-0 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors">
            Schedule now
        </a>
    </div>
    @else
    <div class="space-y-3 mb-8">
        @foreach($upcoming as $session)
        @php
            $isSoon   = $session->scheduled_at->isFuture() && $session->scheduled_at->diffInMinutes(now()) <= 60;
            $isToday  = $session->scheduled_at->isToday();
            $provBg   = match($session->provider) { 'Zoom' => 'bg-blue-100 text-blue-700', 'Meet' => 'bg-green-100 text-green-700', 'Calendly' => 'bg-teal-100 text-teal-700', default => 'bg-slate-100 text-slate-600' };
            $btnColor = match($session->provider) { 'Zoom' => 'bg-blue-600 hover:bg-blue-700', 'Meet' => 'bg-green-600 hover:bg-green-700', 'Calendly' => 'bg-teal-600 hover:bg-teal-700', default => 'bg-slate-600 hover:bg-slate-700' };
        @endphp
        <div class="card p-4 flex flex-wrap items-center gap-4 {{ $isSoon ? 'ring-2 ring-blue-400 ring-offset-1' : '' }}">
            {{-- Provider icon --}}
            <div class="w-10 h-10 rounded-xl {{ $provBg }} flex items-center justify-center flex-shrink-0">
                @if($session->provider === 'Zoom')
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12c0 6.627-5.373 12-12 12S0 18.627 0 12 5.373 0 12 0s12 5.373 12 12zm-6.5-5.5H8a1.5 1.5 0 0 0-1.5 1.5v5a1.5 1.5 0 0 0 1.5 1.5h7a1.5 1.5 0 0 0 1.5-1.5v-1.25l2.5 1.75V9l-2.5 1.75V8A1.5 1.5 0 0 0 17.5 6.5z"/></svg>
                @elseif($session->provider === 'Meet')
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M22 7.99L18 11V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-4l4 3.01V8a.5.5 0 0 0-.5-.5.49.49 0 0 0-.25.06L22 7.99z"/></svg>
                @elseif($session->provider === 'Calendly')
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 4h-1V2h-2v2H8V2H6v2H5C3.9 4 3 4.9 3 6v14a2 2 0 0 0 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zm0-12H5V6h14v2z"/></svg>
                @else
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/></svg>
                @endif
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-0.5">
                    <p class="font-semibold text-slate-900">{{ $session->title }}</p>
                    @if($isSoon)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse inline-block"></span> Starting soon
                        </span>
                    @elseif($isToday)
                        <span class="badge-blue text-xs">Today</span>
                    @endif
                </div>
                <p class="text-xs text-slate-400">
                    {{ $session->course->title ?? '—' }} &middot;
                    {{ $session->scheduled_at->format('D, d M Y') }} at <strong class="text-slate-700">{{ $session->scheduled_at->format('g:i a') }}</strong>
                    &middot; {{ $session->duration_minutes }} min
                    @if($session->meeting_id)
                        &middot; ID: <span class="font-mono">{{ $session->meeting_id }}</span>
                    @endif
                </p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('teacher.live-sessions.edit', $session) }}"
                   class="btn-secondary btn-sm">Edit</a>

                @if($session->start_url)
                    {{-- Zoom host start link (only visible to teacher) --}}
                    <a href="{{ $session->start_url }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg {{ $btnColor }} text-white text-sm font-semibold transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/></svg>
                        Start Zoom
                    </a>
                @elseif($session->meeting_url)
                    <a href="{{ $session->meeting_url }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg {{ $btnColor }} text-white text-sm font-semibold transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/></svg>
                        Start
                    </a>
                @else
                    {{-- Dropdown to pick a platform --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/></svg>
                            Start
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute right-0 mt-1 w-48 bg-white rounded-xl shadow-lg border border-slate-100 py-1 z-20">
                            <a href="https://meet.google.com/new" target="_blank" rel="noopener"
                               class="flex items-center gap-2.5 px-4 py-2.5 text-sm hover:bg-slate-50 text-slate-700">
                                <span class="w-4 h-4 rounded-full bg-green-500 flex-shrink-0"></span> Google Meet
                            </a>
                            <a href="https://zoom.us/start/videomeeting" target="_blank" rel="noopener"
                               class="flex items-center gap-2.5 px-4 py-2.5 text-sm hover:bg-slate-50 text-slate-700">
                                <span class="w-4 h-4 rounded-full bg-blue-500 flex-shrink-0"></span> Zoom
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Past Sessions — Log Your Hours ─────────────────────────────────── --}}
    @if($pastSessions->isNotEmpty())
    <div class="flex items-center justify-between mb-4 mt-8">
        <div>
            <h2 class="section-title">Past Sessions — Log Your Hours</h2>
            @if($pendingPayments > 0)
            <p class="text-sm text-emerald-600 font-medium mt-0.5">
                ${{ number_format($pendingPayments, 2) }} approved &amp; awaiting payment
            </p>
            @endif
        </div>
    </div>
    <div class="space-y-3 mb-8">
        @foreach($pastSessions as $session)
        @php $log = $session->sessionLog; $pay = $session->paymentItem; $ai = $session->aiReport; @endphp
        <div class="card p-4">
            <div class="flex flex-wrap items-start gap-4">
                {{-- Date block --}}
                <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-slate-100 flex flex-col items-center justify-center">
                    <span class="text-xs font-medium uppercase text-slate-500">{{ $session->scheduled_at->format('M') }}</span>
                    <span class="text-lg font-extrabold text-slate-800">{{ $session->scheduled_at->format('d') }}</span>
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center flex-wrap gap-2 mb-1">
                        <p class="font-semibold text-slate-900">{{ $session->title }}</p>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $session->provider === 'Zoom' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ $session->provider }}
                        </span>
                        @if($pay)
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                {{ $pay->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : ($pay->status === 'approved' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">
                                ${{ number_format($pay->total_usd, 2) }} — {{ ucfirst($pay->status) }}
                            </span>
                        @endif
                        @if($ai && $ai->processed_at)
                            <span class="text-xs px-2 py-0.5 rounded-full bg-violet-100 text-violet-700 font-medium">AI ✓</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400">
                        {{ $session->course->title ?? '—' }} ·
                        {{ $session->scheduled_at->format('D d M Y g:i a') }} ·
                        {{ $session->duration_minutes }} min planned
                    </p>
                    @if($log)
                    <p class="text-xs text-slate-500 mt-1">
                        Logged: {{ $log->actual_duration_minutes }} min · {{ $log->actual_student_count }} students
                        @if($log->notes) — <em>{{ Str::limit($log->notes, 60) }}</em>@endif
                    </p>
                    @endif
                </div>

                {{-- Action: log form or status --}}
                @if(! $log)
                <div x-data="{ open: false }" class="flex-shrink-0">
                    <button @click="open = !open"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
                        Log hours
                    </button>
                    <div x-show="open" x-cloak class="mt-3 p-4 bg-slate-50 rounded-xl border border-slate-200">
                        <form action="{{ route('teacher.sessions.log', $session) }}" method="POST" class="space-y-3">
                            @csrf
                            <div class="grid sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="text-xs font-semibold text-slate-600 block mb-1">Actual duration (min)</label>
                                    <input type="number" name="actual_duration_minutes" min="1" max="720"
                                           value="{{ $session->duration_minutes }}"
                                           class="form-input text-sm w-full" required>
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-slate-600 block mb-1">Students in attendance</label>
                                    <input type="number" name="actual_student_count" min="0" max="2000"
                                           value="{{ $session->attendances->count() }}"
                                           class="form-input text-sm w-full" required>
                                </div>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600 block mb-1">Notes (optional)</label>
                                <textarea name="notes" rows="2" maxlength="2000"
                                          placeholder="Topics covered, issues, student engagement…"
                                          class="form-input text-sm w-full resize-none"></textarea>
                            </div>
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors">
                                Submit log &amp; claim payment
                            </button>
                        </form>
                    </div>
                </div>
                @else
                <span class="flex-shrink-0 text-xs text-emerald-600 font-semibold">✓ Logged</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- My Courses ───────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-4">
        <h2 class="section-title">My Courses</h2>
        <a href="{{ route('teacher.courses.browse') }}" class="btn-primary btn-sm">Browse Courses</a>
    </div>

    @if($courses->isEmpty())
    <div class="card empty-state">
        <span class="empty-state-icon">🏫</span>
        <p class="empty-state-title">No courses assigned yet</p>
        <p class="empty-state-text">Browse the ZIMSEC catalogue and claim a course to start teaching.</p>
        <a href="{{ route('teacher.courses.browse') }}" class="btn-primary">Browse the catalogue</a>
    </div>
    @else
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($courses as $course)
        @php
            $color = match(strtolower($course->subject ?? '')) {
                'mathematics','maths' => 'from-blue-500 to-indigo-600',
                'english','english language','english literature' => 'from-purple-500 to-violet-600',
                'physics' => 'from-sky-500 to-blue-600',
                'chemistry' => 'from-emerald-500 to-teal-600',
                'biology' => 'from-green-500 to-emerald-600',
                'history' => 'from-amber-400 to-orange-500',
                'geography' => 'from-teal-500 to-cyan-600',
                'business studies','commerce','accounting' => 'from-orange-400 to-rose-500',
                'computer science','ict' => 'from-indigo-500 to-blue-700',
                default => 'from-slate-400 to-slate-600',
            };
        @endphp
        <a href="{{ route('teacher.courses.show', $course) }}"
           class="card hover:shadow-card-hover transition-all duration-200 overflow-hidden block group">
            <div class="h-24 bg-gradient-to-br {{ $color }} relative flex items-end p-4">
                <span class="text-xs font-bold text-white/80 uppercase tracking-wide">{{ $course->subject }}</span>
                <span class="absolute top-3 right-3 {{ $course->status === 'published' ? 'bg-white/20 text-white' : 'bg-black/20 text-white/80' }} text-xs px-2 py-0.5 rounded-full font-medium">{{ $course->status }}</span>
            </div>
            <div class="p-4">
                <h3 class="font-semibold text-slate-900 leading-snug mb-1 group-hover:text-emerald-700 transition-colors line-clamp-2">{{ $course->title }}</h3>
                <p class="text-xs text-slate-400 mb-3">{{ $course->grade_level }}</p>
                <div class="flex items-center gap-3 text-xs text-slate-500 border-t border-slate-100 pt-3">
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z"/></svg>
                        {{ $course->enrollments_count }} students
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/></svg>
                        {{ $course->lessons_count ?? 0 }} lessons
                    </span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endif

</x-app-layout>
