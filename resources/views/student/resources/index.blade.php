<x-app-layout>
    <x-slot name="title">Resources — {{ $course->title }}</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('student.dashboard') }}" class="hover:text-slate-600">Dashboard</a>
            <span>/</span>
            <span class="text-slate-600">{{ $course->subject }}</span>
            <span>/</span>
            <span class="text-slate-800 font-medium">Resources</span>
        </div>

        {{-- Header --}}
        <div class="card p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-slate-900">{{ $course->title }}</h1>
                    <p class="text-sm text-slate-500 mt-1">{{ $resources->flatten()->count() }} resource{{ $resources->flatten()->count() !== 1 ? 's' : '' }} available</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.966 8.966 0 0 0-6 2.292m0-14.25v14.25"/>
                    </svg>
                    Study Materials
                </span>
            </div>
        </div>

        @if($resources->isEmpty())
        <div class="card p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v8.25m19.5 0v-7.5a2.25 2.25 0 0 0-2.25-2.25h-9.9"/>
                </svg>
            </div>
            <p class="text-slate-500 font-medium">No resources yet</p>
            <p class="text-slate-400 text-sm mt-1">Your teacher hasn't uploaded any study materials for this course yet.</p>
        </div>
        @else

        {{-- Course-level resources (not tied to a specific lesson) --}}
        @if($resources->has(0) && $resources[0]->isNotEmpty())
        <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.966 8.966 0 0 0-6 2.292m0-14.25v14.25"/>
                </svg>
                <h2 class="font-semibold text-slate-800 text-sm">Course Materials</h2>
                <span class="text-xs text-slate-400 ml-auto">{{ $resources[0]->count() }} file{{ $resources[0]->count() !== 1 ? 's' : '' }}</span>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($resources[0] as $resource)
                    @include('student.resources._resource-row', compact('resource'))
                @endforeach
            </div>
        </div>
        @endif

        {{-- Per-lesson resources --}}
        @foreach($resources as $lessonId => $lessonResources)
            @if($lessonId == 0) @continue @endif
            @php $lesson = $lessons->get($lessonId) @endphp
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/60 flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                        <span class="text-xs font-bold text-indigo-600">{{ $lesson?->order ?? '?' }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="font-semibold text-slate-800 text-sm truncate">{{ $lesson?->title ?? 'Lesson '.$lessonId }}</h2>
                    </div>
                    <span class="text-xs text-slate-400 flex-shrink-0">{{ $lessonResources->count() }} file{{ $lessonResources->count() !== 1 ? 's' : '' }}</span>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($lessonResources as $resource)
                        @include('student.resources._resource-row', compact('resource'))
                    @endforeach
                </div>
            </div>
        @endforeach

        @endif
    </div>
</x-app-layout>
