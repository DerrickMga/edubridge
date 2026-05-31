<x-app-layout>
    <x-slot name="title">Verification — {{ $verification->teacher?->name }}</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">

        <div class="page-header">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.verifications.index') }}" class="p-2 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-500 hover:text-slate-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                </a>
                <div>
                    <h1 class="page-title">KYC Review</h1>
                    <p class="page-subtitle">{{ $verification->teacher?->name }} · Submitted {{ $verification->submitted_at?->format('d M Y') ?? $verification->created_at->format('d M Y') }}</p>
                </div>
            </div>
            <span class="{{ $verification->statusBadgeClass() }} text-sm">{{ ucwords(str_replace('_', ' ', $verification->status)) }}</span>
        </div>

        {{-- Teacher Info --}}
        <div class="card p-6">
            <h2 class="section-title mb-4">Teacher Profile</h2>
            <div class="flex items-start gap-4">
                <div class="w-16 h-16 rounded-2xl overflow-hidden flex-shrink-0">
                    @if($verification->teacher?->avatar)
                        <img src="{{ $verification->teacher->avatar_url }}" class="w-full h-full object-cover" alt="">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white text-2xl font-black uppercase">{{ substr($verification->teacher?->name ?? '?', 0, 1) }}</div>
                    @endif
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-2 text-sm flex-1">
                    <div><span class="text-slate-400">Name</span><p class="font-medium">{{ $verification->teacher?->name }}</p></div>
                    <div><span class="text-slate-400">Email</span><p class="font-medium">{{ $verification->teacher?->email }}</p></div>
                    <div><span class="text-slate-400">Legal Name</span><p class="font-medium">{{ $verification->full_legal_name }}</p></div>
                    <div><span class="text-slate-400">National ID</span><p class="font-medium font-mono">{{ $verification->national_id_number }}</p></div>
                    @if($verification->teacher?->qualification)
                    <div><span class="text-slate-400">Qualification</span><p class="font-medium">{{ $verification->teacher->qualification }}</p></div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Documents --}}
        <div class="card p-6">
            <h2 class="section-title mb-4">Submitted Documents</h2>
            <div class="grid grid-cols-2 gap-3">
                @foreach([
                    ['id_document_front',     'ID / Passport Front'],
                    ['id_document_back',      'ID / Passport Back'],
                    ['proof_of_qualification','Proof of Qualification'],
                    ['selfie_with_id',        'Selfie with ID'],
                ] as [$field, $label])
                <div class="border border-slate-200 rounded-xl p-4 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                        </div>
                        <span class="text-sm font-medium text-slate-700">{{ $label }}</span>
                    </div>
                    @if($verification->$field)
                        <a href="{{ route('admin.verifications.download', [$verification, $field]) }}" target="_blank"
                           class="btn btn-secondary btn-xs">Download</a>
                    @else
                        <span class="text-xs text-slate-400">Not submitted</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Payout Details --}}
        <div class="card p-6">
            <h2 class="section-title mb-4">Payout Details</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                @foreach([
                    ['Bank',            $verification->bank_name],
                    ['Account No.',     $verification->bank_account_number],
                    ['Branch Code',     $verification->bank_branch_code],
                    ['EcoCash',         $verification->ecocash_number],
                    ['InnBucks',        $verification->innbucks_number],
                ] as [$label, $val])
                @if($val)
                <div><span class="text-slate-400">{{ $label }}</span><p class="font-medium font-mono">{{ $val }}</p></div>
                @endif
                @endforeach
            </div>
        </div>

        {{-- Admin Review Actions --}}
        @if($verification->status !== 'approved')
        <div class="card p-6 space-y-5" x-data="{ action: null }">
            <h2 class="section-title">Admin Decision</h2>

            @if($verification->reviewed_at)
            <p class="text-sm text-slate-400">Last reviewed {{ $verification->reviewed_at->format('d M Y') }} by {{ $verification->reviewer?->name }}</p>
            @endif

            <div class="flex gap-3 flex-wrap">
                <button @click="action='approve'" :class="action==='approve' ? 'btn-primary' : 'btn-secondary'" class="btn btn-sm">✅ Approve</button>
                <button @click="action='reject'" :class="action==='reject' ? 'btn-danger' : 'btn-secondary'" class="btn btn-sm">❌ Reject</button>
                <button @click="action='resubmit'" :class="action==='resubmit' ? 'border-orange-500 text-orange-700 bg-orange-50' : 'btn-secondary'" class="btn btn-sm">🔄 Request Resubmission</button>
            </div>

            {{-- Approve form --}}
            <form x-show="action === 'approve'" x-cloak method="POST" action="{{ route('admin.verifications.approve', $verification) }}" class="space-y-3 pt-1">
                @csrf
                <div class="form-group">
                    <label class="form-label">Admin Notes (optional)</label>
                    <textarea name="admin_notes" rows="2" class="form-textarea" placeholder="Congratulations — your account is verified...">{{ old('admin_notes') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Confirm Approval</button>
            </form>

            {{-- Reject form --}}
            <form x-show="action === 'reject'" x-cloak method="POST" action="{{ route('admin.verifications.reject', $verification) }}" class="space-y-3 pt-1">
                @csrf
                <input type="hidden" name="status" value="rejected">
                <div class="form-group">
                    <label class="form-label">Reason for Rejection <span class="text-red-500">*</span></label>
                    <textarea name="admin_notes" rows="3" class="form-textarea" required placeholder="Please provide a clear reason...">{{ old('admin_notes') }}</textarea>
                    @error('admin_notes') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn btn-danger btn-sm">Confirm Rejection</button>
            </form>

            {{-- Resubmit form --}}
            <form x-show="action === 'resubmit'" x-cloak method="POST" action="{{ route('admin.verifications.reject', $verification) }}" class="space-y-3 pt-1">
                @csrf
                <input type="hidden" name="status" value="needs_resubmission">
                <div class="form-group">
                    <label class="form-label">Instructions for Teacher <span class="text-red-500">*</span></label>
                    <textarea name="admin_notes" rows="3" class="form-textarea" required placeholder="Please re-upload a clearer photo of your ID...">{{ old('admin_notes') }}</textarea>
                    @error('admin_notes') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn btn-sm border-orange-500 text-orange-700 bg-orange-50 hover:bg-orange-100">Send Resubmission Request</button>
            </form>
        </div>
        @else
        <div class="card p-5 flex items-center gap-3 bg-emerald-50 border-emerald-200">
            <span class="text-2xl">✅</span>
            <div>
                <p class="font-semibold text-emerald-800">Verified and Approved</p>
                <p class="text-sm text-emerald-600">Reviewed {{ $verification->reviewed_at?->format('d M Y') }} by {{ $verification->reviewer?->name }}</p>
                @if($verification->admin_notes)
                    <p class="text-sm text-emerald-700 mt-1">{{ $verification->admin_notes }}</p>
                @endif
            </div>
        </div>
        @endif

    </div>
</x-app-layout>
