<x-app-layout>
    <x-slot name="title">Edit Lesson — {{ $lesson->title }}</x-slot>

    <div class="page-header flex items-center gap-3">
        <a href="{{ route('teacher.courses.show', $lesson->course) }}" class="text-slate-400 hover:text-slate-600 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        </a>
        <div>
            <h1 class="page-title">Edit Lesson</h1>
            <p class="page-subtitle">{{ $lesson->course->title }}</p>
        </div>
    </div>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('teacher.lessons.update', $lesson) }}" class="space-y-6">
            @csrf @method('PUT')

            <div class="card p-6 space-y-4">
                <h2 class="section-title">Lesson Content</h2>
                <div class="form-group">
                    <label for="title" class="form-label">Lesson title <span class="text-red-400">*</span></label>
                    <input id="title" type="text" name="title" value="{{ old('title', $lesson->title) }}" required class="form-input @error('title') border-red-400 @enderror" />
                    @error('title')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label for="description" class="form-label">Description / Learning objectives</label>
                    <textarea id="description" name="description" rows="3" class="form-textarea">{{ old('description', $lesson->description) }}</textarea>
                </div>
            </div>

            <div class="card p-6 space-y-4">
                <h2 class="section-title">Video</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="youtube_video_id" class="form-label">YouTube Video ID</label>
                        <input id="youtube_video_id" type="text" name="youtube_video_id" value="{{ old('youtube_video_id', $lesson->youtube_video_id) }}" class="form-input" placeholder="dQw4w9WgXcQ" />
                    </div>
                    <div class="form-group">
                        <label for="duration_seconds" class="form-label">Duration (seconds)</label>
                        <input id="duration_seconds" type="number" name="duration_seconds" value="{{ old('duration_seconds', $lesson->duration_seconds) }}" min="0" class="form-input" />
                    </div>
                </div>
                <div class="form-group">
                    <label for="video_url" class="form-label">External video URL</label>
                    <input id="video_url" type="url" name="video_url" value="{{ old('video_url', $lesson->video_url) }}" class="form-input" placeholder="https://…" />
                </div>
            </div>

            <div class="card p-6 space-y-4">
                <h2 class="section-title">Order &amp; Visibility</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label for="order" class="form-label">Position in course</label>
                        <input id="order" type="number" name="order" value="{{ old('order', $lesson->order) }}" min="1" class="form-input" />
                    </div>
                    <div class="form-group">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select">
                            <option value="draft" {{ old('status', $lesson->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status', $lesson->status) === 'published' ? 'selected' : '' }}>Published</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Save changes</button>
                <a href="{{ route('teacher.courses.show', $lesson->course) }}" class="btn-secondary">Cancel</a>
            </div>
        </form>

        {{-- Danger zone --}}
        <div class="card border-red-200 mt-6 p-6">
            <h3 class="text-sm font-semibold text-red-600 mb-1">Delete lesson</h3>
            <p class="text-xs text-slate-400 mb-4">This will permanently remove the lesson from the course. This action cannot be undone.</p>
            <form method="POST" action="{{ route('teacher.lessons.destroy', $lesson) }}" onsubmit="return confirm('Delete this lesson permanently?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn-danger btn-sm">Delete lesson</button>
            </form>
        </div>
    </div>
</x-app-layout>
