<x-app-layout>
<x-slot name="title">Policies & Contract</x-slot>
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Policies & Contract</h1>
            <p class="text-sm text-slate-500">Review platform policies and sign your teaching contract.</p>
        </div>
        <a href="{{ route('teacher.policies.contract') }}" class="px-3 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800">View my contract</a>
    </div>

    @if(session('success'))
        <div class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-800 text-sm">{{ session('success') }}</div>
    @endif

    {{-- Contract status --}}
    @if($contract)
    <div class="card p-5 border-l-4 {{ $contract->status === 'signed' ? 'border-emerald-400' : 'border-amber-400' }}">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">Contract v{{ $contract->version }}</p>
                <p class="text-lg font-semibold text-slate-900 capitalize">{{ str_replace('_', ' ', $contract->status) }}</p>
                @if($contract->signed_at)
                    <p class="text-xs text-slate-500 mt-1">Signed {{ $contract->signed_at->format('d M Y, H:i') }} as <strong>{{ $contract->signed_name }}</strong></p>
                @endif
            </div>
            <a href="{{ route('teacher.policies.contract') }}" class="text-sm text-slate-700 hover:underline">Open →</a>
        </div>
    </div>
    @endif

    {{-- Outstanding acks --}}
    @if($outstanding->isNotEmpty())
    <div class="card p-5 border-l-4 border-amber-400">
        <h2 class="font-semibold text-slate-800 mb-2">{{ $outstanding->count() }} policy{{ $outstanding->count() === 1 ? '' : 'ies' }} need your acknowledgement</h2>
        <ul class="divide-y divide-slate-100">
            @foreach($outstanding as $p)
            <li class="flex items-center justify-between py-2 text-sm">
                <span>
                    <span class="font-medium text-slate-800">{{ $p->title }}</span>
                    <span class="text-xs text-slate-400 ml-2">v{{ $p->version }} · {{ str_replace('_', ' ', $p->category) }}</span>
                </span>
                <a href="{{ route('teacher.policies.show', $p) }}" class="text-xs px-2 py-1 rounded bg-slate-900 text-white hover:bg-slate-800">Review & acknowledge</a>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Full library --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800 text-sm">Policy library</h2>
        </div>
        <ul class="divide-y divide-slate-100">
            @foreach($library as $p)
            <li class="px-5 py-3 flex items-center justify-between">
                <div>
                    <p class="font-medium text-slate-900 text-sm">{{ $p->title }}</p>
                    <p class="text-xs text-slate-400">{{ str_replace('_', ' ', $p->category) }} · v{{ $p->version }} · effective {{ optional($p->effective_at)->format('d M Y') }}</p>
                </div>
                <a href="{{ route('teacher.policies.show', $p) }}" class="text-xs text-slate-600 hover:underline">Read</a>
            </li>
            @endforeach
        </ul>
    </div>
</div>
</x-app-layout>
