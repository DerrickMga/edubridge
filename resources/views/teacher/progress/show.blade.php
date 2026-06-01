@extends('layouts.app')
@section('page-title', $course->title.' — Student Progress')

@section('content')
<div class="space-y-5">
    <div class="flex items-end justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Student Progress</h1>
            <p class="text-sm text-slate-500">{{ $course->title }} · {{ $totalLessons }} lessons</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('teacher.courses.gradebook', $course) }}" class="text-sm px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50">Grade book</a>
            <a href="{{ route('teacher.courses.show', $course) }}" class="text-sm text-slate-500 hover:underline self-center">← Back</a>
        </div>
    </div>

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                <tr><th class="text-left px-4 py-2">Student</th><th>Lessons done</th><th>Progress</th><th>Last activity</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($rows as $r)
                <tr>
                    <td class="px-4 py-2">{{ $r->student->name }}<br><span class="text-xs text-slate-400">{{ $r->student->email }}</span></td>
                    <td class="px-2">{{ $r->done }} / {{ $r->total }}</td>
                    <td class="px-2 w-1/3">
                        <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-2 bg-emerald-500" style="width: {{ $r->percent }}%"></div>
                        </div>
                        <span class="text-xs text-slate-500">{{ $r->percent }}%</span>
                    </td>
                    <td class="px-2 text-xs text-slate-500">{{ $r->last_at ? \Illuminate\Support\Carbon::parse($r->last_at)->diffForHumans() : 'No activity' }}</td>
                    <td class="px-2 text-right">
                        <a href="{{ route('teacher.courses.progress.student', [$course, $r->student]) }}" class="text-xs text-slate-600 hover:underline">Drill down →</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-400">No students enrolled.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
