<x-app-layout>
    <x-slot name="title">Settlement Requests — Admin</x-slot>

    <div class="space-y-6">

        <div class="page-header">
            <div>
                <h1 class="page-title">Settlement Management</h1>
                <p class="page-subtitle">Review, approve, and process teacher payout requests.</p>
            </div>
            @if($pendingTotal > 0)
            <div class="text-sm font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-4 py-2">
                ${{ number_format($pendingTotal, 2) }} pending approval
            </div>
            @endif
        </div>

        {{-- Status filter tabs --}}
        <div class="flex gap-1 p-1 bg-slate-100 rounded-xl w-fit flex-wrap">
            @foreach([
                ['all',        'All',        $counts['all']],
                ['pending',    'Pending',    $counts['pending']],
                ['approved',   'Approved',   $counts['approved']],
                ['processing', 'Processing', $counts['processing']],
                ['paid',       'Paid',       $counts['paid']],
                ['rejected',   'Rejected',   $counts['rejected']],
            ] as [$val, $label, $count])
            <a href="{{ route('admin.settlements.index', ['status' => $val]) }}"
               class="px-3 py-1.5 text-sm font-semibold rounded-lg transition-all {{ request('status', 'all') === $val ? 'bg-white shadow-sm text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
                {{ $label }} <span class="ml-1 opacity-60">{{ $count }}</span>
            </a>
            @endforeach
        </div>

        <div class="card overflow-hidden">
            @if($settlements->count())
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Teacher</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Payout Account</th>
                            <th>Requested</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($settlements as $s)
                        <tr x-data="{ showPaid: false, showReject: false }">
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg overflow-hidden flex-shrink-0">
                                        @if($s->teacher?->avatar)
                                            <img src="{{ $s->teacher->avatar_url }}" class="w-full h-full object-cover" alt="">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white text-xs font-black uppercase">{{ substr($s->teacher?->name ?? '?', 0, 1) }}</div>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium">{{ $s->teacher?->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $s->teacher?->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="font-semibold">${{ number_format($s->amount_usd, 2) }}</td>
                            <td class="capitalize text-sm">{{ str_replace('_', ' ', $s->payment_method) }}</td>
                            <td class="text-xs text-slate-500 max-w-[160px]">
                                @if(is_array($s->payout_details))
                                    {{ $s->payout_details['account_name'] ?? '' }}
                                    @if(isset($s->payout_details['account_number']))
                                        <br><span class="font-mono">{{ $s->payout_details['account_number'] }}</span>
                                    @elseif(isset($s->payout_details['email']))
                                        <br>{{ $s->payout_details['email'] }}
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-xs text-slate-400">{{ $s->created_at->format('d M Y') }}</td>
                            <td>
                                <span class="{{ $s->statusBadgeClass() }}">{{ $s->statusLabel() }}</span>
                                @if($s->reference_number)
                                    <p class="text-xs font-mono text-slate-400 mt-0.5">{{ $s->reference_number }}</p>
                                @endif
                            </td>
                            <td class="space-y-1 min-w-[180px]">
                                <a href="{{ route('admin.settlements.show', $s) }}" class="btn btn-secondary btn-xs w-full mb-1 text-center inline-block">View Details</a>
                                @if($s->status === 'pending')
                                <form method="POST" action="{{ route('admin.settlements.approve', $s) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-xs w-full">Approve</button>
                                </form>
                                <button @click="showReject=true" class="btn btn-danger btn-xs w-full">Reject</button>
                                @elseif($s->status === 'approved')
                                <form method="POST" action="{{ route('admin.settlements.processing', $s) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary btn-xs w-full">Mark Processing</button>
                                </form>
                                <button @click="showPaid=true" class="btn btn-primary btn-xs w-full">Mark Paid</button>
                                @elseif($s->status === 'processing')
                                <button @click="showPaid=true" class="btn btn-primary btn-xs w-full">Mark Paid</button>
                                @endif

                                {{-- Mark Paid inline --}}
                                <div x-show="showPaid" x-cloak class="mt-2 space-y-1">
                                    <form method="POST" action="{{ route('admin.settlements.paid', $s) }}">
                                        @csrf
                                        <input type="text" name="reference_number" placeholder="Reference #" class="form-input text-xs py-1.5 mb-1" required>
                                        <button type="submit" class="btn btn-primary btn-xs w-full">Confirm Payment</button>
                                    </form>
                                </div>

                                {{-- Reject inline --}}
                                <div x-show="showReject" x-cloak class="mt-2 space-y-1">
                                    <form method="POST" action="{{ route('admin.settlements.reject', $s) }}">
                                        @csrf
                                        <textarea name="admin_notes" rows="2" class="form-textarea text-xs py-1.5 mb-1" placeholder="Reason for rejection..." required></textarea>
                                        <button type="submit" class="btn btn-danger btn-xs w-full">Confirm Rejection</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-3">{{ $settlements->withQueryString()->links() }}</div>
            @else
            <div class="empty-state">
                <span class="empty-state-icon">🏦</span>
                <p class="empty-state-title">No settlement requests</p>
                <p class="empty-state-text">No requests match the selected filter.</p>
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
