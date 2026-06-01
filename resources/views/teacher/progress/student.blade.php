@extends('layouts.app')
@section('page-title', $student->name.' — '.$course->title)

@section('content')
<div class="space-y-5 max-w-4xl">
    <div class="flex items-end justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $student->name }}</h1>
            <p class="text-sm text-slate-500">{{ $course->title }}</p>
        </div>
        <a href="{{ route('teacher.courses.progress', $course) }}" class="text-sm text-slate-500 hover:underline">← All students</a>
    </div>

    <div class="card p-5">
        <h2 class="font-semibold text-slate-800 text-sm mb-2">Lessons</h2>
        <ul class="divide-y divide-slate-100 text-sm">
            @foreach($lessons as $l)
                @php $p = $progress->get($l->id); @endphp
                <li class="py-2 flex items-center justify-between">
                    <span class="{{ $p && $p->completed ? 'text-emerald-700 font-medium' : 'text-slate-700' }}">{{ $l->order }}. {{ $l->title }}</span>
                    <span class="text-xs text-slate-400">
                        @if($p && $p->completed) ✅ Done {{ optional($p->completed_at)->diffForHumans() }}
                        @elseif($p && $p->watch_seconds) ▶ {{ gmdate('i:s', (int) $p->watch_seconds) }}
                        @else Not started @endif
                    </span>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div class="card p-5">
            <h2 class="font-semibold text-slate-800 text-sm mb-2">Assignment submissions</h2>
            @if($submissions->isEmpty())
                <p class="text-xs text-slate-400">No submissions.</p>
            @else
            <ul class="divide-y divide-slate-100 text-sm">
                @foreach($submissions as $s)
                <li class="py-2 flex items-center justify-between">
                    <span>{{ optional($s->assignment)->title }}</span>
                    <span class="text-xs">
                        @if($s->status === 'graded')
                            <span class="font-bold {{ $s->score / max(1, $s->assignment->max_score) >= 0.5 ? 'text-emerald-700' : 'text-rose-600' }}">{{ $s->score }}/{{ $s->assignment->max_score }}</span>
                        @else
                            <span class="text-amber-600 capitalize">{{ $s->status }}</span>
                        @endif
                    </span>
                </li>
                @endforeach
            </ul>
            @endif
        </div>
        <div class="card p-5">
            <h2 class="font-semibold text-slate-800 text-sm mb-2">Quiz attempts</h2>
            @if($attempts->isEmpty())
                <p class="text-xs text-slate-400">No attempts.</p>
            @else
            <ul class="divide-y divide-slate-100 text-sm">
                @foreach($attempts as $a)
                <li class="py-2 flex items-center justify-between">
                    <span>{{ optional($a->quiz)->title }} <span class="text-xs text-slate-400">#{{ $a->attempt_number }}</span></span>
                    <span class="text-xs font-bold {{ $a->passed ? 'text-emerald-700' : 'text-rose-600' }}">{{ $a->score }}/{{ $a->max_score }}</span>
                </li>
                @endforeach
            </ul>
            @endif
        </div>
    </div>
</div>
@endsection
