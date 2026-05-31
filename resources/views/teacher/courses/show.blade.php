<x-app-layout>
    <x-slot name="title">{{ $course->title }}</x-slot>

    <div class="max-w-4xl">
        {{-- Header --}}
        <div class="page-header flex items-start justify-between gap-4 mb-6">
            <div>
                <a href="{{ route('teacher.courses.index') }}"
                   class="inline-flex items-center gap-1 text-sm text-slate-400 hover:text-slate-700 mb-1 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                    My Courses
                </a>
                <h1 class="page-title">{{ $course->title }}</h1>
                <p class="page-subtitle">
                    {{ $course->subject }} · {{ $course->grade_level }} ·
                    <span class="{{ $course->status === 'published' ? 'text-emerald-600' : 'text-amber-600' }} font-semibold">{{ $course->status }}</span>
                </p>
            </div>
            <a href="{{ route('teacher.courses.edit', $course) }}" class="btn-primary btn-sm flex-shrink-0">Edit Course</a>
        </div>

        @if($course->description)
        <p class="text-slate-500 mb-6">{{ $course->description }}</p>
        @endif

        @if(session('success'))
        <div class="mb-5 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
            <svg class="w-4 h-4 flex-shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- Module quick-links --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            <a href="{{ route('teacher.assignments.index', $course) }}" class="card p-4 text-center card-hover group">
                <span class="text-2xl block mb-1">📋</span>
                <p class="text-sm font-semibold text-slate-700 group-hover:text-emerald-700 transition-colors">Assignments</p>
            </a>
            <a href="{{ route('teacher.quizzes.create', $course) }}" class="card p-4 text-center card-hover group">
                <span class="text-2xl block mb-1">📝</span>
                <p class="text-sm font-semibold text-slate-700 group-hover:text-emerald-700 transition-colors">Create Quiz</p>
            </a>
            <a href="{{ route('teacher.resources.index', $course) }}" class="card p-4 text-center card-hover group">
                <span class="text-2xl block mb-1">📁</span>
                <p class="text-sm font-semibold text-slate-700 group-hover:text-emerald-700 transition-colors">Content Library</p>
            </a>
            <a href="{{ route('teacher.live-sessions.create') }}" class="card p-4 text-center card-hover group">
                <span class="text-2xl block mb-1">📡</span>
                <p class="text-sm font-semibold text-slate-700 group-hover:text-emerald-700 transition-colors">Live Session</p>
            </a>
        </div>

        {{-- Course Plan --}}
        <div class="card overflow-hidden mb-4">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-semibold text-slate-900">Course Plan ({{ $course->lessons->count() }} lessons)</h2>
                <a href="{{ route('teacher.lessons.create', $course) }}" class="btn-primary btn-sm">+ Add Lesson</a>
            </div>
            @if($course->lessons->isEmpty())
            <div class="p-8 text-center text-slate-400 text-sm">
                No lessons yet.
                <a href="{{ route('teacher.lessons.create', $course) }}" class="text-emerald-600 font-semibold hover:underline ml-1">Add your first lesson</a>
            </div>
            @else
            <ul class="divide-y divide-slate-100">
                @foreach($course->lessons->sortBy('order') as $lesson)
                <li class="px-5 py-3.5 flex items-center gap-4">
                    <span class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 text-xs font-bold flex items-center justify-center flex-shrink-0">{{ $lesson->order }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-slate-900 truncate">{{ $lesson->title }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">
                            <span class="{{ $lesson->status === 'published' ? 'text-emerald-600' : 'text-amber-600' }} font-semibold">{{ $lesson->status }}</span>
                            @if($lesson->duration_seconds > 0) · {{ gmdate('G:i', $lesson->duration_seconds) }} @endif
                            @if($lesson->youtube_video_id) · 🎬 YouTube @endif
                        </p>
                    </div>
                    <a href="{{ route('teacher.lessons.edit', $lesson) }}" class="btn-secondary btn-sm flex-shrink-0">Edit</a>
                </li>
                @endforeach
            </ul>
            @endif
        </div>

        {{-- Live Sessions --}}
        <div class="card overflow-hidden mb-4">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-semibold text-slate-900">Live Sessions ({{ $course->liveSessions->count() }})</h2>
                <a href="{{ route('teacher.live-sessions.create') }}" class="btn-secondary btn-sm">+ Schedule</a>
            </div>
            @if($course->liveSessions->isEmpty())
            <div class="p-8 text-center text-slate-400 text-sm">No live sessions scheduled.</div>
            @else
            <ul class="divide-y divide-slate-100">
                @foreach($course->liveSessions as $session)
                <li class="px-5 py-3.5 flex items-center justify-between gap-4">
                    <div>
                        <p class="font-medium text-slate-900">{{ $session->title }}</p>
                        <p class="text-xs text-slate-400">{{ $session->scheduled_at->format('D d M Y, g:ia') }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold {{ $session->status === 'live' ? 'badge-green' : 'badge-slate' }}">{{ $session->status }}</span>
                        @if($session->is_recorded)
                        <a href="{{ route('teacher.recordings.index', $session) }}" class="btn-secondary btn-sm">Recordings</a>
                        @endif
                    </div>
                </li>
                @endforeach
            </ul>
            @endif
        </div>

        {{-- Announcements --}}
        <div class="card overflow-hidden" x-data="{ open: false }">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-semibold text-slate-900">Announcements ({{ $course->announcements->count() }})</h2>
                <button @click="open = !open" class="btn-secondary btn-sm">+ Post</button>
            </div>

            <div x-show="open" class="p-5 border-b border-slate-100 bg-slate-50">
                <form action="{{ route('teacher.announcements.store', $course) }}" method="POST" class="space-y-3">
                    @csrf
                    <div class="form-group mb-0">
                        <label class="form-label text-xs">Title</label>
                        <input type="text" name="title" class="form-input" placeholder="Exam reminder, lesson update..." required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label text-xs">Message</label>
                        <textarea name="body" class="form-textarea" rows="3" required></textarea>
                    </div>
                    <button type="submit" class="btn-primary btn-sm">Post Announcement</button>
                </form>
            </div>

            @if($course->announcements->isEmpty())
            <div class="p-6 text-center text-slate-400 text-sm">No announcements yet.</div>
            @else
            <ul class="divide-y divide-slate-100">
                @foreach($course->announcements->sortByDesc('published_at')->take(5) as $ann)
                <li class="px-5 py-4 flex items-start justify-between gap-4">
                    <div>
                        <p class="font-medium text-slate-900">{{ $ann->title }}</p>
                        <p class="text-sm text-slate-500 mt-0.5">{{ Str::limit($ann->body, 100) }}</p>
                        <p class="text-xs text-slate-400 mt-1">{{ $ann->published_at?->diffForHumans() ?? 'Draft' }}</p>
                    </div>
                    <form action="{{ route('teacher.announcements.destroy', [$course, $ann]) }}" method="POST" onsubmit="return confirm('Delete?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger btn-sm flex-shrink-0">Delete</button>
                    </form>
                </li>
                @endforeach
            </ul>
            @endif
        </div>
    </div>
</x-app-layout>
