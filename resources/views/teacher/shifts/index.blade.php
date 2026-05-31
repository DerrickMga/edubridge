<x-app-layout>
<x-slot name="title">My Shifts</x-slot>
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-900">My Shifts</h1>
        <p class="text-sm text-slate-500">Scheduled teaching slots and hours.</p>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="card p-4">
            <p class="text-xs uppercase tracking-wide text-slate-400">This week</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($totals['this_week_hours'], 1) }} h</p>
        </div>
        <div class="card p-4">
            <p class="text-xs uppercase tracking-wide text-slate-400">This month</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ number_format($totals['this_month_hours'], 1) }} h</p>
        </div>
        <div class="card p-4">
            <p class="text-xs uppercase tracking-wide text-slate-400">Pending payout</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">${{ number_format($totals['pending_payout'], 2) }}</p>
        </div>
    </div>

    {{-- Upcoming --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800 text-sm">Upcoming ({{ $upcoming->count() }})</h2>
        </div>
        @if($upcoming->isEmpty())
            <p class="px-5 py-8 text-sm text-slate-400 text-center">No upcoming shifts.</p>
        @else
        <ul class="divide-y divide-slate-100">
            @foreach($upcoming as $shift)
            <li class="px-5 py-3 flex items-center justify-between">
                <div>
                    <p class="font-medium text-slate-900 text-sm">{{ $shift->title }}</p>
                    <p class="text-xs text-slate-500">
                        {{ $shift->starts_at->format('D, d M · H:i') }} → {{ $shift->ends_at->format('H:i') }}
                        @if($shift->course) · {{ $shift->course->title }} @endif
                        @if($shift->auto_assigned) · <span class="text-slate-400">⚡ auto-assigned</span> @endif
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold">{{ number_format($shift->duration_hours, 1) }} h</p>
                    <span class="text-[10px] px-1.5 py-0.5 rounded
                        {{ $shift->status === 'in_progress' ? 'bg-amber-50 text-amber-700' : 'bg-sky-50 text-sky-700' }}">
                        {{ str_replace('_', ' ', $shift->status) }}
                    </span>
                </div>
            </li>
            @endforeach
        </ul>
        @endif
    </div>

    {{-- Past --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800 text-sm">Completed ({{ $past->count() }})</h2>
        </div>
        @if($past->isEmpty())
            <p class="px-5 py-8 text-sm text-slate-400 text-center">No past shifts yet.</p>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50/60 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-2 text-left">When</th>
                        <th class="px-4 py-2 text-left">Title</th>
                        <th class="px-4 py-2 text-right">Hours</th>
                        <th class="px-4 py-2 text-right">Payout</th>
                        <th class="px-4 py-2 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($past as $shift)
                    <tr>
                        <td class="px-4 py-2 text-xs text-slate-500">{{ $shift->starts_at->format('d M, H:i') }}</td>
                        <td class="px-4 py-2">{{ $shift->title }}@if($shift->course)<span class="text-xs text-slate-400 ml-1">· {{ $shift->course->title }}</span>@endif</td>
                        <td class="px-4 py-2 text-right">{{ number_format($shift->hours_worked ?? $shift->duration_hours, 1) }}</td>
                        <td class="px-4 py-2 text-right">${{ number_format($shift->payout_amount_usd ?? 0, 2) }}</td>
                        <td class="px-4 py-2">
                            <span class="text-[10px] px-1.5 py-0.5 rounded
                                {{ $shift->status === 'completed' ? 'bg-emerald-50 text-emerald-700' :
                                   ($shift->status === 'missed' ? 'bg-rose-50 text-rose-700' : 'bg-slate-50 text-slate-600') }}">
                                {{ str_replace('_', ' ', $shift->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>
</x-app-layout>
