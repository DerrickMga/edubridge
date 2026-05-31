@extends('layouts.app')
@section('page-title', 'My Notes')

@section('content')
<div class="space-y-5">
    <h1 class="text-2xl font-bold text-slate-900">Lesson Notes</h1>
    <p class="text-sm text-slate-500">Personal notes you've taken across your enrolled lessons.</p>

    @if($notes->isEmpty())
        <div class="card p-8 text-center text-slate-500 text-sm">No notes yet. Take notes from any lesson page.</div>
    @else
    <div class="space-y-3">
        @foreach($notes as $n)
        <div class="card p-4">
            <div class="flex items-center justify-between">
                <div class="text-xs text-slate-400">
                    @if($n->lesson)
                        <a href="{{ route('student.lessons.show', $n->lesson) }}" class="hover:underline text-slate-600">
                            {{ optional($n->lesson->course)->title }} · {{ $n->lesson->title }}
                        </a>
                    @endif
                    @if($n->timestamp_seconds !== null)
                        · ⏱ {{ $n->formatted_timestamp }}
                    @endif
                </div>
                <form method="POST" action="{{ route('student.notes.destroy', $n) }}">
                    @csrf @method('DELETE')
                    <button class="text-xs text-rose-500 hover:underline">Delete</button>
                </form>
            </div>
            <p class="text-sm text-slate-700 mt-2 whitespace-pre-wrap">{{ $n->body }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $n->created_at->diffForHumans() }}</p>
        </div>
        @endforeach
    </div>
    {{ $notes->links() }}
    @endif
</div>
@endsection
