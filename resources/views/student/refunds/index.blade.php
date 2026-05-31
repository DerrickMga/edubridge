@extends('layouts.app')
@section('page-title', 'Refund Requests')

@section('content')
<div class="space-y-5">
    <h1 class="text-2xl font-bold text-slate-900">My Refund Requests</h1>

    @if(session('success'))<div class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-800 text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="px-4 py-2 rounded-lg bg-rose-50 text-rose-800 text-sm">{{ session('error') }}</div>@endif

    @if($requests->isEmpty())
        <div class="card p-8 text-center text-slate-500 text-sm">No refund requests yet.</div>
    @else
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                <tr><th class="text-left px-4 py-2">Course</th><th>Amount</th><th>Status</th><th>Requested</th><th>Decision</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($requests as $r)
                <tr>
                    <td class="px-4 py-2">{{ optional($r->course)->title ?? 'Course removed' }}</td>
                    <td class="px-2">{{ $r->currency }} {{ number_format((float) $r->amount, 2) }}</td>
                    <td class="px-2"><span class="text-xs capitalize">{{ $r->status }}</span></td>
                    <td class="px-2 text-xs text-slate-500">{{ $r->created_at->diffForHumans() }}</td>
                    <td class="px-2 text-xs text-slate-500">
                        @if($r->decided_at){{ $r->decided_at->format('d M Y') }}@endif
                        @if($r->admin_notes)<p class="text-slate-600 mt-1">{{ $r->admin_notes }}</p>@endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $requests->links() }}
    @endif
</div>
@endsection
