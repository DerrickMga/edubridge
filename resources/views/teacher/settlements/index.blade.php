<x-app-layout>
    <x-slot name="title">Settlement Requests</x-slot>

    <div class="space-y-6">

        <div class="page-header">
            <div>
                <h1 class="page-title">Settlement &amp; Liquidation</h1>
                <p class="page-subtitle">Request payout of your approved earnings to your registered account.</p>
            </div>
        </div>

        {{-- Verification gate --}}
        @if(! auth()->user()->is_verified)
        <div class="alert alert-warn">
            <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
            <div>
                <p class="font-semibold">Verification required before settlement</p>
                <p class="text-sm mt-0.5">Your account must be verified before requesting payouts. <a href="{{ route('teacher.verification.index') }}" class="underline font-medium">Complete verification →</a></p>
            </div>
        </div>
        @endif

        {{-- Balance overview --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="stat-card">
                <p class="stat-label">Total Earned</p>
                <p class="stat-value text-2xl">${{ number_format($totalEarned, 2) }}</p>
                <p class="text-xs text-slate-400 mt-1">Approved sessions</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Available Balance</p>
                <p class="stat-value text-2xl text-emerald-600">${{ number_format($availableBalance, 2) }}</p>
                <p class="text-xs text-slate-400 mt-1">Ready to withdraw</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">In Flight</p>
                <p class="stat-value text-2xl text-amber-600">${{ number_format($pendingSettled, 2) }}</p>
                <p class="text-xs text-slate-400 mt-1">Pending / processing</p>
            </div>
            <div class="stat-card">
                <p class="stat-label">Total Settled</p>
                <p class="stat-value text-2xl">${{ number_format($totalSettled, 2) }}</p>
                <p class="text-xs text-slate-400 mt-1">Paid out to date</p>
            </div>
        </div>

        {{-- New request form --}}
        @if(auth()->user()->is_verified && $availableBalance > 0)
        <div class="card p-6" x-data="{ method: 'bank_transfer' }">
            <h2 class="section-title mb-5">Request New Settlement</h2>

            <form method="POST" action="{{ route('teacher.settlements.store') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="amount_usd">Amount (USD)</label>
                        <div class="relative">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-semibold">$</span>
                            <input type="number" step="0.01" min="5" max="{{ $availableBalance }}" id="amount_usd" name="amount_usd"
                                   value="{{ old('amount_usd') }}" class="form-input pl-8"
                                   placeholder="{{ number_format($availableBalance, 2) }}">
                        </div>
                        <p class="form-hint">Available: ${{ number_format($availableBalance, 2) }} · Minimum: $5.00</p>
                        @error('amount_usd') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">Payment Method</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach([
                                ['bank_transfer', '🏦', 'Bank Transfer'],
                                ['ecocash',       '📱', 'EcoCash'],
                                ['innbucks',      '💚', 'InnBucks'],
                                ['paynow',        '💳', 'Paynow'],
                            ] as [$val, $icon, $label])
                            <label class="cursor-pointer">
                                <input type="radio" name="payment_method" value="{{ $val }}" x-model="method" class="sr-only">
                                <div class="border-2 rounded-xl px-3 py-2.5 text-center transition-all text-sm font-medium"
                                     :class="method === '{{ $val }}' ? 'border-emerald-600 bg-emerald-50 text-emerald-700' : 'border-slate-200 hover:border-slate-300 text-slate-600'">
                                    {{ $icon }} {{ $label }}
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @error('payment_method') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Payout details (conditional) --}}
                @php
                    $v = auth()->user()->verification;
                @endphp
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="payout_account_name">Account Name</label>
                        <input type="text" id="payout_account_name" name="payout_details[account_name]"
                               value="{{ old('payout_details.account_name', $v?->full_legal_name) }}" class="form-input" required>
                        @error('payout_details.account_name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div x-show="method === 'bank_transfer'" class="form-group">
                        <label class="form-label">Bank Name</label>
                        <input type="text" name="payout_details[bank_name]"
                               value="{{ old('payout_details.bank_name', $v?->bank_name) }}" class="form-input" placeholder="CBZ Bank">
                    </div>

                    <div x-show="method !== 'paynow'" class="form-group">
                        <label class="form-label" x-text="method === 'bank_transfer' ? 'Account Number' : 'Mobile Number'"></label>
                        <input type="text" name="payout_details[account_number]"
                               value="{{ old('payout_details.account_number') }}" class="form-input"
                               :placeholder="method === 'bank_transfer' ? '0012345678' : '0771234567'">
                    </div>

                    <div x-show="method === 'paynow'" class="form-group">
                        <label class="form-label">Paynow Email</label>
                        <input type="email" name="payout_details[email]"
                               value="{{ old('payout_details.email', $v?->paynow_email) }}" class="form-input">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="teacher_notes">Notes (optional)</label>
                    <textarea id="teacher_notes" name="teacher_notes" rows="2" class="form-textarea" placeholder="Any notes for the finance team...">{{ old('teacher_notes') }}</textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn btn-primary">Submit Settlement Request</button>
                </div>
            </form>
        </div>
        @elseif(auth()->user()->is_verified && $availableBalance <= 0)
        <div class="card p-8 text-center">
            <p class="text-4xl mb-3">💰</p>
            <p class="font-semibold text-slate-700">No balance available</p>
            <p class="text-sm text-slate-400 mt-1">Your approved earnings are fully settled or in progress.</p>
        </div>
        @endif

        {{-- Requests history --}}
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="section-title">Settlement History</h2>
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
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($settlements as $s)
                        <tr>
                            <td class="text-slate-500 text-xs">{{ $s->created_at->format('d M Y') }}</td>
                            <td class="font-semibold">${{ number_format($s->amount_usd, 2) }}</td>
                            <td class="capitalize text-sm">{{ str_replace('_', ' ', $s->payment_method) }}</td>
                            <td><span class="{{ $s->statusBadgeClass() }}">{{ $s->statusLabel() }}</span></td>
                            <td class="text-xs font-mono text-slate-500">{{ $s->reference_number ?? '—' }}</td>
                            <td class="text-xs text-slate-400 max-w-xs truncate">{{ $s->admin_notes ?? '—' }}</td>
                            <td>
                                <a href="{{ route('teacher.settlements.pdf', $s) }}" target="_blank"
                                   class="inline-flex items-center gap-1 text-xs text-violet-600 hover:text-violet-800 underline hover:no-underline">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                    PDF
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-3">{{ $settlements->links() }}</div>
            @else
            <div class="empty-state">
                <span class="empty-state-icon">🏦</span>
                <p class="empty-state-title">No settlement requests yet</p>
                <p class="empty-state-text">Once you have approved earnings, you can request a payout here.</p>
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
