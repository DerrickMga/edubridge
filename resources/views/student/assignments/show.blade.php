<x-app-layout>
    <x-slot name="title">{{ $assignment->title }}</x-slot>

    <div class="max-w-3xl">
        <div class="page-header flex items-center gap-3 mb-6">
            <a href="{{ route('student.assignments.index') }}"
               class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </a>
            <div>
                <p class="text-xs text-slate-400 mb-0.5">{{ $assignment->course->title }}</p>
                <h1 class="page-title">{{ $assignment->title }}</h1>
            </div>
        </div>

        @foreach(['success','error'] as $msg)
        @if(session($msg))
        <div class="mb-5 flex items-center gap-3 rounded-xl {{ $msg === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800' }} border px-4 py-3 text-sm">
            {{ session($msg) }}
        </div>
        @endif
        @endforeach

        {{-- Assignment Info --}}
        <div class="card p-6 mb-5 space-y-4">
            <div class="flex flex-wrap gap-3">
                <span class="badge-slate">{{ ucfirst($assignment->type) }}</span>
                <span class="badge-slate">{{ $assignment->max_score }} points</span>
                @if($assignment->due_at)
                <span class="{{ $assignment->due_at->isPast() ? 'badge-red' : 'badge-amber' }}">
                    Due {{ $assignment->due_at->format('D, d M Y H:i') }}
                </span>
                @endif
                @if($assignment->allow_late)
                <span class="badge-slate">Late submissions allowed</span>
                @endif
            </div>

            @if($assignment->lesson)
            <p class="text-xs text-slate-400">Linked to lesson: <span class="font-medium text-slate-600">{{ $assignment->lesson->title }}</span></p>
            @endif

            <div class="prose prose-sm max-w-none text-slate-700">
                {!! nl2br(e($assignment->instructions)) !!}
            </div>
        </div>

        {{-- Existing submission --}}
        @if($submission)
        <div class="card p-5 mb-5 border-l-4 {{ $submission->score !== null ? 'border-emerald-400' : 'border-blue-400' }}">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-slate-800">Your Submission</h3>
                <div class="flex gap-2">
                    @if($submission->score !== null)
                    <span class="badge-green">{{ $submission->score }}/{{ $assignment->max_score }} pts</span>
                    @else
                    <span class="badge-blue">Awaiting grade</span>
                    @endif
                    <span class="text-xs text-slate-400">{{ $submission->submitted_at?->format('d M Y H:i') }}</span>
                </div>
            </div>
            @if($submission->content)
            <div class="bg-slate-50 rounded-lg p-4 text-sm text-slate-700 whitespace-pre-wrap mb-3">{{ $submission->content }}</div>
            @endif
            @if($submission->file_path)
            <p class="text-xs text-slate-500">File uploaded: <span class="font-medium">{{ basename($submission->file_path) }}</span></p>
            @endif
            @if($submission->feedback)
            <div class="mt-3 border-t border-slate-100 pt-3">
                <p class="text-xs font-semibold text-slate-500 mb-1">Teacher Feedback</p>
                <p class="text-sm text-slate-700">{{ $submission->feedback }}</p>
            </div>
            @endif
        </div>
        @endif

        {{-- Submit / Resubmit form --}}
        @php
            $isLate = $assignment->due_at && $assignment->due_at->isPast();
            $canSubmit = !$isLate || $assignment->allow_late;
            $alreadyGraded = $submission && $submission->score !== null;
        @endphp

        @if($canSubmit && !$alreadyGraded)
        <div class="card p-6">
            <h3 class="font-semibold text-slate-900 mb-4">{{ $submission ? 'Update Submission' : 'Submit Your Work' }}</h3>
            <form method="POST" action="{{ route('student.assignments.submit', $assignment) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @if($errors->any())
                <div class="rounded-xl bg-red-50 border border-red-200 p-3">
                    <ul class="text-sm text-red-700 list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
                @endif

                @if(in_array($assignment->type, ['written','project']))
                <div class="form-group">
                    <label class="form-label">Written answer</label>
                    <textarea name="content" rows="8" class="form-textarea" placeholder="Type your answer here…">{{ old('content', $submission?->content) }}</textarea>
                </div>
                @endif

                @if(in_array($assignment->type, ['file_upload','project']))
                <div class="form-group">
                    <label class="form-label">Upload file <span class="text-slate-400 font-normal">(PDF, Word, image, ZIP — max 20 MB)</span></label>
                    <input type="file" name="file" class="form-input" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg,.txt,.zip">
                </div>
                @endif

                <div class="flex gap-3 justify-end">
                    <a href="{{ route('student.assignments.index') }}" class="btn-secondary">Cancel</a>
                    <button type="submit" class="btn-primary">{{ $submission ? 'Update submission' : 'Submit assignment' }}</button>
                </div>
            </form>
        </div>
        @elseif($alreadyGraded)
        <div class="card p-4 text-center text-sm text-slate-500">This assignment has been graded and can no longer be edited.</div>
        @elseif($isLate && !$assignment->allow_late)
        <div class="card p-4 text-center text-sm text-red-500">The deadline has passed and late submissions are not allowed.</div>
        @endif
    </div>
</x-app-layout>
