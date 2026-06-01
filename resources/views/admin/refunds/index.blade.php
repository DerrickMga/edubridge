@extends('layouts.app')
@section('page-title', 'Refund Requests')

@section('content')
<div class="space-y-5">
    <h1 class="text-2xl font-bold text-slate-900">Refund Requests</h1>

    @if(session('success'))<div class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-800 text-sm">{{ session('success') }}</div>@endif

    <div class="flex gap-2 text-sm">
        @foreach(['pending','approved','rejected','processed'] as $s)
            <a href="{{ route('admin.refunds.index', ['status' => $s]) }}"
               class="px-3 py-1.5 rounded-lg border {{ $status === $s ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 hover:bg-slate-50' }}">
                {{ ucfirst($s) }} <span class="opacity-60">({{ $counts[$s] }})</span>
            </a>
        @endforeach
        @if($status)
            <a href="{{ route('admin.refunds.index') }}" class="px-3 py-1.5 text-slate-500 hover:underline">Clear</a>
        @endif
    </div>

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                <tr><th class="text-left px-4 py-2">When</th><th>Student</th><th>Course</th><th>Amount</th><th>Reason</th><th>Status</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($requests as $r)
                <tr>
                    <td class="px-4 py-2 text-xs text-slate-500">{{ $r->created_at->format('d M H:i') }}</td>
                    <td class="px-2">{{ optional($r->user)->name }}<br><span class="text-xs text-slate-400">{{ optional($r->user)->email }}</span></td>
                    <td class="px-2">{{ optional($r->course)->title ?? '—' }}</td>
                    <td class="px-2">{{ $r->currency }} {{ number_format((float) $r->amount, 2) }}</td>
                    <td class="px-2 max-w-xs">
                        <details><summary class="cursor-pointer text-xs text-slate-500">View</summary>
                            <p class="text-xs text-slate-700 mt-1 whitespace-pre-wrap">{{ $r->reason }}</p>
                        </details>
                    </td>
                    <td class="px-2">
                        <span class="text-xs capitalize">{{ $r->status }}</span>
                        @if($r->decided_at)
                            <p class="text-[10px] text-slate-400">by {{ optional($r->decider)->name }} {{ $r->decided_at->diffForHumans() }}</p>
                        @endif
                    </td>
                    <td class="px-2 py-2 text-right">
                        <form method="POST" action="{{ route('admin.refunds.update', $r) }}" class="space-y-1">
                            @csrf @method('PATCH')
                            <select name="status" class="text-xs px-1 py-0.5 rounded border border-slate-200">
                                @foreach(\App\Models\RefundRequest::statuses() as $s)
                                    <option value="{{ $s }}" @selected($r->status === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                            <input name="admin_notes" placeholder="Notes" maxlength="2000"
                                   value="{{ $r->admin_notes }}"
                                   class="block w-40 text-xs px-1 py-0.5 rounded border border-slate-200">
                            <button class="text-xs px-2 py-0.5 rounded bg-slate-900 text-white">Update</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-6 text-center text-slate-400 text-sm">No requests.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $requests->links() }}
</div>
@endsection
