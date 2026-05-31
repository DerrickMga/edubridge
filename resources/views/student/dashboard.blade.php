<x-app-layout>
    <x-slot name="title">My Dashboard</x-slot>

    @php
        $hour       = now()->hour;
        $greeting   = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
        $firstName  = explode(' ', auth()->user()->name)[0];
        $soonSessions = $upcomingSessions->filter(
            fn($s) => $s->scheduled_at->isFuture() && $s->scheduled_at->diffInMinutes(now()) <= 60
        );
    @endphp

    {{-- Starting-Soon Alert ─────────────────────────────────────────────── --}}
    @if($soonSessions->isNotEmpty())
    @php $soon = $soonSessions->first(); @endphp
    <div class="mb-6 flex flex-wrap items-center gap-4 rounded-xl bg-red-600 px-5 py-4 text-white shadow-lg">
        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-white/20 animate-pulse">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
        </span>
        <div class="flex-1">
            <p class="font-bold">Live class starting {{ $soon->scheduled_at->diffForHumans() }}!</p>
            <p class="text-sm text-red-100">{{ $soon->title }} &middot; {{ $soon->course->title ?? '' }}</p>
        </div>
        @if($soon->meeting_url)
        <a href="{{ route('student.live-sessions.join', $soon) }}"
           class="flex-shrink-0 rounded-lg bg-white px-4 py-2 text-sm font-bold text-red-700 hover:bg-red-50 transition-colors">
            Join now
        </a>
        @endif
    </div>
    @endif

    {{-- Page Header ─────────────────────────────────────────────────────── --}}
    <div class="page-header flex flex-wrap items-start justify-between gap-3 mb-6">
        <div>
            <h1 class="page-title">{{ $greeting }}, {{ $firstName }} 👋</h1>
            <p class="page-subtitle">
                @if($enrollments->count())
                    You are enrolled in {{ $enrollments->count() }} {{ Str::plural('course', $enrollments->count()) }}.
                @else
                    Start learning by enrolling in a course.
                @endif
            </p>
        </div>
        <a href="{{ route('courses.index') }}" class="btn-secondary btn-sm hidden sm:flex">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
            Browse courses
        </a>
    </div>

    {{-- Gamification strip ──────────────────────────────────────────────── --}}
    @php
        $xpPct = $stat->level_progress_percent;
        $nextXp = $stat->next_level_xp;
    @endphp
    <div class="card p-4 mb-6 flex flex-wrap items-center gap-4">
        {{-- Level badge --}}
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center flex-shrink-0 shadow">
            <span class="text-white font-extrabold text-lg leading-none">{{ $stat->level }}</span>
        </div>
        {{-- XP bar --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-1">
                <p class="text-sm font-semibold text-slate-700">Level {{ $stat->level }}
                    <span class="text-slate-400 font-normal">· {{ number_format($stat->xp) }} XP</span></p>
                @if($stat->streak_days >= 1)
                <span class="text-sm font-semibold text-orange-500">🔥 {{ $stat->streak_days }}-day streak</span>
                @endif
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: {{ $xpPct }}%"></div>
            </div>
            @if($stat->level < 10)
            <p class="text-xs text-slate-400 mt-0.5">{{ number_format($nextXp - $stat->xp) }} XP to Level {{ $stat->level + 1 }}</p>
            @endif
        </div>
        {{-- Badges count --}}
        @if($recentBadges->count())
        <div class="flex items-center gap-1 flex-shrink-0">
            @foreach($recentBadges as $badge)
            <span title="{{ $badge->name }}" class="text-xl">{{ $badge->icon }}</span>
            @endforeach
        </div>
        @endif
        {{-- Rank + Links --}}
        <div class="flex items-center gap-3 flex-shrink-0">
            <span class="text-sm text-slate-500">#{{ $rank }}</span>
            <a href="{{ route('student.achievements') }}" class="btn-secondary btn-sm">Achievements</a>
            <a href="{{ route('student.leaderboard') }}" class="btn-secondary btn-sm">Leaderboard</a>
        </div>
    </div>

    {{-- Quick Actions ────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

        {{-- AI Companion --}}
        <a href="{{ route('student.companion.index') }}"
           class="group relative overflow-hidden rounded-xl bg-gradient-to-br from-indigo-600 to-violet-700 p-5 text-white hover:shadow-xl transition-all duration-200">
            <div class="absolute -right-3 -top-3 w-20 h-20 rounded-full bg-white/10"></div>
            <div class="absolute -right-1 top-8 w-12 h-12 rounded-full bg-white/5"></div>
            <svg class="w-7 h-7 mb-3 text-indigo-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/>
            </svg>
            <p class="font-bold text-base">Ask Chiedza</p>
            <p class="text-indigo-200 text-xs mt-0.5">Your AI study companion — available 24/7</p>
        </a>

        {{-- Browse Courses --}}
        <a href="{{ route('courses.index') }}"
           class="group card hover:shadow-card-hover p-5 transition-all duration-200 flex flex-col">
            <svg class="w-7 h-7 mb-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <p class="font-bold text-slate-800">Browse Courses</p>
            <p class="text-slate-400 text-xs mt-0.5">Explore all O &amp; A Level subjects</p>
        </a>

        {{-- My Assignments --}}
        <a href="{{ route('student.assignments.index') }}"
           class="group card hover:shadow-card-hover p-5 transition-all duration-200 flex flex-col">
            <svg class="w-7 h-7 mb-3 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
            </svg>
            <p class="font-bold text-slate-800">My Assignments</p>
            <p class="text-slate-400 text-xs mt-0.5">Submit and track your work</p>
        </a>

        {{-- My Schedule --}}
        <div class="card p-5 flex flex-col">
            <svg class="w-7 h-7 mb-3 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
            </svg>
            <p class="font-bold text-slate-800">My Schedule</p>
            <p class="text-slate-400 text-xs mt-0.5 flex-1">
                @if($upcomingSessions->count())
                    <span class="text-emerald-600 font-semibold">{{ $upcomingSessions->count() }}</span> live {{ Str::plural('session', $upcomingSessions->count()) }} upcoming
                @else
                    No sessions scheduled yet
                @endif
            </p>
            @if($upcomingSessions->count())
            <p class="text-xs text-slate-400 mt-2">
                Next: <strong class="text-slate-700">{{ $upcomingSessions->first()->scheduled_at->format('D d M, g:ia') }}</strong>
            </p>
            @endif
        </div>
    </div>

    {{-- Upcoming Live Classes ────────────────────────────────────────────── --}}
    @if($upcomingSessions->isNotEmpty())
    <div class="flex items-center justify-between mb-4">
        <h2 class="section-title">Upcoming Live Classes</h2>
        <span class="badge-blue">{{ $upcomingSessions->count() }} scheduled</span>
    </div>
    <div class="space-y-3 mb-8">
        @foreach($upcomingSessions as $session)
        @php
            $sessionSoon = $session->scheduled_at->isFuture() && $session->scheduled_at->diffInMinutes(now()) <= 60;
            $sessionToday = $session->scheduled_at->isToday();
            $provBg = match($session->provider) {
                'Zoom'     => 'bg-blue-100 text-blue-700',
                'Meet'     => 'bg-green-100 text-green-700',
                'Calendly' => 'bg-teal-100 text-teal-700',
                default    => 'bg-slate-100 text-slate-600',
            };
            $joinBg = match($session->provider) {
                'Zoom'     => 'bg-blue-600 hover:bg-blue-700',
                'Meet'     => 'bg-green-600 hover:bg-green-700',
                'Calendly' => 'bg-teal-600 hover:bg-teal-700',
                default    => 'bg-emerald-600 hover:bg-emerald-700',
            };
        @endphp
        <div class="card p-4 flex flex-wrap items-center gap-4 {{ $sessionSoon ? 'ring-2 ring-emerald-400 ring-offset-1 bg-emerald-50/50' : '' }}">
            {{-- Date block --}}
            <div class="flex-shrink-0 w-12 h-12 rounded-xl {{ $sessionSoon ? 'bg-red-600 text-white' : 'bg-slate-100 text-slate-700' }} flex flex-col items-center justify-center leading-none">
                <span class="text-xs font-medium uppercase">{{ $session->scheduled_at->format('M') }}</span>
                <span class="text-lg font-extrabold leading-none">{{ $session->scheduled_at->format('d') }}</span>
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center flex-wrap gap-2 mb-0.5">
                    <p class="font-semibold text-slate-900">{{ $session->title }}</p>
                    @if($sessionSoon)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse inline-block"></span> Now
                        </span>
                    @elseif($sessionToday)
                        <span class="badge-amber text-xs">Today</span>
                    @endif
                    <span class="badge-slate text-xs {{ $provBg }}">{{ $session->provider }}</span>
                </div>
                <p class="text-xs text-slate-400">
                    {{ $session->course->title ?? '—' }} &middot;
                    {{ $session->scheduled_at->format('g:i a') }} &middot;
                    {{ $session->duration_minutes }} min
                </p>
            </div>

            {{-- Join button --}}
            @if($session->meeting_url)
            <a href="{{ route('student.live-sessions.join', $session) }}"
               class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg {{ $joinBg }} text-white text-sm font-semibold transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/></svg>
                Join class
            </a>
            @else
            <span class="flex-shrink-0 inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-100 text-slate-400 text-xs font-medium cursor-default">
                Link pending
            </span>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    {{-- My Courses ───────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between mb-4">
        <h2 class="section-title">My Courses</h2>
        @if($enrollments->count())
        <span class="badge-slate">{{ $enrollments->count() }} enrolled</span>
        @endif
    </div>

    @if($enrollments->isEmpty())
    <div class="card empty-state">
        <span class="empty-state-icon">📚</span>
        <p class="empty-state-title">No courses yet</p>
        <p class="empty-state-text">Browse our catalogue and enrol in a subject to get started.</p>
        <a href="{{ route('courses.index') }}" class="btn-primary">Browse courses</a>
    </div>
    @else
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-10">
        @foreach($enrollments as $course)
        @php
            $totalLessons = $course->lessons->count();
            $firstLesson  = $course->lessons->first();
            $color = match(strtolower($course->subject ?? '')) {
                'mathematics','maths'         => 'from-blue-500 to-indigo-600',
                'english','english language','english literature' => 'from-purple-500 to-violet-600',
                'physics'                     => 'from-sky-500 to-blue-600',
                'chemistry'                   => 'from-emerald-500 to-teal-600',
                'biology'                     => 'from-green-500 to-emerald-600',
                'history'                     => 'from-amber-400 to-orange-500',
                'geography'                   => 'from-teal-500 to-cyan-600',
                'business studies','commerce','accounting' => 'from-orange-400 to-rose-500',
                'computer science','ict'      => 'from-indigo-500 to-blue-700',
                default                       => 'from-slate-500 to-slate-700',
            };
        @endphp
        <div class="card hover:shadow-card-hover transition-shadow duration-200 overflow-hidden flex flex-col group">
            <div class="h-28 bg-gradient-to-br {{ $color }} flex items-end p-4 relative">
                <span class="text-xs font-bold text-white/80 uppercase tracking-wide">{{ $course->subject }}</span>
                @if($totalLessons > 0)
                <span class="absolute top-3 right-3 bg-white/20 text-white text-xs px-2 py-0.5 rounded-full">{{ $totalLessons }} lessons</span>
                @endif
            </div>
            <div class="p-5 flex flex-col flex-1">
                <h3 class="font-semibold text-slate-900 leading-snug mb-1 line-clamp-2">{{ $course->title }}</h3>
                <p class="text-xs text-slate-400 mb-4">by {{ $course->teacher->name ?? 'EduBridge' }}</p>

                {{-- Progress bar --}}
                <div class="mt-auto space-y-2">
                    <div class="flex items-center justify-between text-xs text-slate-400">
                        <span>Progress</span>
                        <span>0 / {{ $totalLessons }}</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: 0%"></div>
                    </div>

                    @if($firstLesson)
                        <a href="{{ route('student.lessons.show', $firstLesson) }}"
                           class="btn-primary btn-sm justify-center w-full mt-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/></svg>
                            Continue learning
                        </a>
                    @else
                        <span class="btn-secondary btn-sm justify-center w-full mt-1 opacity-50 cursor-default">No lessons yet</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- AI Companion CTA ─────────────────────────────────────────────────── --}}
    <div class="rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-700 p-6 flex flex-col sm:flex-row items-center gap-5 text-white">
        <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-white/15 flex items-center justify-center text-2xl">🤖</div>
        <div class="flex-1 text-center sm:text-left">
            <p class="font-bold text-lg">Stuck on a problem? Ask Chiedza</p>
            <p class="text-indigo-200 text-sm mt-0.5">Your AI tutor can explain any ZIMSEC topic — maths, science, history and more — in simple, clear language.</p>
        </div>
        <a href="{{ route('student.companion.index') }}"
           class="flex-shrink-0 rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-indigo-700 hover:bg-indigo-50 transition-colors">
            Start a chat
        </a>
    </div>

</x-app-layout>
