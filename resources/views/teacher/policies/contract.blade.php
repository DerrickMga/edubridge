<x-app-layout>
<x-slot name="title">My Teaching Contract</x-slot>
<div class="max-w-4xl space-y-5">
    <div class="flex items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Teaching Contract</h1>
            <p class="text-sm text-slate-500">Version {{ $contract->version }} · status <strong class="capitalize">{{ str_replace('_', ' ', $contract->status) }}</strong></p>
        </div>
        <a href="{{ route('teacher.policies.index') }}" class="text-sm text-slate-500 hover:underline">← Policies</a>
    </div>

    @if(session('success'))
        <div class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-800 text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="px-4 py-2 rounded-lg bg-rose-50 text-rose-800 text-sm">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
    @endif

    {{-- Key terms --}}
    <div class="card p-5 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
        <div>
            <p class="text-xs text-slate-400 uppercase">Hourly rate</p>
            <p class="font-semibold">${{ number_format((float) ($contract->rate_usd ?? 0), 2) }}</p>
        </div>
        <div>
            <p class="text-xs text-slate-400 uppercase">Payment terms</p>
            <p class="font-semibold">{{ $contract->payment_terms ?? '—' }}</p>
        </div>
        <div>
            <p class="text-xs text-slate-400 uppercase">Term</p>
            <p class="font-semibold">{{ $contract->term_months ? $contract->term_months.' months' : '—' }}</p>
        </div>
        <div>
            <p class="text-xs text-slate-400 uppercase">Exclusivity</p>
            <p class="font-semibold">{{ str_replace('_', '-', $contract->exclusivity) }}</p>
        </div>
    </div>

    {{-- Snapshotted policies --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800 text-sm">Policies incorporated by reference</h2>
        </div>
        <ul class="divide-y divide-slate-100">
            @foreach(($contract->terms_snapshot ?? []) as $t)
            <li class="px-5 py-2 flex items-center justify-between text-sm">
                <span>{{ $t['title'] ?? $t['slug'] }} <span class="text-xs text-slate-400 ml-1">v{{ $t['version'] ?? 1 }}</span></span>
                <a href="{{ route('teacher.policies.show', $t['policy_id'] ?? 0) }}" class="text-xs text-slate-500 hover:underline">Read</a>
            </li>
            @endforeach
        </ul>
    </div>

    @if($contract->addendum)
    <div class="card p-5">
        <p class="text-xs text-slate-400 uppercase mb-1">Addendum</p>
        <div class="text-sm text-slate-700 whitespace-pre-wrap">{{ $contract->addendum }}</div>
    </div>
    @endif

    @if($contract->status === 'signed')
        <div class="card p-5 border-l-4 border-emerald-400">
            <p class="text-sm">✅ Signed by <strong>{{ $contract->signed_name }}</strong> on {{ $contract->signed_at->format('d M Y, H:i') }} from {{ $contract->signed_ip ?? 'unknown IP' }}.</p>
            @if($contract->expires_at)
                <p class="text-xs text-slate-500 mt-1">Expires {{ $contract->expires_at->format('d M Y') }}.</p>
            @endif
        </div>
    @elseif($contract->status === 'pending')
        @if($outstanding->isNotEmpty())
            <div class="card p-5 border-l-4 border-amber-400">
                <p class="text-sm text-amber-800 font-semibold">Please acknowledge all {{ $outstanding->count() }} active polic{{ $outstanding->count() === 1 ? 'y' : 'ies' }} before signing.</p>
                <a href="{{ route('teacher.policies.index') }}" class="inline-block mt-2 text-xs px-2 py-1 rounded bg-slate-900 text-white">Review policies</a>
            </div>
        @else
            <form method="POST" action="{{ route('teacher.policies.contract.sign', $contract) }}" class="card p-5 space-y-3">
                @csrf
                <p class="text-sm text-slate-700">By typing your full legal name below and clicking <em>Sign contract</em>, you confirm you have read every policy listed above and agree to be bound by this contract.</p>
                <input type="text" name="typed_name" required placeholder="Your full legal name"
                       class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200">
                <label class="flex items-start gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="agree" value="1" required class="mt-0.5 rounded border-slate-300">
                    <span>I agree to the terms of this contract and the policies incorporated by reference.</span>
                </label>
                <div class="flex items-center justify-end">
                    <button class="px-3 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800">Sign contract</button>
                </div>
            </form>
        @endif
    @elseif($contract->status === 'terminated')
        <div class="card p-5 border-l-4 border-rose-400">
            <p class="text-sm font-semibold text-rose-800">Contract terminated {{ optional($contract->terminated_at)->format('d M Y') }}.</p>
            @if($contract->terminated_reason)<p class="text-xs text-slate-600 mt-1">Reason: {{ $contract->terminated_reason }}</p>@endif
        </div>
    @endif
</div>
</x-app-layout>
