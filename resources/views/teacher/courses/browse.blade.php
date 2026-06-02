<x-app-layout>
    <x-slot name="title">Browse Courses</x-slot>

    <div class="page-header flex items-center justify-between gap-4">
        <div>
            <a href="{{ route('teacher.courses.index') }}"
               class="inline-flex items-center gap-1 text-sm text-slate-400 hover:text-slate-700 mb-1 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                My Courses
            </a>
            <h1 class="page-title">Browse Courses</h1>
            <p class="page-subtitle">Choose a course from the ZIMSEC catalogue to teach. Available courses are open for you to claim.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
        <svg class="w-4 h-4 flex-shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @php
        $oLevel  = $courses->filter(fn($c) => str_contains($c->grade_level ?? '', 'O-Level'));
        $aLevel  = $courses->filter(fn($c) => str_contains($c->grade_level ?? '', 'A-Level'));
        $other   = $courses->filter(fn($c) => !str_contains($c->grade_level ?? '', 'O-Level') && !str_contains($c->grade_level ?? '', 'A-Level'));

        $subjectColors = [
            'mathematics' => 'from-blue-500 to-indigo-600',
            'further mathematics' => 'from-indigo-500 to-violet-600',
            'english language' => 'from-purple-500 to-violet-600',
            'english literature' => 'from-violet-500 to-fuchsia-600',
            'physics' => 'from-sky-500 to-blue-600',
            'chemistry' => 'from-emerald-500 to-teal-600',
            'biology' => 'from-green-500 to-emerald-600',
            'combined science' => 'from-teal-400 to-cyan-600',
            'history' => 'from-amber-400 to-orange-500',
            'geography' => 'from-teal-500 to-cyan-600',
            'business studies' => 'from-orange-400 to-rose-500',
            'commerce' => 'from-rose-400 to-pink-500',
            'accounting' => 'from-amber-500 to-yellow-600',
            'economics' => 'from-lime-500 to-green-600',
            'computer science' => 'from-cyan-500 to-blue-500',
            'agriculture' => 'from-green-400 to-lime-500',
            'shona' => 'from-red-400 to-rose-600',
            'ndebele' => 'from-red-500 to-orange-500',
            'default' => 'from-slate-400 to-slate-600',
        ];
    @endphp

    @foreach([['O-Level', $oLevel], ['A-Level', $aLevel], ['Other', $other]] as [$tier, $tierCourses])
    @if($tierCourses->isNotEmpty())
    <h2 class="section-title mb-3 mt-6">{{ $tier }}</h2>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-2">
        @foreach($tierCourses as $course)
        @php
            $key   = strtolower($course->subject ?? '');
            $color = $subjectColors[$key] ?? $subjectColors['default'];
            $mine  = $course->isMine;
        @endphp
        <div class="card overflow-hidden">
            <div class="h-20 bg-gradient-to-br {{ $color }} relative">
                <div class="h-full flex items-end justify-between p-3 pb-2">
                    <span class="text-xs font-bold text-white/80 uppercase tracking-wide">{{ $course->subject }}</span>
                    @if($mine)
                    <span class="text-xs font-bold bg-white/20 text-white rounded-full px-2 py-0.5">Teaching</span>
                    @else
                    <span class="text-xs font-bold bg-white/20 text-white rounded-full px-2 py-0.5">{{ $course->teachers_count }} {{ Str::plural('teacher', $course->teachers_count) }}</span>
                    @endif
                </div>
            </div>
            <div class="p-4">
                <h3 class="font-semibold text-slate-900 leading-snug mb-0.5">{{ $course->title }}</h3>
                <p class="text-xs text-slate-400 mb-3">{{ $course->grade_level }} · {{ $course->lessons_count }} lessons · {{ $course->enrollments_count }} students</p>

                @if($course->description)
                <p class="text-xs text-slate-500 mb-3 line-clamp-2">{{ $course->description }}</p>
                @endif

                @if($mine)
                <div class="flex gap-2">
                    <a href="{{ route('teacher.courses.show', $course) }}"
                       class="btn-primary btn-sm flex-1 justify-center">
                        Manage
                    </a>
                    <form action="{{ route('teacher.courses.leave', $course) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-secondary btn-sm"
                                onclick="return confirm('Leave {{ addslashes($course->title) }}?')">
                            Leave
                        </button>
                    </form>
                </div>
                @else
                <form action="{{ route('teacher.courses.join', $course) }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="btn-primary btn-sm w-full justify-center bg-emerald-600 hover:bg-emerald-700">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Join &amp; Teach
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
    @endforeach

    @if($courses->isEmpty())
    <div class="card empty-state">
        <span class="empty-state-icon">🏫</span>
        <p class="empty-state-title">No courses in the catalogue yet</p>
        <p class="empty-state-text">Ask your admin to set up the course catalogue.</p>
    </div>
    @endif
</x-app-layout>
