@extends('layouts.app')

@section('page-title', 'Policies & Contracts')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Policies & Contracts</h1>
            <p class="text-sm text-slate-500">Versioned policy library, teacher contracts and contingency matrix.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.policies.matrix') }}" class="px-3 py-2 text-sm rounded-lg bg-white border border-slate-200 hover:bg-slate-50">Risk matrix</a>
            <a href="{{ route('admin.policies.create') }}" class="px-3 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800">New policy</a>
        </div>
    </div>

    @if(session('success'))
        <div class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-800 text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        @foreach([
            ['Policies', $kpis['policies']],
            ['Teachers', $kpis['teachers']],
            ['Signed contracts', $kpis['signed']],
            ['Pending contracts', $kpis['pending']],
            ['Open incidents', $kpis['open_inc']],
        ] as $kpi)
        <div class="card p-4">
            <p class="text-xs text-slate-400 uppercase">{{ $kpi[0] }}</p>
            <p class="text-2xl font-bold text-slate-900">{{ $kpi[1] }}</p>
        </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Policy library --}}
        <div class="card overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-semibold text-slate-800 text-sm">Policy library</h2>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                    <tr><th class="text-left px-4 py-2">Title</th><th class="px-2">Cat</th><th class="px-2">v</th><th class="px-2">Active</th><th></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($library as $p)
                    <tr>
                        <td class="px-4 py-2 font-medium text-slate-800">{{ $p->title }}</td>
                        <td class="px-2 text-xs text-slate-500">{{ str_replace('_', ' ', $p->category) }}</td>
                        <td class="px-2">{{ $p->version }}</td>
                        <td class="px-2">
                            <span class="inline-block text-xs px-2 py-0.5 rounded {{ $p->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $p->is_active ? 'active' : 'off' }}</span>
                        </td>
                        <td class="px-2 py-2 text-right">
                            <form method="POST" action="{{ route('admin.policies.toggle', $p) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="text-xs text-slate-600 hover:underline">{{ $p->is_active ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Recent contracts --}}
        <div class="card overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100">
                <h2 class="font-semibold text-slate-800 text-sm">Recent contracts</h2>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                    <tr><th class="text-left px-4 py-2">Teacher</th><th class="px-2">v</th><th class="px-2">Status</th><th></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($contracts as $c)
                    <tr>
                        <td class="px-4 py-2">{{ optional($c->teacher)->name ?? 'Unknown' }}</td>
                        <td class="px-2">{{ $c->version }}</td>
                        <td class="px-2"><span class="text-xs capitalize">{{ str_replace('_', ' ', $c->status) }}</span></td>
                        <td class="px-2 text-right">
                            @if($c->teacher)
                                <a href="{{ route('admin.policies.contract.show', $c->teacher) }}" class="text-xs text-slate-600 hover:underline">Open</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Teachers list --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800 text-sm">Teacher roster</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                <tr><th class="text-left px-4 py-2">Teacher</th><th class="px-2">Email</th><th class="px-2">Contract</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($teachers as $t)
                <tr>
                    <td class="px-4 py-2">{{ $t->name }}</td>
                    <td class="px-2 text-xs text-slate-500">{{ $t->email }}</td>
                    <td class="px-2">
                        @if($t->hasSignedContract())
                            <span class="text-xs px-2 py-0.5 rounded bg-emerald-50 text-emerald-700">signed</span>
                        @else
                            <span class="text-xs px-2 py-0.5 rounded bg-amber-50 text-amber-700">not signed</span>
                        @endif
                    </td>
                    <td class="px-2 py-2 text-right">
                        <a href="{{ route('admin.policies.contract.show', $t) }}" class="text-xs text-slate-600 hover:underline">Manage →</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
