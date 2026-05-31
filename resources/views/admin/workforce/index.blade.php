@extends('layouts.app')

@section('page-title', 'Workforce Management')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Workforce</h1>
            <p class="text-sm text-slate-500">Week of {{ $weekStart->format('D, d M') }} – {{ $weekEnd->format('d M Y') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.workforce.rota') }}" class="px-3 py-2 text-sm rounded-lg border border-slate-200 hover:bg-slate-50">Open rota grid</a>
            <form method="POST" action="{{ route('admin.workforce.auto-assign') }}">@csrf
                <button class="px-3 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800">⚡ Auto-assign upcoming</button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-800 text-sm">{{ session('success') }}</div>
    @endif

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        @php
            $kpis = [
                ['label' => 'Teachers',         'value' => $totals['teachers']],
                ['label' => 'Online now',       'value' => $totals['online_now']],
                ['label' => 'Hours this week',  'value' => number_format($totals['weekly_hours'], 1)],
                ['label' => 'Payout this week', 'value' => '$'.number_format($totals['weekly_payout'], 2)],
                ['label' => 'Unassigned sessions', 'value' => $totals['unassigned']],
            ];
        @endphp
        @foreach($kpis as $k)
        <div class="card p-4">
            <p class="text-xs uppercase tracking-wide text-slate-400">{{ $k['label'] }}</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $k['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Teacher table --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800 text-sm">Teacher load this week</h2>
            <span class="text-xs text-slate-400">Sorted by hours, then status</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50/60 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-2 text-left">Teacher</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-left">Last seen</th>
                        <th class="px-4 py-2 text-right">Hours</th>
                        <th class="px-4 py-2 text-right">Payout</th>
                        <th class="px-4 py-2 text-right">Upcoming</th>
                        <th class="px-4 py-2 text-right">Completed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($rows->sortByDesc('weekly_hours') as $r)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $r->is_online ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                <span class="font-medium text-slate-900">{{ $r->teacher->name }}</span>
                                @if(! $r->accepts)<span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-50 text-amber-700">not accepting</span>@endif
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $r->teacher->email }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs capitalize px-2 py-0.5 rounded-full
                                {{ $r->status === 'online' ? 'bg-emerald-50 text-emerald-700' :
                                   ($r->status === 'busy' ? 'bg-amber-50 text-amber-700' :
                                   ($r->status === 'away' ? 'bg-slate-100 text-slate-600' : 'bg-slate-50 text-slate-400')) }}">
                                {{ $r->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500">
                            {{ $r->last_seen ? $r->last_seen->diffForHumans() : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right font-medium">{{ number_format($r->weekly_hours, 1) }}</td>
                        <td class="px-4 py-3 text-right">${{ number_format($r->weekly_payout, 2) }}</td>
                        <td class="px-4 py-3 text-right">{{ $r->upcoming_shifts }}</td>
                        <td class="px-4 py-3 text-right">{{ $r->completed_shifts }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
