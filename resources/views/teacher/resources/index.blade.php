<x-app-layout>
    <x-slot name="title">Content Library — {{ $course->title }}</x-slot>

    <div class="max-w-3xl">
        <div class="page-header flex items-center gap-3 mb-6">
            <a href="{{ route('teacher.courses.show', $course) }}"
               class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </a>
            <div>
                <h1 class="page-title">Content Library</h1>
                <p class="page-subtitle">{{ $course->title }}</p>
            </div>
        </div>

        @if(session('success'))
        <div class="mb-5 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
            <svg class="w-4 h-4 flex-shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- Upload form --}}
        <div class="card p-6 mb-6" x-data="{ resourceType: 'pdf' }">
            <h3 class="font-semibold text-slate-900 mb-4">Add Resource</h3>
            <form action="{{ route('teacher.resources.store', $course) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-input" required placeholder="e.g. Chapter 5 Notes (PDF)">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select name="type" x-model="resourceType" class="form-select">
                            @foreach(['pdf' => 'PDF Document','video' => 'Video','audio' => 'Audio','image' => 'Image','link' => 'External Link','document' => 'Word/Doc','spreadsheet' => 'Spreadsheet','other' => 'Other'] as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description <span class="text-slate-400 font-normal">(optional)</span></label>
                    <textarea name="description" class="form-textarea" rows="2" placeholder="Brief description for students..."></textarea>
                </div>

                <div x-show="resourceType !== 'link'" class="form-group">
                    <label class="form-label">File</label>
                    <input type="file" name="file" class="form-input text-sm">
                    <p class="form-hint">Max 100 MB.</p>
                </div>

                <div x-show="resourceType === 'link'" class="form-group">
                    <label class="form-label">URL</label>
                    <input type="url" name="external_url" class="form-input" placeholder="https://...">
                </div>

                <div class="flex items-center gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_downloadable" value="1" checked class="accent-emerald-500">
                        <span class="text-sm text-slate-600">Students can download</span>
                    </label>
                </div>

                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                    Upload Resource
                </button>
            </form>
        </div>

        {{-- Resource list --}}
        <h3 class="section-title mb-3">All Resources ({{ $resources->count() }})</h3>

        @if($resources->isEmpty())
        <div class="card empty-state">
            <span class="empty-state-icon">📁</span>
            <p class="empty-state-title">No resources yet</p>
            <p class="empty-state-text">Upload PDFs, videos, or links for your students.</p>
        </div>
        @else
        @php
        $typeIcons = ['pdf' => '📄','video' => '🎬','audio' => '🎵','image' => '🖼️','link' => '🔗','document' => '📝','spreadsheet' => '📊','other' => '📁'];
        @endphp
        <div class="space-y-2">
            @foreach($resources as $resource)
            <div class="card p-4 flex items-center gap-4">
                <span class="text-2xl flex-shrink-0">{{ $typeIcons[$resource->type] ?? '📁' }}</span>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-slate-900 truncate">{{ $resource->title }}</p>
                    <p class="text-xs text-slate-400">
                        {{ strtoupper($resource->type) }}
                        @if($resource->file_size_bytes > 0) &middot; {{ $resource->file_size_formatted }} @endif
                        @if($resource->lesson) &middot; {{ $resource->lesson->title }} @endif
                        &middot; {{ $resource->download_count }} downloads
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($resource->url)
                    <a href="{{ $resource->url }}" target="_blank" rel="noopener" class="btn-secondary btn-sm">View</a>
                    @endif
                    <form action="{{ route('teacher.resources.destroy', [$course, $resource]) }}"
                          method="POST" onsubmit="return confirm('Delete this resource?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger btn-sm">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</x-app-layout>
