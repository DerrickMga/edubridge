<x-app-layout>
    <x-slot name="title">Assignments — {{ $course->title }}</x-slot>

    <div class="max-w-4xl">
        <div class="page-header flex items-start justify-between gap-3 mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('teacher.courses.show', $course) }}"
                   class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                </a>
                <div>
                    <h1 class="page-title">Assignments</h1>
                    <p class="page-subtitle">{{ $course->title }}</p>
                </div>
            </div>
            <a href="{{ route('teacher.assignments.create', $course) }}" class="btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                New Assignment
            </a>
        </div>

        @if(session('success'))
        <div class="mb-5 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
            <svg class="w-4 h-4 flex-shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            {{ session('success') }}
        </div>
        @endif

        @if($assignments->isEmpty())
        <div class="card empty-state">
            <span class="empty-state-icon">📋</span>
            <p class="empty-state-title">No assignments yet</p>
            <p class="empty-state-text">Create assignments for students to complete and submit.</p>
            <a href="{{ route('teacher.assignments.create', $course) }}" class="btn-primary">Create assignment</a>
        </div>
        @else
        <div class="space-y-3">
            @foreach($assignments as $assignment)
            @php
            $overdue = $assignment->due_at && $assignment->due_at->isPast();
            $typeBadge = ['written' => 'badge-slate','file_upload' => 'badge-blue','quiz' => 'badge-purple','project' => 'badge-amber'][$assignment->type] ?? 'badge-slate';
            @endphp
            <div class="card p-5 flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <p class="font-semibold text-slate-900">{{ $assignment->title }}</p>
                        <span class="{{ $typeBadge }} text-xs">{{ ucfirst(str_replace('_',' ',$assignment->type)) }}</span>
                        @if(!$assignment->is_published)
                        <span class="badge-slate text-xs">Draft</span>
                        @endif
                        @if($overdue)
                        <span class="badge-red text-xs">Overdue</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400">
                        {{ $assignment->submissions_count }} submission{{ $assignment->submissions_count !== 1 ? 's' : '' }} &middot;
                        Max score: {{ $assignment->max_score }}
                        @if($assignment->due_at)
                         &middot; Due {{ $assignment->due_at->format('d M Y, g:ia') }}
                        @endif
                    </p>
                </div>
                <div class="flex gap-2 flex-shrink-0">
                    <a href="{{ route('teacher.assignments.show', [$course, $assignment]) }}" class="btn-secondary btn-sm">
                        View Submissions
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</x-app-layout>
