<x-app-layout>
    <x-slot name="title">New Assignment</x-slot>

    <div class="max-w-2xl">
        <div class="page-header flex items-center gap-3 mb-6">
            <a href="{{ route('teacher.assignments.index', $course) }}"
               class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </a>
            <div>
                <h1 class="page-title">New Assignment</h1>
                <p class="page-subtitle">{{ $course->title }}</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-5 flex gap-3 rounded-xl bg-red-50 border border-red-200 p-4">
            <ul class="text-sm text-red-700 list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('teacher.assignments.store', $course) }}" method="POST" class="space-y-5">
            @csrf
            <div class="card p-6 space-y-5">
                <div class="form-group">
                    <label class="form-label" for="title">Assignment Title</label>
                    <input type="text" name="title" id="title" class="form-input"
                           value="{{ old('title') }}" required placeholder="e.g. Write a short essay on climate change in Zimbabwe">
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div class="form-group">
                        <label class="form-label" for="type">Type</label>
                        <select name="type" id="type" class="form-select">
                            <option value="written">Written Response</option>
                            <option value="file_upload">File Upload</option>
                            <option value="project">Project</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="max_score">Max Score</label>
                        <input type="number" name="max_score" id="max_score" class="form-input"
                               value="{{ old('max_score', 100) }}" min="1" max="1000" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="instructions">Instructions</label>
                    <textarea name="instructions" id="instructions" class="form-textarea" rows="5"
                              required placeholder="Detailed instructions for students...">{{ old('instructions') }}</textarea>
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div class="form-group">
                        <label class="form-label" for="due_at">Due Date <span class="text-slate-400 font-normal">(optional)</span></label>
                        <input type="datetime-local" name="due_at" id="due_at" class="form-input"
                               value="{{ old('due_at') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="lesson_id">Link to Lesson <span class="text-slate-400 font-normal">(optional)</span></label>
                        <select name="lesson_id" id="lesson_id" class="form-select">
                            <option value="">— No specific lesson —</option>
                            @foreach($lessons as $lesson)
                            <option value="{{ $lesson->id }}" {{ old('lesson_id') == $lesson->id ? 'selected' : '' }}>
                                {{ $lesson->title }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="allow_late" value="1" class="accent-emerald-500" {{ old('allow_late') ? 'checked' : '' }}>
                        <span class="text-sm text-slate-600">Allow late submissions</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_published" value="1" class="accent-emerald-500" {{ old('is_published') ? 'checked' : '' }}>
                        <span class="text-sm text-slate-600">Publish immediately</span>
                    </label>
                </div>
            </div>

            <div class="flex gap-3 justify-end pt-2">
                <a href="{{ route('teacher.assignments.index', $course) }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Create Assignment</button>
            </div>
        </form>
    </div>
</x-app-layout>
