@extends('layouts.app')
@section('page-title', 'Subscription Plans')

@section('content')
<div class="space-y-5">
    <div class="flex items-end justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Subscription Plans</h1>
            <p class="text-sm text-slate-500">All-access pricing.</p>
        </div>
        <a href="{{ route('admin.plans.create') }}" class="px-3 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800">+ New plan</a>
    </div>

    @if(session('success'))<div class="px-4 py-2 rounded bg-emerald-50 text-emerald-800 text-sm">{{ session('success') }}</div>@endif

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                <tr><th class="text-left px-4 py-2">Name</th><th>Interval</th><th>Price USD</th><th>Active</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($plans as $p)
                <tr>
                    <td class="px-4 py-2 font-semibold">{{ $p->name }}</td>
                    <td class="px-2 capitalize">{{ $p->interval }}</td>
                    <td class="px-2">${{ number_format((float) $p->price_usd, 2) }}</td>
                    <td class="px-2">{{ $p->is_active ? 'Yes' : 'No' }}</td>
                    <td class="px-2 py-2 text-right">
                        <a href="{{ route('admin.plans.edit', $p) }}" class="text-xs text-slate-600 hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.plans.destroy', $p) }}" class="inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-rose-500 hover:underline ml-2">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $plans->links() }}
</div>
@endsection
