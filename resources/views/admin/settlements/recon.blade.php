<x-app-layout>
    <x-slot name="title">Settlement Reconciliation</x-slot>

    <div class="space-y-6">

        {{-- Header --}}
        <div class="page-header">
            <div>
                <h1 class="page-title">Settlement Reconciliation</h1>
                <p class="page-subtitle">Audit all paid settlements, track outstanding balances, and review per-teacher payout history.</p>
            </div>
            <a href="{{ route('admin.settlements.index') }}" class="btn btn-secondary btn-sm">← All Settlements</a>
        </div>

        {{-- Date filter --}}
        <form method="GET" action="{{ route('admin.settlements.reconciliation') }}" class="card p-4 flex flex-wrap items-end gap-4">
            <div class="form-group mb-0">
                <label class="form-label text-xs">From</label>
                <input type="date" name="from" value="{{ $from }}" class="form-input py-2">
            </div>
            <div class="form-group mb-0">
                <label class="form-label text-xs">To</label>
                <input type="date" name="to" value="{{ $to }}" class="form-input py-2">
            </div>
            <button type="submit" class="btn btn-primary btn-sm self-end">Apply Filter</button>
            <a href="{{ route('admin.settlements.reconciliation') }}" class="btn btn-secondary btn-sm self-end">This Month</a>
            <div class="ml-auto self-end text-xs text-slate-400">
                Period: <span class="font-medium text-slate-600">{{ \Carbon\Carbon::parse($from)->format('d M Y') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</span>
            </div>
        </form>

        {{-- Summary cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="stat-card">
                <p class="stat-label">Total Paid Out</p>
                <p class="stat-value text-2xl">${{ number_format($totals['paid_all_time'], 2) }}</p>
                <p class="text-xs text-slate-400 mt-1">All time</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Paid This Period</p>
                <p class="stat-value text-2xl text-emerald-600">${{ number_format($totals['paid_period'], 2) }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ \Carbon\Carbon::parse($from)->format('d M') }} – {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Outstanding</p>
                <p class="stat-value text-2xl text-amber-600">${{ number_format($totals['outstanding'], 2) }}</p>
                <p class="text-xs text-slate-400 mt-1">Pending / approved / processing</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Active Teachers</p>
                <p class="stat-value text-2xl">{{ $totals['total_teachers'] }}</p>
                <p class="text-xs text-slate-400 mt-1">With settlement requests</p>
            </div>
        </div>

        {{-- Per-teacher breakdown --}}
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="section-title">Per-Teacher Breakdown</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Cumulative totals across all time</p>
                </div>
                <span class="badge-slate badge">{{ $teacherBreakdown->count() }} teachers</span>
            </div>
            @if($teacherBreakdown->count())
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Teacher</th>
                            <th class="text-right">Total Paid</th>
                            <th class="text-right">Pending / In-Flight</th>
                            <th class="text-right">Requests</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teacherBreakdown as $row)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl overflow-hidden flex-shrink-0">
                                        @if($row->teacher?->avatar)
                                            <img src="{{ $row->teacher->avatar_url }}" class="w-full h-full object-cover" alt="">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white text-xs font-black uppercase">{{ substr($row->teacher?->name ?? '?', 0, 1) }}</div>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-800">{{ $row->teacher?->name ?? 'Unknown' }}</p>
                                        <p class="text-xs text-slate-400">{{ $row->teacher?->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-right">
                                <span class="font-bold text-emerald-700">${{ number_format($row->total_paid, 2) }}</span>
                            </td>
                            <td class="text-right">
                                @if($row->pending_amount > 0)
                                    <span class="font-semibold text-amber-600">${{ number_format($row->pending_amount, 2) }}</span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="text-right text-sm text-slate-500">{{ $row->total_count }}</td>
                            <td>
                                <a href="{{ route('admin.settlements.index', ['status' => 'all']) }}?teacher={{ $row->teacher_id }}" class="text-xs text-emerald-600 hover:underline">View</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50 border-t border-slate-200">
                            <td class="px-4 py-3 text-sm font-semibold text-slate-700">Totals</td>
                            <td class="px-4 py-3 text-right font-bold text-emerald-700">${{ number_format($teacherBreakdown->sum('total_paid'), 2) }}</td>
                            <td class="px-4 py-3 text-right font-bold text-amber-600">${{ number_format($teacherBreakdown->sum('pending_amount'), 2) }}</td>
                            <td class="px-4 py-3 text-right text-sm text-slate-500">{{ $teacherBreakdown->sum('total_count') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="empty-state">
                <span class="empty-state-icon">📊</span>
                <p class="empty-state-title">No settlement data</p>
                <p class="empty-state-text">No teachers have submitted settlement requests yet.</p>
            </div>
            @endif
        </div>

        {{-- Paid Settlements in Period --}}
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="section-title">Paid Settlements — Period Audit</h2>
                    <p class="text-xs text-slate-400 mt-0.5">
                        {{ $paidSettlements->total() }} payment{{ $paidSettlements->total() !== 1 ? 's' : '' }} totalling
                        <span class="font-semibold text-emerald-700">${{ number_format($totals['paid_period'], 2) }}</span>
                        in selected period
                    </p>
                </div>
                <span class="badge-green badge">{{ $paidSettlements->total() }} paid</span>
            </div>

            @if($paidSettlements->count())
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date Paid</th>
                            <th>Teacher</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Payout Account</th>
                            <th>Reference</th>
                            <th>Processed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $periodTotal = 0; @endphp
                        @foreach($paidSettlements as $s)
                        @php $periodTotal += $s->amount_usd; @endphp
                        <tr>
                            <td class="text-xs text-slate-500 whitespace-nowrap">{{ $s->processed_at?->format('d M Y H:i') }}</td>
                            <td>
                                <p class="text-sm font-medium">{{ $s->teacher?->name }}</p>
                                <p class="text-xs text-slate-400">{{ $s->teacher?->email }}</p>
                            </td>
                            <td class="font-bold text-emerald-700">${{ number_format($s->amount_usd, 2) }}</td>
                            <td class="text-sm capitalize">{{ str_replace('_', ' ', $s->payment_method) }}</td>
                            <td class="text-xs text-slate-500">
                                @if(is_array($s->payout_details))
                                    {{ $s->payout_details['account_name'] ?? '' }}
                                    @if(isset($s->payout_details['account_number']))
                                        <br><span class="font-mono">{{ $s->payout_details['account_number'] }}</span>
                                    @elseif(isset($s->payout_details['email']))
                                        <br>{{ $s->payout_details['email'] }}
                                    @endif
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.settlements.show', $s) }}" class="text-xs font-mono text-emerald-600 hover:underline">{{ $s->reference_number }}</a>
                            </td>
                            <td class="text-xs text-slate-400">{{ $s->processor?->name ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-emerald-50 border-t border-emerald-100">
                            <td colspan="2" class="px-4 py-3 text-sm font-semibold text-emerald-800">Period Total</td>
                            <td class="px-4 py-3 font-bold text-emerald-700">${{ number_format($totals['paid_period'], 2) }}</td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="px-6 py-3">{{ $paidSettlements->withQueryString()->links() }}</div>
            @else
            <div class="empty-state">
                <span class="empty-state-icon">💸</span>
                <p class="empty-state-title">No paid settlements in this period</p>
                <p class="empty-state-text">Adjust the date range to see different periods.</p>
            </div>
            @endif
        </div>

        {{-- Rejected / outstanding alert --}}
        @if($totals['rejected_count'] > 0)
        <div class="alert alert-warn">
            <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
            <span>{{ $totals['rejected_count'] }} settlement request{{ $totals['rejected_count'] !== 1 ? 's have' : ' has' }} been rejected. <a href="{{ route('admin.settlements.index', ['status' => 'rejected']) }}" class="underline font-medium">Review them →</a></span>
        </div>
        @endif

    </div>
</x-app-layout>
