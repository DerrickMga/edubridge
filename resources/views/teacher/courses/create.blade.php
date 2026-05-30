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
                <div class="form-group">
                    <label class="form-label">Course Title</label>
                    <input type="text" name="title" value="{{ old('title') }}" class="form-input" required
                           placeholder="e.g. Mathematics — O-Level Revision 2026">
                    @error('title')<p class="form-error">{{ $message }}</p>@enderror
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div class="form-group">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" class="form-input"
                               placeholder="e.g. Mathematics" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Grade Level</label>
                        <select name="grade_level" class="form-select" required>
                            <option value="">Select grade…</option>
                            @foreach(['Form 1','Form 2','Form 3','Form 4 (O-Level)','Form 5 (O-Level)','Lower 6 (A-Level)','Upper 6 (A-Level)'] as $grade)
                            <option value="{{ $grade }}" {{ old('grade_level') === $grade ? 'selected' : '' }}>{{ $grade }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" rows="4"
                              placeholder="What will students learn? What makes this course valuable?">{{ old('description') }}</textarea>
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div class="form-group">
                        <label class="form-label">Price (USD)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">$</span>
                            <input type="number" name="price_usd" value="{{ old('price_usd', 0) }}"
                                   min="0" step="0.01" class="form-input pl-7">
                        </div>
                        <p class="form-hint">Set to 0 for a free course.</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Price (ZWG)</label>
                        <input type="number" name="price_zwg" value="{{ old('price_zwg', 0) }}"
                               min="0" step="0.01" class="form-input">
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
                <a href="{{ route('teacher.dashboard') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Create Course</button>
            </div>
        </form>
    </div>
</x-app-layout>
