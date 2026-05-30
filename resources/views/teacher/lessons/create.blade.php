<x-app-layout>
    <x-slot name="title">Add Lesson — {{ $course->title }}</x-slot>

    <div class="page-header flex items-center gap-3">
        <a href="{{ route('teacher.courses.show', $course) }}" class="text-slate-400 hover:text-slate-600 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        </a>
        <div>
            <h1 class="page-title">Add New Lesson</h1>
            <p class="page-subtitle">{{ $course->title }} · {{ $course->subject }}</p>
        </div>
    </div>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('teacher.lessons.store', $course) }}" class="space-y-6">
            @csrf

            <div class="card p-6 space-y-4">
                <h2 class="section-title">Lesson Content</h2>
                <div class="form-group">
                    <label for="title" class="form-label">Lesson title <span class="text-red-400">*</span></label>
                    <input id="title" type="text" name="title" value="{{ old('title') }}" required class="form-input @error('title') border-red-400 @enderror" placeholder="e.g. Introduction to Algebra" />
                    @error('title')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label for="description" class="form-label">Description / Learning objectives</label>
                    <textarea id="description" name="description" rows="3" class="form-textarea" placeholder="What will students learn in this lesson?">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="card p-6 space-y-4">
                <h2 class="section-title">Video</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="youtube_video_id" class="form-label">YouTube Video ID</label>
                        <input id="youtube_video_id" type="text" name="youtube_video_id" value="{{ old('youtube_video_id') }}" class="form-input" placeholder="dQw4w9WgXcQ" />
                        <p class="form-hint">The ID after <code>watch?v=</code> in the YouTube URL</p>
                    </div>
                    <div class="form-group">
                        <label for="duration_seconds" class="form-label">Duration (seconds)</label>
                        <input id="duration_seconds" type="number" name="duration_seconds" value="{{ old('duration_seconds', 0) }}" min="0" class="form-input" />
                        <p class="form-hint">Leave 0 if unknown</p>
                    </div>
                </div>
                <div class="form-group">
                    <label for="video_url" class="form-label">External video URL</label>
                    <input id="video_url" type="url" name="video_url" value="{{ old('video_url') }}" class="form-input" placeholder="https://…" />
                    <p class="form-hint">Use this if the video is not on YouTube</p>
                </div>
            </div>

            <div class="card p-6 space-y-4">
                <h2 class="section-title">Order &amp; Visibility</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="order" class="form-label">Position in course</label>
                        <input id="order" type="number" name="order" value="{{ old('order') }}" min="1" class="form-input" placeholder="Auto (append at end)" />
                        <p class="form-hint">Leave blank to append at the end</p>
                    </div>
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="draft" {{ old('status','draft') === 'draft' ? 'selected' : '' }}>Draft — not visible to students</option>
                            <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published — visible to enrolled students</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    Add lesson
                </button>
                <a href="{{ route('teacher.courses.show', $course) }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
