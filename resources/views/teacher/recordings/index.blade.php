<x-app-layout>
    <x-slot name="title">Recordings — {{ $liveSession->title }}</x-slot>

    <div class="max-w-3xl">
        <div class="page-header flex items-center gap-3 mb-6">
            <a href="{{ route('teacher.dashboard') }}"
               class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </a>
            <div>
                <h1 class="page-title">Session Recordings</h1>
                <p class="page-subtitle">{{ $liveSession->title }} &middot; {{ $liveSession->scheduled_at->format('d M Y') }}</p>
            </div>
        </div>

        @if(session('success'))
        <div class="mb-5 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
            <svg class="w-4 h-4 flex-shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- Add Recording Form --}}
        <div class="card p-6 mb-6" x-data="{ sourceType: 'external' }">
            <h3 class="font-semibold text-slate-900 mb-4">Add Recording</h3>
            <form action="{{ route('teacher.recordings.store', $liveSession) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="form-group">
                    <label class="form-label">Recording Title</label>
                    <input type="text" name="title" class="form-input" placeholder="e.g. Session 1 — Algebra recap" required>
                </div>

                <div class="flex gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="source_type" value="external" x-model="sourceType" class="accent-emerald-500">
                        <span class="text-sm font-medium text-slate-700">Link (Zoom/YouTube/Meet)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="source_type" value="upload" x-model="sourceType" class="accent-emerald-500">
                        <span class="text-sm font-medium text-slate-700">Upload file</span>
                    </label>
                </div>

                <div x-show="sourceType === 'external'" class="space-y-4">
                    <div class="form-group">
                        <label class="form-label">Recording URL</label>
                        <input type="url" name="external_url" class="form-input" placeholder="https://zoom.us/rec/... or https://youtu.be/...">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Platform</label>
                        <select name="source" class="form-select">
                            <option value="zoom">Zoom Cloud Recording</option>
                            <option value="google_meet">Google Meet</option>
                            <option value="youtube">YouTube</option>
                        </select>
                    </div>
                </div>

                <div x-show="sourceType === 'upload'" class="form-group">
                    <label class="form-label">Video File</label>
                    <input type="file" name="file" accept="video/*" class="form-input text-sm">
                    <p class="form-hint">Max 2 GB. MP4, MOV, AVI, WebM accepted.</p>
                </div>

                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Add Recording
                </button>
            </form>
        </div>

        {{-- Recording List --}}
        <h3 class="section-title mb-3">All Recordings ({{ $recordings->count() }})</h3>

        @if($recordings->isEmpty())
        <div class="card empty-state">
            <span class="empty-state-icon">🎬</span>
            <p class="empty-state-title">No recordings yet</p>
            <p class="empty-state-text">Add a Zoom cloud recording link or upload a video above.</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($recordings as $recording)
            <div class="card p-4 flex items-center gap-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center">
                    @if($recording->source === 'zoom')
                        <span class="text-blue-600 font-bold text-xs">ZM</span>
                    @elseif($recording->source === 'youtube')
                        <span class="text-red-600 font-bold text-xs">YT</span>
                    @else
                        <svg class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-slate-900 truncate">{{ $recording->title }}</p>
                    <p class="text-xs text-slate-400">
                        {{ ucfirst($recording->source) }}
                        @if($recording->file_size_bytes > 0) &middot; {{ $recording->file_size_formatted }} @endif
                        &middot; {{ $recording->created_at->diffForHumans() }}
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($recording->url)
                    <a href="{{ $recording->url }}" target="_blank" rel="noopener"
                       class="btn-secondary btn-sm">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                        View
                    </a>
                    @endif
                    <form action="{{ route('teacher.recordings.destroy', [$liveSession, $recording]) }}"
                          method="POST" onsubmit="return confirm('Delete this recording?')">
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
