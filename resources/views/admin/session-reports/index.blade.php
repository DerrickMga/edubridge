<x-app-layout>
    <x-slot name="title">Session Reports</x-slot>

    <div class="page-header flex items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="page-title">AI Session Reports</h1>
            <p class="page-subtitle">All past live sessions — AI summaries, attendance, teacher logs, and payment status.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
        {{ session('success') }}
    </div>
    @endif

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600">Session</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600 hidden sm:table-cell">Teacher</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600 hidden md:table-cell">Date</th>
                    <th class="text-center px-4 py-3 font-semibold text-slate-600">Attendees</th>
                    <th class="text-center px-4 py-3 font-semibold text-slate-600 hidden lg:table-cell">AI Report</th>
                    <th class="text-center px-4 py-3 font-semibold text-slate-600 hidden lg:table-cell">Teacher Log</th>
                    <th class="text-center px-4 py-3 font-semibold text-slate-600">Payment</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($sessions as $session)
                @php $ai = $session->aiReport; $log = $session->sessionLog; $pay = $session->paymentItem; @endphp
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <p class="font-semibold text-slate-800">{{ Str::limit($session->title, 40) }}</p>
                        <p class="text-xs text-slate-400">{{ $session->course->title ?? '—' }} · {{ $session->provider }}</p>
                    </td>
                    <td class="px-4 py-3 hidden sm:table-cell text-slate-700">{{ $session->teacher->name ?? '—' }}</td>
                    <td class="px-4 py-3 hidden md:table-cell text-slate-500 whitespace-nowrap">
                        {{ $session->scheduled_at->format('d M Y g:i a') }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="font-semibold text-slate-800">{{ $ai?->attendees_count ?? $session->attendances->count() }}</span>
                        @if($log)<span class="text-xs text-slate-400 block">log: {{ $log->actual_student_count }}</span>@endif
                    </td>
                    <td class="px-4 py-3 text-center hidden lg:table-cell">
                        @if($ai && $ai->processed_at)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-violet-100 text-violet-700 text-xs font-medium">✓ Done</span>
                        @elseif($ai && $ai->error)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-red-100 text-red-600 text-xs font-medium">Error</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 text-xs">Pending</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center hidden lg:table-cell">
                        @if($log)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-xs font-medium">✓ Submitted</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-amber-100 text-amber-600 text-xs font-medium">Awaiting</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($pay)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $pay->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : ($pay->status === 'approved' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">
                                ${{ number_format($pay->total_usd, 2) }} · {{ ucfirst($pay->status) }}
                            </span>
                        @else
                            <span class="text-xs text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.session-reports.show', $session) }}"
                           class="text-xs font-semibold text-blue-600 hover:text-blue-800">View →</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-10 text-center text-slate-400">No past sessions found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $sessions->links() }}
        </div>
    </div>
</x-app-layout>
