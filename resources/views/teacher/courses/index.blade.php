<x-app-layout>
    <x-slot name="title">My Courses</x-slot>

    <div class="page-header flex items-center justify-between gap-4">
        <div>
            <h1 class="page-title">My Courses</h1>
            <p class="page-subtitle">Manage and organise your course catalogue.</p>
        </div>
        <a href="{{ route('teacher.courses.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New course
        </a>
    </div>

    @if($courses->isEmpty())
    <div class="card empty-state">
        <span class="empty-state-icon">🏫</span>
        <p class="empty-state-title">No courses yet</p>
        <p class="empty-state-text">Create your first course to start teaching.</p>
        <a href="{{ route('teacher.courses.create') }}" class="btn-primary">Create first course</a>
    </div>
    @else
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($courses as $course)
        @php
            $color = match(strtolower($course->subject ?? '')) {
                'mathematics','maths' => 'from-blue-500 to-indigo-600',
                'english' => 'from-purple-500 to-violet-600',
                'physics','chemistry','biology','science' => 'from-emerald-500 to-teal-600',
                'history' => 'from-amber-400 to-orange-500',
                'geography' => 'from-teal-500 to-cyan-600',
                'business studies','commerce' => 'from-orange-400 to-rose-500',
                default => 'from-slate-400 to-slate-600',
            };
        @endphp
        <div class="card hover:shadow-card-hover transition-all duration-200 overflow-hidden">
            <a href="{{ route('teacher.courses.show', $course) }}" class="block h-24 bg-gradient-to-br {{ $color }}">
                <div class="h-full flex items-end p-4">
                    <span class="text-xs font-bold text-white/80 uppercase tracking-wide">{{ $course->subject }}</span>
                </div>
            </a>
            <div class="p-5">
                <div class="flex items-start gap-2 mb-2">
                    <h3 class="font-semibold text-slate-900 flex-1 leading-snug">{{ $course->title }}</h3>
                    <span class="{{ $course->status === 'published' ? 'badge-green' : 'badge-amber' }}">{{ $course->status }}</span>
                </div>
                <p class="text-xs text-slate-400 mb-4">{{ $course->grade_level }} · {{ $course->enrollments_count }} students</p>
                <div class="flex gap-2">
                    <a href="{{ route('teacher.courses.show', $course) }}" class="btn-primary btn-sm flex-1 justify-center">Manage</a>
                    <a href="{{ route('teacher.courses.edit', $course) }}" class="btn-secondary btn-sm">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125"/></svg>
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    {{ $courses->links() }}
    @endif
</x-app-layout>
