<x-app-layout>
    <x-slot name="title">Teacher Payments</x-slot>

    <div class="page-header flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="page-title">Teacher Payments</h1>
            <p class="page-subtitle">Review, approve, and track all teacher session payment claims.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    {{-- Totals ──────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="stat-card border-l-4 border-amber-400">
            <p class="stat-value text-amber-700">${{ number_format($totals['pending'], 2) }}</p>
            <p class="stat-label">Pending</p>
        </div>
        <div class="stat-card border-l-4 border-blue-500">
            <p class="stat-value text-blue-700">${{ number_format($totals['approved'], 2) }}</p>
            <p class="stat-label">Approved</p>
        </div>
        <div class="stat-card border-l-4 border-emerald-500">
            <p class="stat-value text-emerald-700">${{ number_format($totals['paid'], 2) }}</p>
            <p class="stat-label">Paid out</p>
        </div>
    </div>

    {{-- Filters ─────────────────────────────────────────────────────────── --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-5">
        <select name="status" class="form-input text-sm w-auto">
            <option value="">All statuses</option>
            @foreach(['pending','approved','paid'] as $s)
            <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <select name="teacher_id" class="form-input text-sm w-auto">
            <option value="">All teachers</option>
            @foreach($teachers as $t)
            <option value="{{ $t->id }}" @selected(request('teacher_id') == $t->id)>{{ $t->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors">Filter</button>
        <a href="{{ route('admin.teacher-payments.index') }}" class="px-4 py-2 rounded-lg border border-slate-200 text-sm text-slate-600 hover:bg-slate-50 transition-colors">Clear</a>
    </form>

    {{-- Table ───────────────────────────────────────────────────────────── --}}
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600">Session</th>
                    <th class="text-left px-4 py-3 font-semibold text-slate-600">Teacher</th>
                    <th class="text-right px-4 py-3 font-semibold text-slate-600">Hours</th>
                    <th class="text-right px-4 py-3 font-semibold text-slate-600">Rate</th>
                    <th class="text-right px-4 py-3 font-semibold text-slate-600">Total</th>
                    <th class="text-center px-4 py-3 font-semibold text-slate-600">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($payments as $pay)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <p class="font-semibold text-slate-800">{{ Str::limit($pay->session->title ?? '—', 35) }}</p>
                        <p class="text-xs text-slate-400">{{ $pay->session?->scheduled_at?->format('d M Y') }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-slate-700">{{ $pay->teacher->name ?? '—' }}</p>
                        <form action="{{ route('admin.teacher-payments.update-rate', $pay->teacher) }}"
                              method="POST" class="flex items-center gap-1 mt-1">
                            @csrf
                            <span class="text-xs text-slate-400">Rate $</span>
                            <input type="number" name="hourly_rate_usd" step="0.01" min="0"
                                   value="{{ $pay->teacher->hourly_rate_usd ?? 15 }}"
                                   class="w-16 text-xs border border-slate-200 rounded px-1 py-0.5">
                            <button class="text-xs text-blue-600 hover:text-blue-800 font-medium">Save</button>
                        </form>
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-700">{{ $pay->hours_logged }}h</td>
                    <td class="px-4 py-3 text-right text-slate-600">${{ number_format($pay->hourly_rate_usd, 2) }}</td>
                    <td class="px-4 py-3 text-right font-bold text-slate-900">${{ number_format($pay->total_usd, 2) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold
                            {{ $pay->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : ($pay->status === 'approved' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">
                            {{ strtoupper($pay->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-end gap-2">
                            @if($pay->status === 'pending')
                            <form action="{{ route('admin.teacher-payments.approve', $pay) }}" method="POST">
                                @csrf
                                <button class="text-xs px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold transition-colors">Approve</button>
                            </form>
                            @elseif($pay->status === 'approved')
                            <form action="{{ route('admin.teacher-payments.mark-paid', $pay) }}" method="POST">
                                @csrf
                                <button class="text-xs px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold transition-colors">Mark Paid</button>
                            </form>
                            <form action="{{ route('admin.teacher-payments.reject', $pay) }}" method="POST">
                                @csrf
                                <input type="hidden" name="admin_notes" value="Returned by admin">
                                <button class="text-xs px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-600 font-semibold transition-colors">Reject</button>
                            </form>
                            @endif
                            <a href="{{ route('admin.session-reports.show', $pay->live_session_id) }}"
                               class="text-xs text-slate-400 hover:text-blue-600">View →</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-slate-400">No payment claims yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $payments->links() }}
        </div>
    </div>
</x-app-layout>
