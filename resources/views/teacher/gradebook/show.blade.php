@extends('layouts.app')
@section('page-title', $course->title.' — Grade Book')

@section('content')
<div class="space-y-5">
    <div class="flex items-end justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Grade Book</h1>
            <p class="text-sm text-slate-500">{{ $course->title }} · {{ $students->count() }} students</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('teacher.courses.progress', $course) }}" class="text-sm px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50">Progress view</a>
            <a href="{{ route('teacher.courses.show', $course) }}" class="text-sm text-slate-500 hover:underline self-center">← Back to course</a>
        </div>
    </div>

    <div class="card overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase sticky top-0">
                <tr>
                    <th class="text-left px-4 py-2">Student</th>
                    @foreach($assignments as $a)
                        <th class="px-2 py-2" title="{{ $a->title }}">A: {{ \Illuminate\Support\Str::limit($a->title, 16) }}<br><span class="text-[10px] normal-case text-slate-400">/{{ $a->max_score }}</span></th>
                    @endforeach
                    @foreach($quizzes as $q)
                        <th class="px-2 py-2" title="{{ $q->title }}">Q: {{ \Illuminate\Support\Str::limit($q->title, 16) }}<br><span class="text-[10px] normal-case text-slate-400">pass {{ $q->pass_percentage }}%</span></th>
                    @endforeach
                    <th class="px-2 py-2">Average</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($students as $s)
                @php
                    $scores = [];
                    foreach ($assignments as $a) {
                        $sub = ($subs[$s->id.'-'.$a->id] ?? collect())->first();
                        if ($sub && $sub->status === 'graded') $scores[] = ($sub->score / max(1, $a->max_score)) * 100;
                    }
                    foreach ($quizzes as $q) {
                        $latest = ($attempts[$s->id.'-'.$q->id] ?? collect())->sortByDesc('attempt_number')->first();
                        if ($latest) $scores[] = ($latest->score / max(1, $latest->max_score)) * 100;
                    }
                    $avg = count($scores) ? (int) round(array_sum($scores) / count($scores)) : null;
                @endphp
                <tr>
                    <td class="px-4 py-2 whitespace-nowrap">
                        <a href="{{ route('teacher.courses.progress.student', [$course, $s]) }}" class="hover:underline">{{ $s->name }}</a>
                        <p class="text-[10px] text-slate-400">{{ $s->email }}</p>
                    </td>
                    @foreach($assignments as $a)
                        @php $sub = ($subs[$s->id.'-'.$a->id] ?? collect())->first(); @endphp
                        <td class="px-2 py-2 text-center">
                            @if($sub && $sub->status === 'graded')
                                <span class="text-xs font-semibold {{ $sub->score / max(1,$a->max_score) >= 0.5 ? 'text-emerald-700' : 'text-rose-600' }}">{{ $sub->score }}</span>
                            @elseif($sub)
                                <span class="text-xs text-amber-600">⏳</span>
                            @else
                                <span class="text-xs text-slate-300">—</span>
                            @endif
                        </td>
                    @endforeach
                    @foreach($quizzes as $q)
                        @php $latest = ($attempts[$s->id.'-'.$q->id] ?? collect())->sortByDesc('attempt_number')->first(); @endphp
                        <td class="px-2 py-2 text-center">
                            @if($latest)
                                <span class="text-xs font-semibold {{ $latest->passed ? 'text-emerald-700' : 'text-rose-600' }}">{{ $latest->score }}/{{ $latest->max_score }}</span>
                            @else
                                <span class="text-xs text-slate-300">—</span>
                            @endif
                        </td>
                    @endforeach
                    <td class="px-2 py-2 text-center font-bold {{ $avg !== null && $avg >= 50 ? 'text-emerald-700' : ($avg !== null ? 'text-rose-600' : 'text-slate-300') }}">
                        {{ $avg !== null ? $avg.'%' : '—' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="99" class="px-4 py-6 text-center text-sm text-slate-400">No enrolled students yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
