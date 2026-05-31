<x-app-layout>
    <x-slot name="title">Settlement #{{ $settlement->id }} — {{ $settlement->teacher?->name }}</x-slot>

    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="page-header">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.settlements.index') }}" class="p-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-500 hover:text-slate-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                </a>
                <div>
                    <h1 class="page-title">Settlement Request</h1>
                    <p class="page-subtitle">ID #{{ $settlement->id }} · Submitted {{ $settlement->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="{{ $settlement->statusBadgeClass() }} text-sm px-3 py-1">{{ $settlement->statusLabel() }}</span>
                @if($settlement->reference_number)
                    <span class="text-xs font-mono bg-slate-100 text-slate-600 px-2 py-1 rounded-lg">{{ $settlement->reference_number }}</span>
                @endif
            </div>
        </div>

        {{-- Status Progress --}}
        <div class="card px-6 py-5">
            <div class="flex items-center gap-0">
                @php
                    $steps = [
                        ['pending',    'Submitted',    'bg-slate-400'],
                        ['approved',   'Approved',     'bg-blue-500'],
                        ['processing', 'Processing',   'bg-purple-500'],
                        ['paid',       'Paid',         'bg-emerald-500'],
                    ];
                    $order  = ['pending' => 0, 'approved' => 1, 'processing' => 2, 'paid' => 3, 'rejected' => -1];
                    $current = $order[$settlement->status] ?? -1;
                @endphp
                @if($settlement->status === 'rejected')
                    <div class="flex items-center gap-3 w-full">
                        <div class="w-8 h-8 rounded-full bg-red-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-red-700">Rejected</p>
                            @if($settlement->admin_notes)
                                <p class="text-sm text-red-500 mt-0.5">{{ $settlement->admin_notes }}</p>
                            @endif
                        </div>
                    </div>
                @else
                    @foreach($steps as $i => [$val, $label, $color])
                    <div class="flex items-center {{ $i < count($steps) - 1 ? 'flex-1' : '' }}">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $current >= $i ? $color : 'bg-slate-200' }}">
                                @if($current > $i)
                                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                @elseif($current === $i)
                                    <div class="w-2.5 h-2.5 bg-white rounded-full"></div>
                                @else
                                    <div class="w-2.5 h-2.5 bg-slate-400 rounded-full opacity-40"></div>
                                @endif
                            </div>
                            <p class="text-xs mt-1 font-medium {{ $current >= $i ? 'text-slate-700' : 'text-slate-400' }}">{{ $label }}</p>
                        </div>
                        @if($i < count($steps) - 1)
                        <div class="flex-1 h-0.5 mb-5 mx-1 {{ $current > $i ? $color : 'bg-slate-200' }}"></div>
                        @endif
                    </div>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Teacher Card --}}
            <div class="card p-6">
                <h2 class="section-title mb-4">Teacher</h2>
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl overflow-hidden flex-shrink-0">
                        @if($settlement->teacher?->avatar)
                            <img src="{{ $settlement->teacher->avatar_url }}" class="w-full h-full object-cover" alt="">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white text-xl font-black uppercase">{{ substr($settlement->teacher?->name ?? '?', 0, 1) }}</div>
                        @endif
                    </div>
                    <div class="space-y-1.5 text-sm flex-1">
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-slate-800">{{ $settlement->teacher?->name }}</p>
                            @if($settlement->teacher?->is_verified)
                                <span class="badge-green text-xs">✅ Verified</span>
                            @else
                                <span class="badge-amber text-xs">⚠ Unverified</span>
                            @endif
                        </div>
                        <p class="text-slate-500">{{ $settlement->teacher?->email }}</p>
                        @if($settlement->teacher?->phone)
                            <p class="text-slate-400">{{ $settlement->teacher->phone }}</p>
                        @endif
                        <a href="{{ route('admin.verifications.show', $settlement->teacher?->verification) }}" class="text-xs text-emerald-600 hover:underline mt-1 inline-block">View KYC →</a>
                    </div>
                </div>
            </div>

            {{-- Settlement Details --}}
            <div class="card p-6">
                <h2 class="section-title mb-4">Settlement Details</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Amount Requested</span>
                        <span class="font-bold text-lg text-slate-800">${{ number_format($settlement->amount_usd, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Payment Method</span>
                        <span class="font-medium capitalize">{{ str_replace('_', ' ', $settlement->payment_method) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Submitted</span>
                        <span class="font-medium">{{ $settlement->created_at->format('d M Y H:i') }}</span>
                    </div>
                    @if($settlement->processed_at)
                    <div class="flex justify-between">
                        <span class="text-slate-500">Processed</span>
                        <span class="font-medium">{{ $settlement->processed_at->format('d M Y H:i') }}</span>
                    </div>
                    @endif
                    @if($settlement->processor)
                    <div class="flex justify-between">
                        <span class="text-slate-500">Processed By</span>
                        <span class="font-medium">{{ $settlement->processor->name }}</span>
                    </div>
                    @endif
                    @if($settlement->teacher_notes)
                    <div class="pt-2 border-t border-slate-100">
                        <p class="text-slate-500 mb-1">Teacher Notes</p>
                        <p class="text-slate-700 bg-slate-50 rounded-lg px-3 py-2">{{ $settlement->teacher_notes }}</p>
                    </div>
                    @endif
                    @if($settlement->admin_notes)
                    <div class="pt-2 border-t border-slate-100">
                        <p class="text-slate-500 mb-1">Admin Notes</p>
                        <p class="text-slate-700 bg-slate-50 rounded-lg px-3 py-2">{{ $settlement->admin_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- Payout Details --}}
        <div class="card p-6">
            <h2 class="section-title mb-4">Payout Details</h2>
            @if(is_array($settlement->payout_details) && count($settlement->payout_details))
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                @foreach($settlement->payout_details as $key => $val)
                @if($val)
                <div>
                    <span class="text-slate-400 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                    <p class="font-medium font-mono mt-0.5 text-slate-800">{{ $val }}</p>
                </div>
                @endif
                @endforeach
            </div>
            @else
            <p class="text-sm text-slate-400">No payout details recorded.</p>
            @endif
        </div>

        {{-- Admin Action Section --}}
        @if(! in_array($settlement->status, ['paid', 'rejected']))
        <div class="card p-6" x-data="{ panel: null }">
            <h2 class="section-title mb-4">Admin Actions</h2>

            <div class="flex gap-3 flex-wrap">
                @if($settlement->status === 'pending')
                    <button @click="panel = panel === 'approve' ? null : 'approve'" :class="panel==='approve' ? 'btn-primary' : 'btn-secondary'" class="btn btn-sm">✅ Approve</button>
                    <button @click="panel = panel === 'reject' ? null : 'reject'" :class="panel==='reject' ? 'btn-danger' : 'btn-secondary'" class="btn btn-sm">❌ Reject</button>
                @endif
                @if($settlement->status === 'approved')
                    <form method="POST" action="{{ route('admin.settlements.processing', $settlement) }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary btn-sm">⚙ Mark Processing</button>
                    </form>
                    <button @click="panel = panel === 'paid' ? null : 'paid'" :class="panel==='paid' ? 'btn-primary' : 'btn-secondary'" class="btn btn-sm">💸 Mark Paid</button>
                    <button @click="panel = panel === 'reject' ? null : 'reject'" :class="panel==='reject' ? 'btn-danger' : 'btn-secondary'" class="btn btn-sm">❌ Reject</button>
                @endif
                @if($settlement->status === 'processing')
                    <button @click="panel = panel === 'paid' ? null : 'paid'" :class="panel==='paid' ? 'btn-primary' : 'btn-secondary'" class="btn btn-sm">💸 Mark Paid</button>
                @endif
            </div>

            {{-- Approve form --}}
            <div x-show="panel === 'approve'" x-cloak class="mt-5 space-y-3 border-t border-slate-100 pt-5">
                <form method="POST" action="{{ route('admin.settlements.approve', $settlement) }}" class="space-y-3">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Admin Notes (optional)</label>
                        <textarea name="admin_notes" rows="2" class="form-textarea" placeholder="Any notes for the teacher...">{{ old('admin_notes') }}</textarea>
                        @error('admin_notes') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Confirm Approval</button>
                </form>
            </div>

            {{-- Mark Paid form --}}
            <div x-show="panel === 'paid'" x-cloak class="mt-5 space-y-3 border-t border-slate-100 pt-5">
                <form method="POST" action="{{ route('admin.settlements.paid', $settlement) }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Reference / Transaction Number <span class="text-red-500">*</span></label>
                            <input type="text" name="reference_number" value="{{ old('reference_number') }}" class="form-input font-mono" required placeholder="e.g. TXN20260531ABC">
                            @error('reference_number') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Admin Notes (optional)</label>
                            <input type="text" name="admin_notes" value="{{ old('admin_notes') }}" class="form-input" placeholder="Any notes...">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Confirm Payment</button>
                </form>
            </div>

            {{-- Reject form --}}
            <div x-show="panel === 'reject'" x-cloak class="mt-5 space-y-3 border-t border-slate-100 pt-5">
                <form method="POST" action="{{ route('admin.settlements.reject', $settlement) }}" class="space-y-3">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Reason for Rejection <span class="text-red-500">*</span></label>
                        <textarea name="admin_notes" rows="3" class="form-textarea" required placeholder="Please explain why this request is being rejected...">{{ old('admin_notes') }}</textarea>
                        @error('admin_notes') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="btn btn-danger btn-sm">Confirm Rejection</button>
                </form>
            </div>
        </div>
        @else
        <div class="card p-5 flex items-center gap-3 {{ $settlement->status === 'paid' ? 'bg-emerald-50 border-emerald-200' : 'bg-red-50 border-red-200' }}">
            <span class="text-2xl">{{ $settlement->status === 'paid' ? '✅' : '❌' }}</span>
            <div>
                <p class="font-semibold {{ $settlement->status === 'paid' ? 'text-emerald-800' : 'text-red-800' }}">
                    {{ $settlement->status === 'paid' ? 'Payment Confirmed — '.$settlement->reference_number : 'Request Rejected' }}
                </p>
                <p class="text-sm mt-0.5 {{ $settlement->status === 'paid' ? 'text-emerald-600' : 'text-red-600' }}">
                    {{ $settlement->processed_at?->format('d M Y H:i') }} · By {{ $settlement->processor?->name ?? 'Admin' }}
                </p>
                @if($settlement->admin_notes)
                    <p class="text-sm mt-1 {{ $settlement->status === 'paid' ? 'text-emerald-700' : 'text-red-700' }}">{{ $settlement->admin_notes }}</p>
                @endif
            </div>
        </div>
        @endif

        {{-- Teacher's settlement history --}}
        @if($teacherHistory->count())
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="section-title">Other Settlements by {{ $settlement->teacher?->name }}</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teacherHistory as $h)
                        <tr>
                            <td class="text-xs text-slate-500">{{ $h->created_at->format('d M Y') }}</td>
                            <td class="font-semibold">${{ number_format($h->amount_usd, 2) }}</td>
                            <td class="capitalize text-sm">{{ str_replace('_', ' ', $h->payment_method) }}</td>
                            <td><span class="{{ $h->statusBadgeClass() }}">{{ $h->statusLabel() }}</span></td>
                            <td class="text-xs font-mono text-slate-400">
                                @if($h->reference_number)
                                    <a href="{{ route('admin.settlements.show', $h) }}" class="hover:underline text-emerald-600">{{ $h->reference_number }}</a>
                                @else
                                    <a href="{{ route('admin.settlements.show', $h) }}" class="hover:underline text-slate-400">#{{ $h->id }}</a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>
</x-app-layout>
