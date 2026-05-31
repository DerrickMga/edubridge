<x-app-layout>
<x-slot name="title">Lesson Tracker</x-slot>

@php
    $statusColor = fn($s) => match($s) {
        'completed' => 'bg-emerald-100 text-emerald-700',
        'scheduled' => 'bg-blue-100 text-blue-700',
        'ongoing'   => 'bg-violet-100 text-violet-700',
        'cancelled' => 'bg-red-100 text-red-700',
        default     => 'bg-slate-100 text-slate-500',
    };
    $provBg = fn($p) => match($p) {
        'Zoom'     => 'bg-blue-100 text-blue-700',
        'Meet'     => 'bg-green-100 text-green-700',
        'Calendly' => 'bg-teal-100 text-teal-700',
        default    => 'bg-slate-100 text-slate-600',
    };
@endphp

{{-- Flash --}}
@if(session('success'))
<div class="mb-5 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- Page Header --}}
<div class="page-header flex flex-wrap items-start justify-between gap-3 mb-6">
    <div>
        <h1 class="page-title">Lesson Tracker</h1>
        <p class="page-subtitle">Track all your scheduled sessions, log hours, record attendance, and update notes.</p>
    </div>
    <a href="{{ route('teacher.live-sessions.create') }}"
       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Schedule Session
    </a>
</div>

