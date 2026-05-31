@extends('layouts.app')

@section('page-title', 'Weekly Rota')

@section('content')
<div class="space-y-6">

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Weekly Rota</h1>
            <p class="text-sm text-slate-500">{{ $weekStart->format('D, d M') }} – {{ $weekEnd->format('d M Y') }}</p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <a href="{{ route('admin.workforce.rota', ['week' => $weekStart->copy()->subWeek()->toDateString()]) }}" class="px-3 py-2 text-sm rounded-lg border border-slate-200 hover:bg-slate-50">← Prev</a>
            <input type="date" name="week" value="{{ $weekStart->toDateString() }}" class="px-3 py-2 text-sm rounded-lg border border-slate-200" onchange="this.form.submit()">
            <a href="{{ route('admin.workforce.rota', ['week' => $weekStart->copy()->addWeek()->toDateString()]) }}" class="px-3 py-2 text-sm rounded-lg border border-slate-200 hover:bg-slate-50">Next →</a>
            <a href="{{ route('admin.workforce.index') }}" class="px-3 py-2 text-sm rounded-lg border border-slate-200 hover:bg-slate-50">Overview</a>
        </form>
    </div>

    @if(session('success'))
        <div class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-800 text-sm">{{ session('success') }}</div>
    @endif

    <div class="card overflow-x-auto">
        <table class="min-w-full text-xs">
            <thead class="bg-slate-50/60">
                <tr>
                    <th class="px-3 py-2 text-left text-slate-500 uppercase tracking-wide sticky left-0 bg-slate-50 z-10">Teacher</th>
                    @foreach($days as $d)
                        <th class="px-3 py-2 text-left text-slate-500 uppercase tracking-wide min-w-[140px]">
                            {{ $d->format('D') }}<br><span class="text-slate-400 font-normal">{{ $d->format('d M') }}</span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($teachers as $t)
                <tr>
                    <td class="px-3 py-2 sticky left-0 bg-white z-10 align-top">
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full {{ $t->isOnline() ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                            <span class="font-medium text-slate-900 whitespace-nowrap">{{ $t->name }}</span>
                        </div>
                    </td>
                    @php $teacherShifts = $shifts->get($t->id, collect()); @endphp
                    @foreach($days as $d)
                        @php
                            $dayShifts = $teacherShifts->filter(fn($s) => $s->starts_at->isSameDay($d));
                        @endphp
                        <td class="px-2 py-2 align-top">
                            @foreach($dayShifts as $shift)
                                <div class="mb-1 px-2 py-1 rounded text-[11px] border-l-2
                                    {{ $shift->status === 'completed' ? 'bg-emerald-50 border-emerald-400 text-emerald-800' :
                                       ($shift->status === 'in_progress' ? 'bg-amber-50 border-amber-400 text-amber-800' :
                                       ($shift->status === 'missed' ? 'bg-rose-50 border-rose-400 text-rose-800' :
                                       ($shift->status === 'cancelled' ? 'bg-slate-50 border-slate-300 text-slate-500' :
                                       'bg-sky-50 border-sky-400 text-sky-800'))) }}">
                                    <p class="font-semibold leading-tight">{{ $shift->starts_at->format('H:i') }}–{{ $shift->ends_at->format('H:i') }}</p>
                                    <p class="truncate" title="{{ $shift->title }}">{{ $shift->title }}</p>
                                    @if($shift->course)<p class="text-slate-500 truncate">{{ $shift->course->title }}</p>@endif
                                    @if($shift->auto_assigned)<p class="text-[10px] text-slate-400">⚡ auto</p>@endif
                                </div>
                            @endforeach
                        </td>
                    @endforeach
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">No teachers yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Manual shift create --}}
    <div class="card p-5">
        <h2 class="font-semibold text-slate-800 mb-3">Add a manual shift</h2>
        <form method="POST" action="{{ route('admin.workforce.shifts.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3">
            @csrf
            <select name="teacher_id" required class="md:col-span-2 px-3 py-2 text-sm rounded-lg border border-slate-200">
                <option value="">Teacher…</option>
                @foreach($teachers as $t)
                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                @endforeach
            </select>
            <input type="text" name="title" placeholder="Title" required maxlength="200" class="md:col-span-2 px-3 py-2 text-sm rounded-lg border border-slate-200">
            <input type="datetime-local" name="starts_at" required class="px-3 py-2 text-sm rounded-lg border border-slate-200">
            <input type="datetime-local" name="ends_at" required class="px-3 py-2 text-sm rounded-lg border border-slate-200">
            <button class="md:col-span-6 px-3 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800">Create shift</button>
        </form>
    </div>

</div>
@endsection
