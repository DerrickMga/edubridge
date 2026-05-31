<x-app-layout>
    <x-slot name="title">Discussion – {{ $lesson->title }}</x-slot>

    <div class="max-w-3xl">
        <div class="page-header flex items-center gap-3 mb-6">
            <a href="{{ route('student.lessons.show', $lesson) }}"
               class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </a>
            <div>
                <p class="text-xs text-slate-400 mb-0.5">{{ $lesson->course->title }} / Discussion</p>
                <h1 class="page-title">{{ $lesson->title }}</h1>
            </div>
        </div>

        @if(session('success'))
        <div class="mb-5 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- Post new comment --}}
        <div class="card p-5 mb-6">
            <h3 class="font-semibold text-slate-800 mb-3 text-sm">Ask a question or share a thought</h3>
            <form method="POST" action="{{ route('student.discussions.store', $lesson) }}" class="space-y-3">
                @csrf
                @error('body')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                <textarea name="body" rows="3" class="form-textarea text-sm"
                    placeholder="Type your question or comment…">{{ old('body') }}</textarea>
                <div class="flex justify-end">
                    <button type="submit" class="btn-primary btn-sm">Post comment</button>
                </div>
            </form>
        </div>

        {{-- Threads --}}
        @if($threads->isEmpty())
        <div class="card p-8 text-center">
            <p class="text-slate-400 text-sm">No comments yet. Be the first to ask a question!</p>
        </div>
        @else
        <div class="space-y-5">
            @foreach($threads as $thread)
            <div class="card overflow-hidden {{ $thread->is_pinned ? 'border-amber-200 bg-amber-50/30' : '' }}">
                <div class="p-4">
                    @if($thread->is_pinned)
                    <span class="inline-flex items-center gap-1 text-xs text-amber-600 font-semibold mb-2">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="m15.75 2.25-9 9 .75 3.75 3.75.75 9-9-4.5-4.5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 21.75 9 18"/></svg>
                        Pinned
                    </span>
                    @endif
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-violet-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                            {{ strtoupper(substr($thread->author->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-semibold text-sm text-slate-800">{{ $thread->author->name }}</span>
                                <span class="text-xs text-slate-400">{{ $thread->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-slate-700 mt-1 leading-relaxed">{{ $thread->body }}</p>
                            <div class="flex items-center gap-3 mt-2">
                                @if(auth()->id() === $thread->author_id || auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('student.discussions.destroy', $thread) }}">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-slate-400 hover:text-red-500 transition-colors" onclick="return confirm('Delete this comment?')">Delete</button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Replies --}}
                @if($thread->replies->isNotEmpty())
                <div class="border-t border-slate-100 bg-slate-50/60 divide-y divide-slate-100">
                    @foreach($thread->replies as $reply)
                    <div class="p-4 pl-14">
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                {{ strtoupper(substr($reply->author->name, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-xs text-slate-700">{{ $reply->author->name }}</span>
                                    <span class="text-xs text-slate-400">{{ $reply->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-slate-600 mt-0.5 leading-relaxed">{{ $reply->body }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Reply form --}}
                <div class="border-t border-slate-100 p-3">
                    <form method="POST" action="{{ route('student.discussions.store', $lesson) }}" class="flex gap-2">
                        @csrf
                        <input type="hidden" name="parent_id" value="{{ $thread->id }}">
                        <input type="text" name="body" class="form-input text-sm flex-1 py-1.5"
                               placeholder="Reply to {{ $thread->author->name }}…" required>
                        <button type="submit" class="btn-secondary btn-sm flex-shrink-0">Reply</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</x-app-layout>
