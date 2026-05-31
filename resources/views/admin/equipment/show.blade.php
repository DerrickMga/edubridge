<x-app-layout>
    <x-slot name="title">Loan Application #{{ $loan->reference_number }}</x-slot>

    <div class="max-w-3xl mx-auto space-y-6" x-data="{ panel: '' }">

        {{-- Back + header --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.equipment.index') }}" class="text-slate-400 hover:text-slate-600 transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            </a>
            <div class="flex-1">
                <h1 class="page-title">Loan Application</h1>
                <p class="page-subtitle">{{ $loan->reference_number }}</p>
            </div>
            <span class="{{ $loan->statusBadgeClass() }}">{{ $loan->statusLabel() }}</span>
        </div>

        {{-- Flash --}}
        @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- Teacher card --}}
        <div class="card p-5 flex items-start gap-4">
            @if($loan->teacher->avatar_url)
            <img src="{{ $loan->teacher->avatar_url }}" class="w-14 h-14 rounded-2xl object-cover flex-shrink-0">
            @else
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white font-bold text-xl flex-shrink-0">{{ substr($loan->teacher->name, 0, 1) }}</div>
            @endif
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-slate-800">{{ $loan->teacher->name }}</p>
                <p class="text-sm text-slate-500">{{ $loan->teacher->email }}</p>
                <div class="flex flex-wrap gap-2 mt-2">
                    @if($loan->teacher->equipmentProfile)
                    <span class="{{ $loan->teacher->equipmentProfile->statusBadgeClass() }}">{{ $loan->teacher->equipmentProfile->statusLabel() }}</span>
                    @else
                    <span class="badge-slate">No Equipment Profile</span>
                    @endif
                    <span class="badge-slate">{{ $loan->teacher->city ?? 'No city' }}</span>
                </div>
            </div>
        </div>

        {{-- Equipment Profile --}}
        @if($loan->teacher->equipmentProfile)
        <div class="card p-5">
            <h2 class="section-label mb-3">Teacher's Equipment Profile</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach($loan->teacher->equipmentProfile->requirementsStatus() as $req)
                <div class="text-center rounded-xl p-3 {{ $req['met'] ? 'bg-emerald-50 border border-emerald-200' : 'bg-red-50 border border-red-100' }}">
                    <div class="flex justify-center mb-1">
                        @if($req['met'])
                        <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        @else
                        <svg class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                        @endif
                    </div>
                    <p class="text-xs font-medium text-slate-700 leading-tight">{{ $req['label'] }}</p>
                    @if($req['detail'])
                    <p class="text-xs text-slate-400 mt-0.5">{{ $req['detail'] }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Loan details --}}
        <div class="card p-5 space-y-4">
            <h2 class="section-label">Loan Details</h2>

            <div>
                <p class="text-xs text-slate-400 mb-2">Items Requested</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($loan->requestedItemLabels() as $label)
                    <span class="badge-blue">{{ $label }}</span>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-xs text-slate-400">Amount Requested</p>
                    <p class="font-bold text-slate-800 text-lg">${{ number_format($loan->amount_requested_usd, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Repayment Period</p>
                    <p class="font-semibold text-slate-700">{{ $loan->repayment_period_months }} months</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Submitted</p>
                    <p class="font-semibold text-slate-700">{{ $loan->created_at->format('d M Y') }}</p>
                </div>
            </div>

            <div>
                <p class="text-xs text-slate-400 mb-1">Purpose / Reason</p>
                <p class="text-sm text-slate-700 bg-slate-50 rounded-xl px-3 py-2 leading-relaxed">{{ $loan->purpose }}</p>
            </div>

            @if($loan->employment_context)
            <div>
                <p class="text-xs text-slate-400 mb-1">Teaching Context</p>
                <p class="text-sm text-slate-700 bg-slate-50 rounded-xl px-3 py-2 leading-relaxed">{{ $loan->employment_context }}</p>
            </div>
            @endif

            @if($loan->teacher_notes)
            <div>
                <p class="text-xs text-slate-400 mb-1">Teacher Notes</p>
                <p class="text-sm text-slate-700 bg-slate-50 rounded-xl px-3 py-2 leading-relaxed">{{ $loan->teacher_notes }}</p>
            </div>
            @endif

            {{-- Supporting Documents ─────────────────────────────────── --}}
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                <div class="bg-slate-50 px-4 py-3 border-b border-slate-100">
                    <p class="font-semibold text-sm text-slate-700">Supporting Documents</p>
                </div>
                <div class="p-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    {{-- Income Proof --}}
                    <div class="border rounded-xl p-3 {{ $loan->income_proof_path ? 'border-emerald-200 bg-emerald-50' : 'border-red-100 bg-red-50' }}">
                        <p class="text-xs font-semibold {{ $loan->income_proof_path ? 'text-emerald-700' : 'text-red-600' }} mb-1">Proof of Income</p>
                        @if($loan->income_proof_path)
                        <a href="{{ route('admin.equipment.download-doc', [$loan, 'income']) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 text-xs text-emerald-700 underline hover:no-underline">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Download
                        </a>
                        <p class="text-xs text-emerald-600 mt-0.5">{{ basename($loan->income_proof_path) }}</p>
                        @else
                        <p class="text-xs text-red-500">Not uploaded</p>
                        @endif
                    </div>
                    {{-- Address Proof --}}
                    <div class="border rounded-xl p-3 {{ $loan->address_proof_path ? 'border-emerald-200 bg-emerald-50' : 'border-red-100 bg-red-50' }}">
                        <p class="text-xs font-semibold {{ $loan->address_proof_path ? 'text-emerald-700' : 'text-red-600' }} mb-1">Proof of Address</p>
                        @if($loan->address_proof_path)
                        <a href="{{ route('admin.equipment.download-doc', [$loan, 'address']) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 text-xs text-emerald-700 underline hover:no-underline">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Download
                        </a>
                        <p class="text-xs text-emerald-600 mt-0.5">{{ basename($loan->address_proof_path) }}</p>
                        @else
                        <p class="text-xs text-red-500">Not uploaded</p>
                        @endif
                    </div>
                    {{-- Quotation (optional) --}}
                    <div class="border rounded-xl p-3 {{ $loan->quotation_path ? 'border-violet-200 bg-violet-50' : 'border-slate-100 bg-slate-50' }}">
                        <p class="text-xs font-semibold {{ $loan->quotation_path ? 'text-violet-700' : 'text-slate-400' }} mb-1">Alternative Quotation</p>
                        @if($loan->quotation_path)
                        <a href="{{ route('admin.equipment.download-doc', [$loan, 'quotation']) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 text-xs text-violet-700 underline hover:no-underline">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Download
                        </a>
                        <p class="text-xs text-violet-600 mt-0.5">{{ basename($loan->quotation_path) }}</p>
                        @if($loan->quotation_notes)
                        <p class="text-xs text-violet-700 mt-1 leading-relaxed italic">{{ $loan->quotation_notes }}</p>
                        @endif
                        @else
                        <p class="text-xs text-slate-400">None provided</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Approved details (if approved or later) --}}
        @if($loan->approved_amount_usd)
        <div class="card p-5 bg-emerald-50 border-emerald-200 space-y-3">
            <h2 class="section-label text-emerald-800">Approval Details</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                <div>
                    <p class="text-xs text-emerald-600">Approved Amount</p>
                    <p class="font-bold text-emerald-800">${{ number_format($loan->approved_amount_usd, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-emerald-600">Monthly Repayment</p>
                    <p class="font-bold text-emerald-800">${{ number_format($loan->monthly_repayment_usd, 2) }}/mo</p>
                </div>
                <div>
                    <p class="text-xs text-emerald-600">Period</p>
                    <p class="font-semibold text-emerald-700">{{ $loan->approved_months }} months</p>
                </div>
                <div>
                    <p class="text-xs text-emerald-600">Interest Rate</p>
                    <p class="font-semibold text-emerald-700">{{ $loan->interest_rate_percent }}%</p>
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-sm">
                <div>
                    <p class="text-xs text-emerald-600">Repayment Starts</p>
                    <p class="font-semibold text-emerald-700">{{ $loan->repayment_starts_on?->format('d M Y') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-emerald-600">Repayment Ends</p>
                    <p class="font-semibold text-emerald-700">{{ $loan->repayment_ends_on?->format('d M Y') ?? '—' }}</p>
                </div>
                @if($loan->disbursed_at)
                <div>
                    <p class="text-xs text-emerald-600">Disbursed At</p>
                    <p class="font-semibold text-emerald-700">{{ $loan->disbursed_at->format('d M Y') }}</p>
                </div>
                @endif
            </div>
            @if($loan->reviewer)
            <p class="text-xs text-emerald-600">Reviewed by {{ $loan->reviewer->name }} on {{ $loan->reviewed_at?->format('d M Y') }}</p>
            @endif
        </div>
        @endif

        {{-- Admin notes --}}
        @if($loan->admin_notes)
        <div class="card p-4">
            <p class="text-xs text-slate-400 mb-1">Admin Notes</p>
            <p class="text-sm text-slate-700">{{ $loan->admin_notes }}</p>
        </div>
        @endif

        {{-- Action panel --}}
        @if($loan->status === 'pending')
        <div class="card p-5 space-y-4">
            <h2 class="section-label">Actions</h2>
            <div class="flex flex-wrap gap-3" x-show="panel === ''">
                <form method="POST" action="{{ route('admin.equipment.under-review', $loan) }}">@csrf
                    <button class="btn btn-secondary btn-sm">Mark Under Review</button>
                </form>
                <button @click="panel='approve'" class="btn btn-primary btn-sm">Approve Loan</button>
                <button @click="panel='reject'" class="btn btn-danger btn-sm">Reject</button>
            </div>

            {{-- Approve form --}}
            <div x-show="panel === 'approve'" x-cloak class="space-y-4 border-t border-slate-100 pt-4">
                <h3 class="font-semibold text-sm text-slate-700">Approve Equipment Loan</h3>
                <form method="POST" action="{{ route('admin.equipment.approve', $loan) }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="form-group">
                            <label class="form-label">Approved Amount (USD)</label>
                            <input type="number" name="approved_amount_usd" class="form-input" step="1" min="1" max="2000" value="{{ $loan->amount_requested_usd }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Repayment Period</label>
                            <select name="approved_months" class="form-select" required>
                                @foreach([3,6,9,12,18,24] as $m)
                                <option value="{{ $m }}" {{ $loan->repayment_period_months == $m ? 'selected' : '' }}>{{ $m }} months</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Interest Rate (%)</label>
                            <input type="number" name="interest_rate_percent" class="form-input" step="0.1" min="0" max="50" value="0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Repayment Starts On</label>
                            <input type="date" name="repayment_starts_on" class="form-input" value="{{ now()->addMonths(1)->toDateString() }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Admin Notes</label>
                        <textarea name="admin_notes" class="form-textarea" rows="2" placeholder="Optional notes for the teacher…"></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="btn btn-primary btn-sm">Confirm Approval</button>
                        <button type="button" @click="panel=''" class="btn btn-secondary btn-sm">Cancel</button>
                    </div>
                </form>
            </div>

            {{-- Reject form --}}
            <div x-show="panel === 'reject'" x-cloak class="space-y-4 border-t border-slate-100 pt-4">
                <h3 class="font-semibold text-sm text-slate-700">Reject Application</h3>
                <form method="POST" action="{{ route('admin.equipment.reject', $loan) }}" class="space-y-4">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Reason for Rejection <span class="text-red-500">*</span></label>
                        <textarea name="admin_notes" class="form-textarea" rows="3" required minlength="10" placeholder="Explain why this application is being rejected…"></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="btn btn-danger btn-sm">Confirm Rejection</button>
                        <button type="button" @click="panel=''" class="btn btn-secondary btn-sm">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        @elseif($loan->status === 'under_review')
        <div class="card p-5 space-y-4">
            <h2 class="section-label">Actions</h2>
            <div class="flex flex-wrap gap-3" x-show="panel === ''">
                <button @click="panel='approve'" class="btn btn-primary btn-sm">Approve Loan</button>
                <button @click="panel='reject'" class="btn btn-danger btn-sm">Reject</button>
            </div>

            <div x-show="panel === 'approve'" x-cloak class="space-y-4 border-t border-slate-100 pt-4">
                <h3 class="font-semibold text-sm text-slate-700">Approve Equipment Loan</h3>
                <form method="POST" action="{{ route('admin.equipment.approve', $loan) }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="form-group">
                            <label class="form-label">Approved Amount (USD)</label>
                            <input type="number" name="approved_amount_usd" class="form-input" step="1" min="1" max="2000" value="{{ $loan->amount_requested_usd }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Repayment Period</label>
                            <select name="approved_months" class="form-select" required>
                                @foreach([3,6,9,12,18,24] as $m)
                                <option value="{{ $m }}" {{ $loan->repayment_period_months == $m ? 'selected' : '' }}>{{ $m }} months</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Interest Rate (%)</label>
                            <input type="number" name="interest_rate_percent" class="form-input" step="0.1" min="0" max="50" value="0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Repayment Starts On</label>
                            <input type="date" name="repayment_starts_on" class="form-input" value="{{ now()->addMonths(1)->toDateString() }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Admin Notes</label>
                        <textarea name="admin_notes" class="form-textarea" rows="2" placeholder="Optional notes…"></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="btn btn-primary btn-sm">Confirm Approval</button>
                        <button type="button" @click="panel=''" class="btn btn-secondary btn-sm">Cancel</button>
                    </div>
                </form>
            </div>

            <div x-show="panel === 'reject'" x-cloak class="space-y-4 border-t border-slate-100 pt-4">
                <h3 class="font-semibold text-sm text-slate-700">Reject Application</h3>
                <form method="POST" action="{{ route('admin.equipment.reject', $loan) }}" class="space-y-4">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Reason for Rejection <span class="text-red-500">*</span></label>
                        <textarea name="admin_notes" class="form-textarea" rows="3" required minlength="10"></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="btn btn-danger btn-sm">Confirm Rejection</button>
                        <button type="button" @click="panel=''" class="btn btn-secondary btn-sm">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        @elseif($loan->status === 'approved')
        <div class="card p-5">
            <h2 class="section-label mb-4">Actions</h2>
            <div x-show="panel === ''" class="flex gap-3">
                <button @click="panel='disburse'" class="btn btn-primary btn-sm">Mark as Disbursed</button>
            </div>
            <div x-show="panel === 'disburse'" x-cloak class="space-y-4">
                <h3 class="font-semibold text-sm text-slate-700">Confirm Disbursement</h3>
                <form method="POST" action="{{ route('admin.equipment.disburse', $loan) }}" class="space-y-3">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="admin_notes" class="form-textarea" rows="2" placeholder="e.g. Equipment handed over on …"></textarea>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit" class="btn btn-primary btn-sm">Confirm Disbursement</button>
                        <button type="button" @click="panel=''" class="btn btn-secondary btn-sm">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        @elseif($loan->status === 'disbursed')
        <div class="card p-5 flex flex-wrap gap-3">
            <form method="POST" action="{{ route('admin.equipment.mark-repaying', $loan) }}">@csrf
                <button class="btn btn-primary btn-sm">Mark as Repaying</button>
            </form>
            <form method="POST" action="{{ route('admin.equipment.mark-completed', $loan) }}">@csrf
                <button class="btn btn-secondary btn-sm">Mark as Completed</button>
            </form>
        </div>

        @elseif($loan->status === 'repaying')
        <div class="card p-5">
            <form method="POST" action="{{ route('admin.equipment.mark-completed', $loan) }}">@csrf
                <button class="btn btn-primary btn-sm">Mark as Fully Repaid (Completed)</button>
            </form>
        </div>

        @elseif(in_array($loan->status, ['completed', 'rejected']))
        <div class="card p-4">
            <p class="text-sm text-slate-500">This application is <strong>{{ $loan->statusLabel() }}</strong>. No further actions are available.</p>
        </div>
        @endif

    </div>
</x-app-layout>
