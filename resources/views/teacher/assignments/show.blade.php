<x-app-layout>
    <x-slot name="title">{{ $assignment->title }} — Submissions</x-slot>

    <div class="max-w-4xl">
        <div class="page-header flex items-center gap-3 mb-6">
            <a href="{{ route('teacher.assignments.index', $course) }}"
               class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </a>
            <div>
                <h1 class="page-title">{{ $assignment->title }}</h1>
                <p class="page-subtitle">{{ $submissions->count() }} submission{{ $submissions->count() !== 1 ? 's' : '' }} &middot; Max score: {{ $assignment->max_score }}</p>
            </div>
        </div>

        @if(session('success'))
        <div class="mb-5 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
            <svg class="w-4 h-4 flex-shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            {{ session('success') }}
        </div>
        @endif

        @if($submissions->isEmpty())
        <div class="card empty-state">
            <span class="empty-state-icon">📭</span>
            <p class="empty-state-title">No submissions yet</p>
            <p class="empty-state-text">Students haven't submitted this assignment yet.</p>
        </div>
        @else
        <div class="space-y-4">
            @foreach($submissions as $submission)
            @php
            $statusColor = ['draft' => 'badge-slate','submitted' => 'badge-blue','graded' => 'badge-green','returned' => 'badge-amber'][$submission->status] ?? 'badge-slate';
            @endphp
            <div class="card p-5">
                <div class="flex items-start justify-between gap-4 mb-3">
                    <div>
                        <p class="font-semibold text-slate-900">{{ $submission->student->name }}</p>
                        <p class="text-xs text-slate-400">
                            <span class="{{ $statusColor }} text-xs mr-2">{{ ucfirst($submission->status) }}</span>
                            @if($submission->submitted_at)
                            Submitted {{ $submission->submitted_at->diffForHumans() }}
                            @endif
                            @if($submission->score !== null)
                             &middot; Score: <strong>{{ $submission->score }}/{{ $assignment->max_score }}</strong>
                            @endif
                        </p>
                    </div>
                    @if($submission->file_path)
                    <a href="{{ Storage::url($submission->file_path) }}" target="_blank" class="btn-secondary btn-sm flex-shrink-0">Download</a>
                    @endif
                </div>

                @if($submission->content)
                <div class="bg-slate-50 rounded-xl p-4 text-sm text-slate-700 mb-4 max-h-40 overflow-y-auto">
                    {{ $submission->content }}
                </div>
                @endif

                @if($submission->feedback)
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-sm text-amber-800 mb-4">
                    <strong>Your feedback:</strong> {{ $submission->feedback }}
                </div>
                @endif

                @if(in_array($submission->status, ['submitted','graded']))
                <form action="{{ route('teacher.assignments.grade', [$course, $assignment, $submission]) }}" method="POST" class="flex flex-wrap items-end gap-3">
                    @csrf @method('PUT')
                    <div class="form-group mb-0">
                        <label class="form-label text-xs">Score</label>
                        <input type="number" name="score" class="form-input w-24"
                               min="0" max="{{ $assignment->max_score }}"
                               value="{{ $submission->score ?? '' }}" required>
                    </div>
                    <div class="form-group flex-1 mb-0">
                        <label class="form-label text-xs">Feedback</label>
                        <input type="text" name="feedback" class="form-input"
                               placeholder="Optional feedback for student..."
                               value="{{ $submission->feedback }}">
                    </div>
                    <button type="submit" class="btn-primary btn-sm">Grade</button>
                </form>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>
</x-app-layout>
