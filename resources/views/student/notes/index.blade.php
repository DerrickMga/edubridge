<x-app-layout>
    <x-slot name="title">My Notes</x-slot>

    <div class="space-y-6 max-w-4xl">
        <div class="page-header">
            <div>
                <h1 class="page-title">My Notes</h1>
                <p class="page-subtitle">Personal notes you've taken across your enrolled lessons.</p>
            </div>
        </div>

        @if($notes->isEmpty())
        <div class="flex flex-col items-center justify-center py-20 text-center">
            <svg class="w-12 h-12 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
            </svg>
            <p class="text-slate-500 font-medium">No notes yet</p>
            <p class="text-slate-400 text-sm mt-1">Take notes while watching lessons — they'll appear here.</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($notes as $n)
            <div class="card p-4 flex gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2 flex-wrap">
                        <div class="text-xs text-slate-500">
                            @if($n->lesson)
                                <a href="{{ route('student.lessons.show', $n->lesson) }}" class="hover:underline text-violet-600 font-medium">
                                    {{ optional($n->lesson->course)->title }} &middot; {{ $n->lesson->title }}
                                </a>
                            @endif
                            @if($n->timestamp_seconds !== null)
                                &middot; <span class="font-mono">{{ $n->formatted_timestamp }}</span>
                            @endif
                        </div>
                        <span class="text-xs text-slate-400 whitespace-nowrap">{{ $n->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-slate-700 mt-2 whitespace-pre-wrap">{{ $n->body }}</p>
                </div>
                <form method="POST" action="{{ route('notes.destroy', $n) }}"
                      onsubmit="return confirm('Delete this note?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-slate-300 hover:text-red-500 transition-colors mt-0.5" title="Delete note">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                        </svg>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
        {{ $notes->links() }}
        @endif
    </div>
</x-app-layout>
