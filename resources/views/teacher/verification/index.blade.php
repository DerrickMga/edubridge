<x-app-layout>
    <x-slot name="title">Account Verification</x-slot>

    <div class="max-w-2xl mx-auto space-y-6">

        <div class="page-header">
            <div>
                <h1 class="page-title">Account Verification</h1>
                <p class="page-subtitle">Complete KYC verification to unlock settlement payouts and build trust with students.</p>
            </div>
        </div>

        {{-- Status Banner --}}
        @if($verification)
            @php
                $banner = match($verification->status) {
                    'approved'           => ['bg-emerald-50 border-emerald-200 text-emerald-800', '✅', 'Verified', 'Your account is fully verified. You can now request settlements.'],
                    'pending'            => ['bg-amber-50 border-amber-200 text-amber-800', '⏳', 'Under Review', 'Your documents are being reviewed. We\'ll notify you within 1–2 business days.'],
                    'rejected'           => ['bg-red-50 border-red-200 text-red-800', '❌', 'Rejected', 'Your verification was rejected. Please review the notes below and resubmit.'],
                    'needs_resubmission' => ['bg-orange-50 border-orange-200 text-orange-800', '🔄', 'Resubmission Required', 'Some documents need to be updated. Please review the notes and resubmit.'],
                    default              => ['bg-slate-50 border-slate-200 text-slate-800', 'ℹ️', 'Unknown', ''],
                };
            @endphp
            <div class="border rounded-2xl px-5 py-4 {{ $banner[0] }}">
                <div class="flex items-start gap-3">
                    <span class="text-xl flex-shrink-0">{{ $banner[1] }}</span>
                    <div>
                        <p class="font-semibold">{{ $banner[2] }}</p>
                        <p class="text-sm mt-0.5 opacity-80">{{ $banner[3] }}</p>
                        @if($verification->admin_notes)
                            <div class="mt-2 text-sm bg-white/60 rounded-xl px-3 py-2">
                                <p class="font-medium mb-1">Admin Notes:</p>
                                <p>{{ $verification->admin_notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info">
                <svg class="w-5 h-5 flex-shrink-0 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                <span>Submit your documents to get verified. Verified teachers appear higher in search results and can request settlements.</span>
            </div>
        @endif

        {{-- Form (show unless already approved) --}}
        @if(!$verification || $verification->status !== 'approved')
        <form method="POST" action="{{ route('teacher.verification.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Identity Section --}}
            <div class="card p-6 space-y-5">
                <h2 class="section-title">Identity Documents</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label class="form-label" for="full_legal_name">Full Legal Name</label>
                        <input type="text" id="full_legal_name" name="full_legal_name" value="{{ old('full_legal_name', $verification?->full_legal_name) }}" class="form-input" required placeholder="As it appears on your ID">
                        @error('full_legal_name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="national_id_number">National ID / Passport Number</label>
                        <input type="text" id="national_id_number" name="national_id_number" value="{{ old('national_id_number', $verification?->national_id_number) }}" class="form-input" required placeholder="63-123456X78">
                        @error('national_id_number') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- File uploads --}}
                @foreach([
                    ['id_document_front', 'National ID / Passport (Front)', true,  'Clear photo of the front of your ID'],
                    ['id_document_back',  'National ID / Passport (Back)',  false, 'Back side if applicable'],
                    ['proof_of_qualification', 'Proof of Qualification',    false, 'Degree, diploma or teaching certificate'],
                    ['selfie_with_id',    'Selfie Holding Your ID',         false, 'Hold your ID next to your face'],
                ] as [$field, $label, $required, $hint])
                <div class="form-group" x-data="{ name: '{{ $verification?->$field ? 'Current file on record' : '' }}' }">
                    <label class="form-label" for="file_{{ $field }}">{{ $label }} @if($required)<span class="text-red-500">*</span>@endif</label>
                    @if($verification?->$field)
                        <div class="flex items-center gap-2 mb-2 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            File submitted — upload a new file to replace it
                        </div>
                    @endif
                    <label for="file_{{ $field }}" class="flex items-center gap-3 border-2 border-dashed border-slate-300 hover:border-emerald-400 rounded-xl px-4 py-4 cursor-pointer transition-colors group">
                        <svg class="w-5 h-5 text-slate-400 group-hover:text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                        <span class="text-sm text-slate-500 group-hover:text-emerald-600" x-text="name || 'Click to upload file'"></span>
                        <input type="file" id="file_{{ $field }}" name="{{ $field }}" class="sr-only" @required($required && !$verification?->$field) accept=".jpg,.jpeg,.png,.pdf"
                               @change="name = $event.target.files[0]?.name ?? ''">
                    </label>
                    <p class="form-hint">{{ $hint }} · JPG, PNG or PDF · Max 5 MB</p>
                    @error($field) <p class="form-error">{{ $message }}</p> @enderror
                </div>
                @endforeach
            </div>

            {{-- Payout Details --}}
            <div class="card p-6 space-y-5">
                <div>
                    <h2 class="section-title">Payout Details</h2>
                    <p class="text-sm text-slate-500 mt-1">At least one payout method is required to enable settlements.</p>
                </div>

                <div class="space-y-4">
                    <p class="section-label pt-1">Bank Transfer (USD)</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="form-group">
                            <label class="form-label text-xs" for="bank_name">Bank Name</label>
                            <input type="text" id="bank_name" name="bank_name" value="{{ old('bank_name', $verification?->bank_name) }}" placeholder="CBZ Bank" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label text-xs" for="bank_account_number">Account Number</label>
                            <input type="text" id="bank_account_number" name="bank_account_number" value="{{ old('bank_account_number', $verification?->bank_account_number) }}" placeholder="0012345678" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label text-xs" for="bank_branch_code">Branch Code</label>
                            <input type="text" id="bank_branch_code" name="bank_branch_code" value="{{ old('bank_branch_code', $verification?->bank_branch_code) }}" placeholder="060165" class="form-input">
                        </div>
                    </div>

                    <p class="section-label pt-1">Mobile Money (ZWG)</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="form-group">
                            <label class="form-label text-xs" for="ecocash_number">EcoCash Number</label>
                            <input type="text" id="ecocash_number" name="ecocash_number" value="{{ old('ecocash_number', $verification?->ecocash_number) }}" placeholder="0771234567" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label text-xs" for="innbucks_number">InnBucks Number</label>
                            <input type="text" id="innbucks_number" name="innbucks_number" value="{{ old('innbucks_number', $verification?->innbucks_number) }}" placeholder="0781234567" class="form-input">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <p class="text-xs text-slate-400">Documents are stored securely and only seen by EduBridge admins.</p>
                <button type="submit" class="btn btn-primary">
                    {{ $verification ? 'Resubmit for Review' : 'Submit for Verification' }}
                </button>
            </div>
        </form>
        @endif

    </div>
</x-app-layout>
