@extends('layouts.app')

@section('page-title', 'Teacher contract — '.$teacher->name)

@section('content')
<div class="max-w-5xl space-y-6">
    <div class="flex items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $teacher->name }}</h1>
            <p class="text-sm text-slate-500">{{ $teacher->email }}</p>
        </div>
        <a href="{{ route('admin.policies.index') }}" class="text-sm text-slate-500 hover:underline">← Back</a>
    </div>

    @if(session('success'))
        <div class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-800 text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="px-4 py-2 rounded-lg bg-rose-50 text-rose-800 text-sm">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
    @endif

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Current contract --}}
        <div class="card p-5 space-y-2">
            <h2 class="font-semibold text-slate-800 text-sm">Current contract</h2>
            @if($contract)
                <p class="text-sm">v{{ $contract->version }} — <strong class="capitalize">{{ str_replace('_', ' ', $contract->status) }}</strong></p>
                <p class="text-xs text-slate-500">Rate ${{ number_format((float) $contract->rate_usd, 2) }} · {{ $contract->payment_terms }}</p>
                @if($contract->signed_at)
                    <p class="text-xs text-slate-500">Signed {{ $contract->signed_at->format('d M Y H:i') }} by {{ $contract->signed_name }}</p>
                @endif
                @if($contract->expires_at)
                    <p class="text-xs text-slate-500">Expires {{ $contract->expires_at->format('d M Y') }}</p>
                @endif

                @if($contract->status === 'signed')
                <form method="POST" action="{{ route('admin.policies.contract.terminate', $contract) }}" class="mt-3 space-y-2"
                      onsubmit="return confirm('Terminate this contract?');">
                    @csrf
                    <input name="reason" required maxlength="500" placeholder="Reason for termination"
                           class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200">
                    <button class="px-3 py-2 text-sm rounded-lg bg-rose-600 text-white hover:bg-rose-500">Terminate</button>
                </form>
                @endif
            @else
                <p class="text-sm text-slate-500">No contract issued yet.</p>
            @endif
        </div>

        {{-- Issue new --}}
        <form method="POST" action="{{ route('admin.policies.contract.issue', $teacher) }}" class="card p-5 space-y-3">
            @csrf
            <h2 class="font-semibold text-slate-800 text-sm">Issue new contract</h2>
            <div class="grid grid-cols-2 gap-2 text-sm">
                <label><span class="block text-xs text-slate-500">Rate USD/hr</span>
                    <input name="rate_usd" type="number" step="0.01" min="0" value="{{ $teacher->hourly_rate_usd }}" class="w-full px-2 py-1.5 rounded border border-slate-200">
                </label>
                <label><span class="block text-xs text-slate-500">Term (months)</span>
                    <input name="term_months" type="number" min="1" max="60" value="12" class="w-full px-2 py-1.5 rounded border border-slate-200">
                </label>
                <label class="col-span-2"><span class="block text-xs text-slate-500">Payment terms</span>
                    <input name="payment_terms" maxlength="80" value="Monthly, paid within 14 days of month-end" class="w-full px-2 py-1.5 rounded border border-slate-200">
                </label>
                <label><span class="block text-xs text-slate-500">Exclusivity</span>
                    <select name="exclusivity" class="w-full px-2 py-1.5 rounded border border-slate-200">
                        <option value="non_exclusive">Non-exclusive</option>
                        <option value="exclusive">Exclusive</option>
                    </select>
                </label>
            </div>
            <label class="text-sm block"><span class="block text-xs text-slate-500">Addendum (optional)</span>
                <textarea name="addendum" rows="4" class="w-full px-2 py-1.5 rounded border border-slate-200"></textarea>
            </label>
            <div class="text-right">
                <button class="px-3 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800">Issue contract</button>
            </div>
        </form>
    </div>

    {{-- Outstanding acks --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800 text-sm">Outstanding policy acknowledgements</h2>
            <span class="text-xs text-slate-400">{{ $outstanding->count() }}</span>
        </div>
        @if($outstanding->isEmpty())
            <p class="px-5 py-3 text-sm text-emerald-700">All current policies acknowledged ✅</p>
        @else
            <ul class="divide-y divide-slate-100 text-sm">
                @foreach($outstanding as $p)
                    <li class="px-5 py-2 flex items-center justify-between">
                        <span>{{ $p->title }} <span class="text-xs text-slate-400">v{{ $p->version }}</span></span>
                        <span class="text-xs text-slate-400">{{ str_replace('_', ' ', $p->category) }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Incidents --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800 text-sm">Incidents</h2>
            <a href="{{ route('admin.policies.matrix') }}" class="text-xs text-slate-500 hover:underline">Log new →</a>
        </div>
        @if($incidents->isEmpty())
            <p class="px-5 py-3 text-sm text-slate-500">No incidents recorded.</p>
        @else
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                <tr><th class="text-left px-4 py-2">Event</th><th class="px-2">Severity</th><th class="px-2">Status</th><th class="px-2">Opened</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($incidents as $i)
                <tr>
                    <td class="px-4 py-2">{{ optional($i->event)->title ?? '—' }}</td>
                    <td class="px-2 capitalize">{{ $i->severity_override ?? optional($i->event)->severity }}</td>
                    <td class="px-2 capitalize">{{ $i->status }}</td>
                    <td class="px-2 text-xs text-slate-500">{{ optional($i->opened_at)->format('d M Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection
