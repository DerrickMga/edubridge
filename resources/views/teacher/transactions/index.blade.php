<x-app-layout>
    <x-slot name="title">My Transactions</x-slot>

    <div class="space-y-6">

        <div class="page-header">
            <div>
                <h1 class="page-title">Transaction History</h1>
                <p class="page-subtitle">Full record of your earnings, settlements, and pending items.</p>
            </div>
            <a href="{{ route('teacher.settlements.index') }}" class="btn btn-primary btn-sm">
                Request Settlement
            </a>
        </div>

        {{-- Summary cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="stat-card col-span-1">
                <p class="stat-label">Total Earned</p>
                <p class="stat-value text-xl">${{ number_format($totals['earned'], 2) }}</p>
            </div>
            <div class="stat-card col-span-1">
                <p class="stat-label">Pending Approval</p>
                <p class="stat-value text-xl text-amber-600">${{ number_format($totals['pending'], 2) }}</p>
            </div>
            <div class="stat-card col-span-1">
                <p class="stat-label">Available</p>
                <p class="stat-value text-xl text-emerald-600">${{ number_format($totals['available'], 2) }}</p>
            </div>
            <div class="stat-card col-span-1">
                <p class="stat-label">In Flight</p>
                <p class="stat-value text-xl text-blue-600">${{ number_format($totals['in_flight'], 2) }}</p>
            </div>
            <div class="stat-card col-span-1">
                <p class="stat-label">Total Settled</p>
                <p class="stat-value text-xl">${{ number_format($totals['settled'], 2) }}</p>
            </div>
        </div>

        {{-- Tabs --}}
        <div x-data="{ tab: 'earnings' }">
            <div class="flex gap-1 p-1 bg-slate-100 rounded-xl w-fit mb-5">
                <button @click="tab='earnings'" :class="tab==='earnings' ? 'bg-white shadow-sm text-slate-800' : 'text-slate-500 hover:text-slate-700'"
                        class="px-4 py-2 text-sm font-semibold rounded-lg transition-all">Earnings</button>
                <button @click="tab='settlements'" :class="tab==='settlements' ? 'bg-white shadow-sm text-slate-800' : 'text-slate-500 hover:text-slate-700'"
                        class="px-4 py-2 text-sm font-semibold rounded-lg transition-all">Settlements</button>
            </div>

            {{-- Earnings tab --}}
            <div x-show="tab === 'earnings'">
                <div class="card overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="section-title">Session Earnings</h2>
                        <span class="badge-slate badge">{{ $paymentItems->total() }} records</span>
                    </div>
                    @if($paymentItems->count())
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Session</th>
                                    <th>Date</th>
                                    <th>Hours</th>
                                    <th>Students</th>
                                    <th>Rate</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($paymentItems as $item)
                                <tr>
                                    <td class="font-medium text-slate-800">{{ $item->session?->title ?? 'Session #'.$item->live_session_id }}</td>
                                    <td class="text-xs text-slate-500">{{ $item->created_at->format('d M Y') }}</td>
                                    <td class="tabular-nums">{{ number_format($item->hours_logged, 2) }}h</td>
                                    <td class="tabular-nums">{{ $item->student_count }}</td>
                                    <td class="tabular-nums">${{ number_format($item->hourly_rate_usd, 2) }}/hr</td>
                                    <td class="font-semibold tabular-nums">${{ number_format($item->total_usd, 2) }}</td>
                                    <td>
                                        @php
                                            $cls = match($item->status) {
                                                'approved', 'paid' => 'badge-green',
                                                'rejected'         => 'badge-red',
                                                default            => 'badge-amber',
                                            };
                                        @endphp
                                        <span class="{{ $cls }}">{{ ucfirst($item->status) }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-3">{{ $paymentItems->appends(['settlements_page' => request('settlements_page')])->links() }}</div>
                    @else
                    <div class="empty-state">
                        <span class="empty-state-icon">📋</span>
                        <p class="empty-state-title">No earnings recorded yet</p>
                        <p class="empty-state-text">Submit session logs after your live sessions to generate payment claims.</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Settlements tab --}}
            <div x-show="tab === 'settlements'" x-cloak>
                <div class="card overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h2 class="section-title">Settlement Requests</h2>
                        <a href="{{ route('teacher.settlements.index') }}" class="btn btn-primary btn-sm">New Request</a>
                    </div>
                    @if($settlements->count())
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Reference</th>
                                    <th>Processed</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($settlements as $s)
                                <tr>
                                    <td class="text-xs text-slate-500">{{ $s->created_at->format('d M Y') }}</td>
                                    <td class="font-semibold">${{ number_format($s->amount_usd, 2) }}</td>
                                    <td class="text-sm capitalize">{{ str_replace('_', ' ', $s->payment_method) }}</td>
                                    <td><span class="{{ $s->statusBadgeClass() }}">{{ $s->statusLabel() }}</span></td>
                                    <td class="text-xs font-mono text-slate-500">{{ $s->reference_number ?? '—' }}</td>
                                    <td class="text-xs text-slate-400">{{ $s->processed_at?->format('d M Y') ?? '—' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-3">{{ $settlements->appends(['earnings_page' => request('earnings_page')])->links() }}</div>
                    @else
                    <div class="empty-state">
                        <span class="empty-state-icon">🏦</span>
                        <p class="empty-state-title">No settlement requests</p>
                        <p class="empty-state-text">Request a payout once you have approved earnings.</p>
                        <a href="{{ route('teacher.settlements.index') }}" class="btn btn-primary btn-sm">Request Settlement</a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
