@extends('layouts.app')
@section('page-title', 'Referrals')

@section('content')
<div class="space-y-6 max-w-3xl">
    <h1 class="text-2xl font-bold text-slate-900">Refer friends, earn credit</h1>
    <p class="text-sm text-slate-500">You earn 10% credit on the first paid order made by anyone who signs up through your link.</p>

    <div class="card p-5">
        <p class="text-xs uppercase text-slate-400 mb-1">Your referral link</p>
        <div class="flex gap-2 items-center">
            <input id="ref" value="{{ $shareUrl }}" readonly class="flex-1 px-3 py-2 rounded-lg border border-slate-200 text-sm font-mono">
            <button onclick="navigator.clipboard.writeText(document.getElementById('ref').value); this.textContent='Copied!'" class="px-3 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800">Copy</button>
        </div>
        <p class="text-xs text-slate-400 mt-2">Or share your code: <span class="font-mono font-bold">{{ auth()->user()->referral_code }}</span></p>
    </div>

    <div class="grid grid-cols-3 gap-3">
        <div class="card p-4"><p class="text-xs text-slate-400">Signed up via you</p><p class="text-2xl font-bold">{{ $referrals->count() }}</p></div>
        <div class="card p-4"><p class="text-xs text-slate-400">Credits earned</p><p class="text-2xl font-bold">${{ number_format($totalEarned, 2) }}</p></div>
        <div class="card p-4"><p class="text-xs text-slate-400">Pending</p><p class="text-2xl font-bold">{{ $credits->where('status', 'pending')->count() }}</p></div>
    </div>

    @if($credits->isNotEmpty())
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                <tr><th class="text-left px-4 py-2">When</th><th>Friend</th><th>Amount</th><th>Status</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($credits as $c)
                <tr>
                    <td class="px-4 py-2 text-xs text-slate-500">{{ $c->created_at->format('d M Y') }}</td>
                    <td class="px-2">{{ optional($c->referred)->name ?? '—' }}</td>
                    <td class="px-2">{{ $c->currency }} {{ number_format((float) $c->amount, 2) }}</td>
                    <td class="px-2 capitalize">{{ $c->status }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
