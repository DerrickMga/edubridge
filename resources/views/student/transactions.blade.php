<x-app-layout>
    <x-slot name="title">Payment History</x-slot>

    <div class="space-y-6">

        <div class="page-header">
            <div>
                <h1 class="page-title">Payment History</h1>
                <p class="page-subtitle">Your course purchases and payment status.</p>
            </div>
        </div>

        {{-- Summary --}}
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="stat-card">
                <p class="stat-label">Total Spent</p>
                <p class="stat-value text-2xl">${{ number_format($totalSpent, 2) }}</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Pending Payments</p>
                <p class="stat-value text-2xl text-amber-600">{{ $pendingCount }}</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Active Courses</p>
                <p class="stat-value text-2xl text-emerald-600">{{ $payments->where('status', 'completed')->count() }}</p>
            </div>
        </div>

        {{-- Payments table --}}
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="section-title">All Transactions</h2>
            </div>
            @if($payments->count())
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Provider</th>
                            <th>Access Period</th>
                            <th>Status</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $p)
                        <tr>
                            <td class="font-medium text-slate-800">{{ $p->course?->title ?? 'Unknown Course' }}</td>
                            <td class="text-xs text-slate-500">{{ $p->created_at->format('d M Y') }}</td>
                            <td class="font-semibold tabular-nums">${{ number_format($p->amount_usd, 2) }}</td>
                            <td class="text-sm capitalize">{{ $p->payment_provider ?? '—' }}</td>
                            <td class="text-xs text-slate-500">
                                @if($p->access_starts_at && $p->access_expires_at)
                                    {{ $p->access_starts_at->format('d M Y') }} – {{ $p->access_expires_at->format('d M Y') }}
                                @elseif($p->access_expires_at)
                                    Expires {{ $p->access_expires_at->format('d M Y') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @php
                                    $cls = match($p->status) {
                                        'completed' => 'badge-green',
                                        'pending'   => 'badge-amber',
                                        'failed', 'refunded' => 'badge-red',
                                        default     => 'badge-slate',
                                    };
                                @endphp
                                <span class="{{ $cls }}">{{ ucfirst($p->status) }}</span>
                            </td>
                            <td class="text-xs font-mono text-slate-400">{{ $p->provider_reference ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-3">{{ $payments->links() }}</div>
            @else
            <div class="empty-state">
                <span class="empty-state-icon">🛒</span>
                <p class="empty-state-title">No payments yet</p>
                <p class="empty-state-text">Your course purchases will appear here once you enroll.</p>
                <a href="{{ route('courses.index') }}" class="btn btn-primary btn-sm">Browse Courses</a>
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
