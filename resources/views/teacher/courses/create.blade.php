<x-app-layout>
    <x-slot name="title">Create Course</x-slot>

    <div class="max-w-2xl">
        <div class="page-header flex items-center gap-3 mb-6">
            <a href="{{ route('teacher.dashboard') }}"
               class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </a>
            <div>
                <h1 class="page-title">Create Course</h1>
                <p class="page-subtitle">Set up a new course for your students.</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-5 flex gap-3 rounded-xl bg-red-50 border border-red-200 p-4">
            <ul class="text-sm text-red-700 list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('teacher.courses.store') }}" class="space-y-5">
            @csrf
            <div class="card p-6 space-y-5">

                <div class="grid sm:grid-cols-2 gap-5">
                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <select id="subject" name="subject" class="form-select" required>
                            <option value="">Select subject…</option>
                            @foreach([
                                'Mathematics','Further Mathematics','English Language','English Literature',
                                'Physics','Chemistry','Biology','Combined Science',
                                'History','Geography',
                                'Business Studies','Commerce','Accounting','Economics',
                                'Computer Science','Agriculture',
                                'Shona','Ndebele','French','Art','Music','Physical Education'
                            ] as $subj)
                            <option value="{{ $subj }}" {{ old('subject') === $subj ? 'selected' : '' }}>{{ $subj }}</option>
                            @endforeach
                        </select>
                        @error('subject')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Grade Level</label>
                        <select id="grade_level" name="grade_level" class="form-select" required>
                            <option value="">Select grade…</option>
                            @foreach(['Form 1','Form 2','Form 3','Form 4 (O-Level)','Form 5 (O-Level)','Lower 6 (A-Level)','Upper 6 (A-Level)'] as $grade)
                            <option value="{{ $grade }}" {{ old('grade_level') === $grade ? 'selected' : '' }}>{{ $grade }}</option>
                            @endforeach
                        </select>
                        @error('grade_level')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Course Title</label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" class="form-input" required
                           placeholder="Select subject and grade above to auto-fill">
                    <p class="form-hint">Auto-filled from subject &amp; grade — edit only if you need a custom title.</p>
                    @error('title')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" rows="4"
                              placeholder="What will students learn? What makes this course valuable?">{{ old('description') }}</textarea>
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

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="draft" {{ old('status','draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                    <p class="form-hint">Only published courses are visible to students.</p>
                </div>
            </div>

            <div class="flex gap-3 justify-end">
                <a href="{{ route('teacher.courses.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Create Course</button>
            </div>
        </form>
    </div>

    <script>
    (function () {
        const subjectEl = document.getElementById('subject');
        const gradeEl   = document.getElementById('grade_level');
        const titleEl   = document.getElementById('title');
        const userEdited = titleEl.value !== '';  // pre-filled by old() on validation fail

        function autoFill() {
            if (userEdited) return;
            const s = subjectEl.value;
            const g = gradeEl.value;
            if (s && g) {
                titleEl.value = s + ' \u2014 ' + g;
            } else if (s) {
                titleEl.value = s;
            }
        }

        subjectEl.addEventListener('change', autoFill);
        gradeEl.addEventListener('change', autoFill);

        // Allow manual override — once user types, stop auto-filling
        titleEl.addEventListener('input', function () {
            // mark as user-edited by detaching auto-fill
            subjectEl.removeEventListener('change', autoFill);
            gradeEl.removeEventListener('change', autoFill);
        });
    })();
    </script>
</x-app-layout>
