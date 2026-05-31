<x-app-layout>
    <x-slot name="title">Edit {{ $course->title }}</x-slot>

    <div class="max-w-2xl">
        <div class="page-header flex items-center gap-3 mb-6">
            <a href="{{ route('teacher.courses.show', $course) }}"
               class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </a>
            <div>
                <h1 class="page-title">Edit Course</h1>
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

        <form method="POST" action="{{ route('teacher.courses.update', $course) }}" class="space-y-5">
            @csrf @method('PUT')
            <div class="card p-6 space-y-5">
                <div class="form-group">
                    <label class="form-label">Course Title</label>
                    <input type="text" name="title" value="{{ old('title', $course->title) }}" class="form-input" required>
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject', $course->subject) }}" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Grade Level</label>
                        <select name="grade_level" class="form-select" required>
                            @foreach(['Form 1','Form 2','Form 3','Form 4 (O-Level)','Form 5 (O-Level)','Lower 6 (A-Level)','Upper 6 (A-Level)'] as $grade)
                            <option value="{{ $grade }}" {{ old('grade_level', $course->grade_level) === $grade ? 'selected' : '' }}>{{ $grade }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @foreach(['draft','published','archived'] as $s)
                        <option value="{{ $s }}" {{ old('status', $course->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" rows="4">{{ old('description', $course->description) }}</textarea>
                </div>

                <div class="form-group">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        <p class="font-semibold text-slate-700 mb-1">Platform pricing applies to all courses</p>
                        <ul class="space-y-0.5 text-slate-500">
                            <li>1 Hour &mdash; <span class="font-medium text-slate-700">$1.00</span> / ZWG 30</li>
                            <li>1 Month &mdash; <span class="font-medium text-slate-700">$5.00</span> / ZWG 150</li>
                            <li>1 Term (3 months) &mdash; <span class="font-medium text-slate-700">$10.00</span> / ZWG 300</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 justify-end">
                <a href="{{ route('teacher.courses.show', $course) }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>

        {{-- Danger zone --}}
        <div class="mt-8 card border-red-200 p-5">
            <h3 class="font-semibold text-red-700 mb-1">Danger Zone</h3>
            <p class="text-sm text-slate-500 mb-4">Permanently delete this course and all its lessons. This cannot be undone.</p>
            <form method="POST" action="{{ route('teacher.courses.destroy', $course) }}"
                  onsubmit="return confirm('Delete this course permanently? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger">Delete Course</button>
            </form>
        </div>
    </div>
</x-app-layout>
