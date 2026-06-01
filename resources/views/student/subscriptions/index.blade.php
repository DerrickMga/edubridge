@extends('layouts.app')
@section('page-title', 'Subscriptions')

@section('content')
<div class="space-y-6 max-w-4xl">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">All-Access Subscriptions</h1>
        <p class="text-sm text-slate-500">One plan, every course, no extra checkout.</p>
    </div>

    @if(session('success'))<div class="px-4 py-2 rounded bg-emerald-50 text-emerald-800 text-sm">{{ session('success') }}</div>@endif

    @if($active)
    <div class="card p-5 border-emerald-200 bg-emerald-50">
        <h2 class="font-semibold text-emerald-800">Active plan: {{ $active->plan->name }}</h2>
        <p class="text-sm text-emerald-700">Renews / expires {{ $active->expires_at->format('d M Y') }}.</p>
        <form method="POST" action="{{ route('student.subscriptions.cancel', $active) }}" class="mt-2" onsubmit="return confirm('Cancel subscription?')">
            @csrf
            <button class="text-xs text-rose-600 hover:underline">Cancel subscription</button>
        </form>
    </div>
    @endif

    <div class="grid md:grid-cols-2 gap-4">
        @forelse($plans as $p)
        <div class="card p-5 flex flex-col">
            <h3 class="font-bold text-slate-900">{{ $p->name }}</h3>
            <p class="text-xs uppercase text-slate-400">{{ $p->interval }}</p>
            <p class="text-3xl font-extrabold text-slate-900 my-2">${{ number_format((float) $p->price_usd, 2) }}</p>
            @if($p->description)<p class="text-sm text-slate-600 flex-1">{{ $p->description }}</p>@endif
            <form method="POST" action="{{ route('student.subscriptions.buy', $p) }}" class="mt-3">
                @csrf
                <button class="w-full py-2 rounded-lg bg-slate-900 text-white text-sm hover:bg-slate-800">
                    {{ $active ? 'Switch / extend' : 'Subscribe' }}
                </button>
            </form>
        </div>
        @empty
        <p class="text-slate-400 text-sm">No plans available yet. Check back soon.</p>
        @endforelse
    </div>

    @if($history->isNotEmpty())
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                <tr><th class="text-left px-4 py-2">When</th><th>Plan</th><th>Status</th><th>Expires</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($history as $h)
                <tr>
                    <td class="px-4 py-2 text-xs text-slate-500">{{ $h->created_at->format('d M Y') }}</td>
                    <td class="px-2">{{ optional($h->plan)->name }}</td>
                    <td class="px-2 capitalize">{{ $h->status }}</td>
                    <td class="px-2 text-xs text-slate-500">{{ optional($h->expires_at)->format('d M Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