{{-- KPI Summary Strip --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-8">
    <div class="card p-4 text-center">
        <p class="text-2xl font-bold text-slate-900">{{ $stats['total_sessions'] }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Total sessions</p>
    </div>
    <div class="card p-4 text-center">
        <p class="text-2xl font-bold text-emerald-600">{{ $stats['completed'] }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Completed</p>
    </div>
    <div class="card p-4 text-center">
        <p class="text-2xl font-bold text-blue-600">{{ $stats['upcoming'] }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Upcoming</p>
    </div>
    @if($stats['unlogged'] > 0)
    <div class="card p-4 text-center border-amber-200 bg-amber-50">
        <p class="text-2xl font-bold text-amber-600">{{ $stats['unlogged'] }}</p>
        <p class="text-xs text-amber-500 mt-0.5">Unlogged ⚠</p>
    </div>
    @else
    <div class="card p-4 text-center">
        <p class="text-2xl font-bold text-slate-900">{{ $stats['unlogged'] }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Unlogged</p>
    </div>
    @endif
    <div class="card p-4 text-center">
        <p class="text-2xl font-bold text-violet-600">{{ number_format($stats['total_attendance']) }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Total attendance</p>
    </div>
    <div class="card p-4 text-center">
        <p class="text-2xl font-bold text-slate-900">{{ $stats['total_hours'] }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Hours logged</p>
    </div>
</div>

{{-- Two-column layout: course sidebar + session list --}}
<div class="grid lg:grid-cols-4 gap-6">

    {{-- ── Left: Course breakdown ── --}}
    <div class="lg:col-span-1 space-y-3">
        <h2 class="text-xs font-semibold uppercase tracking-wide text-slate-500 px-1">Courses</h2>

        {{-- All courses filter --}}
        <a href="{{ route('teacher.lesson-tracker') }}"
           class="flex items-center justify-between px-3 py-2.5 rounded-xl border text-sm font-medium transition-colors
                  {{ ! $courseFilter ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
            <span>All courses</span>
            <span class="{{ ! $courseFilter ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }} text-xs px-1.5 py-0.5 rounded-full font-bold">
                {{ $allSessions->count() }}
            </span>
        </a>

        @foreach($courseStats as $cs)
        <a href="{{ route('teacher.lesson-tracker', ['course' => $cs['course']->id]) }}"
           class="block px-3 py-3 rounded-xl border text-sm transition-colors
                  {{ $courseFilter == $cs['course']->id ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-700 border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
            <div class="flex items-start justify-between gap-2 mb-2">
                <p class="font-semibold leading-snug line-clamp-2">{{ $cs['course']->title }}</p>
                @if($cs['unlogged'] > 0)
                <span class="{{ $courseFilter == $cs['course']->id ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-700' }} text-[10px] px-1.5 py-0.5 rounded-full font-bold shrink-0">
                    {{ $cs['unlogged'] }} unlogged
                </span>
                @endif
            </div>
            <p class="{{ $courseFilter == $cs['course']->id ? 'text-blue-100' : 'text-slate-400' }} text-xs mb-2">
                {{ $cs['course']->subject ?? 'General' }} · {{ $cs['course']->grade_level ?? 'All levels' }}
            </p>
            <div class="flex items-center justify-between text-xs {{ $courseFilter == $cs['course']->id ? 'text-blue-100' : 'text-slate-500' }} mb-1.5">
                <span>{{ $cs['sessions_done'] }}/{{ $cs['sessions_total'] }} sessions done</span>
                <span>{{ $cs['pct_done'] }}%</span>
            </div>
            <div class="{{ $courseFilter == $cs['course']->id ? 'bg-blue-500' : 'bg-slate-100' }} rounded-full h-1.5 overflow-hidden">
                <div class="{{ $courseFilter == $cs['course']->id ? 'bg-white' : 'bg-emerald-500' }} h-1.5 rounded-full"
                     style="width: {{ $cs['pct_done'] }}%"></div>
            </div>
            <div class="flex items-center gap-3 mt-2 text-xs {{ $courseFilter == $cs['course']->id ? 'text-blue-100' : 'text-slate-400' }}">
                <span>👥 {{ $cs['course']->enrollments_count }} students</span>
                <span>📋 {{ $cs['course']->lessons_count }} lessons</span>
            </div>
        </a>
        @endforeach

        @if($courses->isEmpty())
        <div class="text-center py-6 text-slate-400 text-sm border border-dashed border-slate-200 rounded-xl">
            No courses assigned yet.
            <a href="{{ route('teacher.courses.browse') }}" class="block mt-2 text-blue-600 text-xs hover:underline">Browse courses →</a>
        </div>
        @endif
    </div>

    {{-- ── Right: Session list ── --}}
    <div class="lg:col-span-3 space-y-4">

        @if($courseFilter)
        @php $filterCourse = $courses->firstWhere('id', (int) $courseFilter); @endphp
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-700">
                {{ $filterCourse?->title ?? 'Course' }}
                <span class="ml-1 text-slate-400 font-normal">— {{ $sessions->count() }} session(s)</span>
            </h2>
            <a href="{{ route('teacher.lesson-tracker') }}" class="text-xs text-slate-400 hover:text-slate-600">Clear filter ×</a>
        </div>
        @else
        <h2 class="text-sm font-semibold text-slate-700">All sessions <span class="text-slate-400 font-normal">({{ $sessions->count() }})</span></h2>
        @endif

        @if($sessions->isEmpty())
        <div class="card p-10 text-center">
            <p class="text-slate-400 text-sm mb-3">No sessions found{{ $courseFilter ? ' for this course' : '' }}.</p>
            <a href="{{ route('teacher.live-sessions.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors">
                Schedule your first session
            </a>
        </div>
        @endif

        @foreach($sessions as $session)
        @php
            $isPast    = $session->scheduled_at < now();
            $log       = $session->sessionLog;
            $pay       = $session->paymentItem;
            $isSoon    = ! $isPast && $session->scheduled_at->diffInMinutes(now()) <= 60;
            $attendCount = $session->attendances_count;
        @endphp
        <div x-data="{ logOpen: false, notesOpen: false }"
             class="card overflow-hidden {{ $isSoon ? 'ring-2 ring-blue-400 ring-offset-1' : '' }}
                    {{ ($isPast && !$log && $session->status !== 'cancelled') ? 'border-amber-200' : '' }}">

            {{-- Session header --}}
            <div class="flex flex-wrap items-start gap-3 p-4">

                {{-- Date block --}}
                <div class="shrink-0 w-12 h-12 rounded-xl {{ $isPast ? 'bg-slate-100' : 'bg-blue-100' }} flex flex-col items-center justify-center">
                    <span class="text-[10px] font-semibold uppercase {{ $isPast ? 'text-slate-400' : 'text-blue-500' }}">{{ $session->scheduled_at->format('M') }}</span>
                    <span class="text-lg font-extrabold {{ $isPast ? 'text-slate-700' : 'text-blue-700' }}">{{ $session->scheduled_at->format('d') }}</span>
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <p class="font-semibold text-slate-900 text-sm">{{ $session->title }}</p>

                        {{-- Status badge --}}
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $statusColor($session->status) }} capitalize">
                            {{ $session->status }}
                        </span>

                        {{-- Provider badge --}}
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $provBg($session->provider) }}">{{ $session->provider }}</span>

                        @if($isSoon)
                        <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700 font-semibold">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse inline-block"></span> Starting soon
                        </span>
                        @endif

                        @if($pay)
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $pay->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : ($pay->status === 'approved' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">
                            ${{ number_format($pay->total_usd, 2) }} — {{ ucfirst($pay->status) }}
                        </span>
                        @endif
                    </div>

                    <p class="text-xs text-slate-500">
                        @if(! $courseFilter)
                        <span class="font-medium text-slate-600">{{ $session->course?->title ?? '—' }}</span> ·
                        @endif
                        {{ $session->scheduled_at->format('D d M Y') }} at
                        <strong class="text-slate-700">{{ $session->scheduled_at->format('g:i a') }}</strong>
                        · {{ $session->duration_minutes }} min planned
                        @if($session->meeting_id) · ID: <span class="font-mono">{{ $session->meeting_id }}</span>@endif
                    </p>

                    {{-- Attendance row --}}
                    <div class="flex flex-wrap items-center gap-3 mt-2 text-xs">
                        <span class="flex items-center gap-1 {{ $attendCount > 0 ? 'text-emerald-700 font-semibold' : 'text-slate-400' }}">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z"/></svg>
                            {{ $attendCount }} attended
                        </span>
                        @if($log)
                        <span class="flex items-center gap-1 text-emerald-600">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            Logged: {{ $log->actual_duration_minutes }}min · {{ $log->actual_student_count }} students
                            @if($log->notes) · <em class="text-slate-400">{{ Str::limit($log->notes, 50) }}</em>@endif
                        </span>
                        @elseif($isPast && $session->status !== 'cancelled')
                        <span class="text-amber-600 font-semibold flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                            Hours not yet logged
                        </span>
                        @endif

                        @if($session->teacher_notes)
                        <span class="text-slate-400 italic">📝 Notes saved</span>
                        @endif
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="flex items-center gap-1.5 shrink-0 flex-wrap">
                    {{-- Notes toggle --}}
                    <button @click="notesOpen = !notesOpen"
                            class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-600 transition-colors">
                        📝 Notes
                    </button>

                    {{-- Log hours (past unlogged only) --}}
                    @if($isPast && !$log && $session->status !== 'cancelled')
                    <button @click="logOpen = !logOpen"
                            class="px-2.5 py-1.5 text-xs rounded-lg bg-amber-500 hover:bg-amber-600 text-white font-semibold transition-colors">
                        Log hours
                    </button>
                    @endif

                    {{-- Mark complete (past, not yet completed) --}}
                    @if($isPast && $session->status === 'scheduled')
                    <form action="{{ route('teacher.sessions.complete', $session) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="px-2.5 py-1.5 text-xs rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold transition-colors">
                            Mark done
                        </button>
                    </form>
                    @endif

                    {{-- Join/Start link (upcoming only) --}}
                    @if(! $isPast)
                        @if($session->start_url)
                        <a href="{{ $session->start_url }}" target="_blank" rel="noopener"
                           class="px-2.5 py-1.5 text-xs rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold transition-colors">
                            Start
                        </a>
                        @elseif($session->meeting_url)
                        <a href="{{ $session->meeting_url }}" target="_blank" rel="noopener"
                           class="px-2.5 py-1.5 text-xs rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold transition-colors">
                            Start
                        </a>
                        @endif
                        <a href="{{ route('teacher.live-sessions.edit', $session) }}"
                           class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-600 transition-colors">
                            Edit
                        </a>
                    @endif
                </div>
            </div>

            {{-- ── Attendance list (collapsible if has attendances) ── --}}
            @if($attendCount > 0)
            <div x-data="{ attOpen: false }">
                <button @click="attOpen = !attOpen"
                        class="w-full px-4 py-2 text-xs text-left text-slate-500 bg-slate-50 border-t border-slate-100 hover:bg-slate-100 transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z"/></svg>
                    View {{ $attendCount }} attendee(s)
                    <svg class="w-3 h-3 ml-auto transition-transform" :class="attOpen && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                </button>
                <div x-show="attOpen" x-cloak class="px-4 py-3 bg-slate-50 border-t border-slate-100">
                    <div class="flex flex-wrap gap-2">
                        @foreach($session->attendances as $att)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-xs text-slate-700">
                            @if($att->user?->avatar)
                            <img src="{{ $att->user->avatar }}" class="w-4 h-4 rounded-full object-cover" alt="" />
                            @else
                            <span class="w-4 h-4 rounded-full bg-slate-300 flex items-center justify-center text-[8px] font-bold text-white">
                                {{ strtoupper(substr($att->user?->name ?? '?', 0, 1)) }}
                            </span>
                            @endif
                            {{ $att->user?->name ?? 'Unknown' }}
                            <span class="text-slate-400">{{ $att->joined_at?->format('g:i a') }}</span>
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            {{-- ── Log Hours form ── --}}
            <div x-show="logOpen" x-cloak class="border-t border-amber-200 bg-amber-50 px-4 py-4">
                <p class="text-xs font-semibold text-amber-800 mb-3">Log hours for this session</p>
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
                                   value="{{ $attendCount }}"
                                   class="form-input text-sm w-full" required>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Notes (optional)</label>
                        <textarea name="notes" rows="2" maxlength="2000"
                                  placeholder="Topics covered, issues, student engagement…"
                                  class="form-input text-sm w-full resize-none">{{ $session->teacher_notes }}</textarea>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit"
                                class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors">
                            Submit log &amp; claim payment
                        </button>
                        <button type="button" @click="logOpen = false"
                                class="px-4 py-2 rounded-lg border border-slate-200 hover:bg-white text-slate-600 text-sm transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>

            {{-- ── Session Notes form ── --}}
            <div x-show="notesOpen" x-cloak class="border-t border-slate-100 bg-slate-50 px-4 py-4">
                <p class="text-xs font-semibold text-slate-600 mb-2">Session notes / lesson summary</p>
                <form action="{{ route('teacher.sessions.notes', $session) }}" method="POST" class="space-y-2">
                    @csrf
                    @method('PATCH')
                    <textarea name="teacher_notes" rows="3" maxlength="2000"
                              placeholder="What topics were covered? Any issues? Next steps for students…"
                              class="form-input text-sm w-full resize-none">{{ $session->teacher_notes }}</textarea>
                    <div class="flex items-center gap-2">
                        <button type="submit"
                                class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition-colors">
                            Save notes
                        </button>
                        <button type="button" @click="notesOpen = false"
                                class="px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-white text-slate-600 text-xs transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>

</x-app-layout>
