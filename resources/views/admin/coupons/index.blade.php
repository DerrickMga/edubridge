@extends('layouts.app')
@section('page-title', 'Coupons')

@section('content')
<div class="space-y-5">
    <div class="flex items-end justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Discount Coupons</h1>
            <p class="text-sm text-slate-500">Promo codes applied at checkout.</p>
        </div>
        <a href="{{ route('admin.coupons.create') }}" class="px-3 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800">+ New coupon</a>
    </div>

    @if(session('success'))<div class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-800 text-sm">{{ session('success') }}</div>@endif

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                <tr><th class="text-left px-4 py-2">Code</th><th>Type</th><th>Value</th><th>Scope</th><th>Used</th><th>Window</th><th>Active</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($coupons as $c)
                <tr>
                    <td class="px-4 py-2 font-mono font-semibold">{{ $c->code }}</td>
                    <td class="px-2 capitalize">{{ $c->type }}</td>
                    <td class="px-2">{{ $c->type === 'percent' ? rtrim(rtrim((string) $c->value, '0'), '.').'%' : $c->currency.' '.number_format((float) $c->value, 2) }}</td>
                    <td class="px-2">{{ optional($c->course)->title ?? 'Global' }}</td>
                    <td class="px-2">{{ $c->redemptions_count }}{{ $c->max_redemptions ? '/'.$c->max_redemptions : '' }}</td>
                    <td class="px-2 text-xs text-slate-500">
                        @if($c->starts_at){{ $c->starts_at->format('d M') }} → @endif
                        @if($c->expires_at){{ $c->expires_at->format('d M Y') }}@else &mdash; @endif
                    </td>
                    <td class="px-2">
                        <span class="text-xs px-2 py-0.5 rounded {{ $c->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $c->is_active ? 'on' : 'off' }}</span>
                    </td>
                    <td class="px-2 py-2 text-right">
                        <a href="{{ route('admin.coupons.edit', $c) }}" class="text-xs text-slate-600 hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.coupons.destroy', $c) }}" class="inline" onsubmit="return confirm('Delete coupon?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-rose-500 hover:underline ml-2">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $coupons->links() }}
</div>
@endsection
