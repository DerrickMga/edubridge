<x-app-layout>
    <x-slot name="title">{{ $lesson->title }}</x-slot>

    <div class="grid lg:grid-cols-[1fr_320px] gap-6">

        {{-- Video & content --}}
        <div class="space-y-5">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-400 mb-3">
                    <a href="{{ route('student.dashboard') }}" class="hover:text-slate-600">Dashboard</a>
                    <span>/</span>
                    <span class="text-slate-600">{{ $lesson->course->subject }}</span>
                    <span>/</span>
                    <span class="text-slate-800 font-medium">{{ $lesson->title }}</span>
                </div>
            </div>

            {{-- Video player --}}
            @if($lesson->youtube_video_id)
            <div class="card overflow-hidden">
                <div class="aspect-video bg-slate-900">
                    <iframe
                        src="https://www.youtube.com/embed/{{ $lesson->youtube_video_id }}?rel=0&modestbranding=1"
                        class="w-full h-full"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                    ></iframe>
                </div>
            </div>
            @elseif($lesson->video_url)
            <div class="card overflow-hidden">
                <div class="aspect-video bg-slate-900">
                    <video src="{{ $lesson->video_url }}" controls class="w-full h-full">Your browser doesn't support HTML5 video.</video>
                </div>
            </div>
            @else
            <div class="card p-8 text-center">
                <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                </div>
                <p class="text-slate-400 text-sm">No video attached to this lesson yet.</p>
            </div>
            @endif

            {{-- Title + description --}}
            <div class="card p-5">
                <div class="flex items-start gap-3">
                    <div class="flex-1">
                        <h1 class="text-xl font-bold text-slate-900 mb-1">{{ $lesson->title }}</h1>
                        <div class="flex items-center gap-3 text-xs text-slate-400">
                            <span>{{ $lesson->course->subject }}</span>
                            <span>·</span>
                            <span>{{ $lesson->course->grade_level }}</span>
                            @if($lesson->duration_seconds > 0)
                            <span>·</span>
                            <span>{{ gmdate($lesson->duration_seconds >= 3600 ? 'G:i:s' : 'i:s', $lesson->duration_seconds) }}</span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('student.companion.index') }}" class="btn-secondary btn-sm flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                        Ask Chiedza
                    </a>
                </div>
                @if($lesson->description)
                <p class="mt-4 text-slate-600 text-sm leading-relaxed">{{ $lesson->description }}</p>
                @endif
            </div>

            {{-- Lesson Resources --}}
            @php $allResources = $lessonResources->merge($courseResources) @endphp
            @if($allResources->isNotEmpty())
            <div class="card overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.966 8.966 0 0 0-6 2.292m0-14.25v14.25"/>
                        </svg>
                        <h3 class="font-semibold text-slate-800 text-sm">Study Resources</h3>
                        <span class="text-xs text-slate-400">({{ $allResources->count() }})</span>
                    </div>
                    <a href="{{ route('student.courses.resources', $course) }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                        View all →
                    </a>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($allResources->take(5) as $resource)
                        @include('student.resources._resource-row', compact('resource'))
                    @endforeach
                </div>
                @if($allResources->count() > 5)
                <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/40 text-center">
                    <a href="{{ route('student.courses.resources', $course) }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                        + {{ $allResources->count() - 5 }} more resources
                    </a>
                </div>
                @endif
            </div>
            @endif

            {{-- Prev / Next --}}
            <div class="flex justify-between gap-4">
                @if($prev)
                <a href="{{ route('student.lessons.show', $prev) }}" class="btn-secondary flex-1 sm:flex-none">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Previous
                </a>
                @else <div></div>
                @endif
                @if($next)
                <a href="{{ route('student.lessons.show', $next) }}" class="btn-primary flex-1 sm:flex-none">
                    Next
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                </a>
                @endif
            </div>
        </div>

        {{-- Lesson sidebar --}}
        <div class="space-y-4">
            {{-- Quick links --}}
            <div class="card p-4 flex flex-col gap-2">
                <a href="{{ route('student.courses.resources', $course) }}"
                    class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 transition-colors text-indigo-700 text-sm font-medium">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.966 8.966 0 0 0-6 2.292m0-14.25v14.25"/>
                    </svg>
                    Study Resources
                </a>
            </div>

            <div class="card overflow-hidden sticky top-6">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-semibold text-slate-800 text-sm">{{ $lesson->course->title }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $lessons->count() }} lessons</p>
                </div>
                <div class="divide-y divide-slate-100 max-h-[60vh] overflow-y-auto">
                    @foreach($lessons as $l)
                    <a href="{{ route('student.lessons.show', $l) }}"
                        class="flex items-center gap-3 px-4 py-3 transition-colors {{ $l->id === $lesson->id ? 'bg-emerald-50' : 'hover:bg-slate-50' }}">
                        <div class="w-6 h-6 rounded-full text-xs font-bold flex items-center justify-center flex-shrink-0
                            {{ $l->id === $lesson->id ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-500' }}">
                            {{ $l->order }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium truncate {{ $l->id === $lesson->id ? 'text-emerald-700' : 'text-slate-700' }}">{{ $l->title }}</p>
                            @if($l->duration_seconds > 0)
                            <p class="text-[10px] text-slate-400 mt-0.5">{{ gmdate($l->duration_seconds >= 3600 ? 'G:i:s' : 'i:s', $l->duration_seconds) }}</p>
                            @endif
                        </div>
                        @if($l->youtube_video_id || $l->video_url)
                        <svg class="w-3.5 h-3.5 text-slate-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z"/></svg>
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
