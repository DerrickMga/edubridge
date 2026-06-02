<x-app-layout>
    <x-slot name="title">Refund Requests — Admin</x-slot>

    <div class="space-y-6">
        <div class="page-header">
            <div>
                <h1 class="page-title">Refund Requests</h1>
                <p class="page-subtitle">Review and manage student refund requests.</p>
            </div>
        </div>

        {{-- Status tabs --}}
        <div class="flex gap-1 p-1 bg-slate-100 rounded-xl w-fit flex-wrap">
            @foreach([
                [null,        'All',       array_sum($counts)],
                ['pending',   'Pending',   $counts['pending']],
                ['approved',  'Approved',  $counts['approved']],
                ['rejected',  'Rejected',  $counts['rejected']],
                ['processed', 'Processed', $counts['processed']],
            ] as [$val, $label, $count])
            <a href="{{ route('admin.refunds.index', $val ? ['status' => $val] : []) }}"
               class="px-3 py-1.5 text-sm font-semibold rounded-lg transition-all {{ $status === $val ? 'bg-white shadow-sm text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
                {{ $label }} <span class="ml-1 opacity-60">{{ $count }}</span>
            </a>
            @endforeach
        </div>

        @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
        @endif

        <div class="card overflow-hidden">
            @if($requests->count())
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Amount</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Requested</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $r)
                        <tr>
                            <td class="font-mono text-xs text-slate-500">#{{ $r->id }}</td>
                            <td>
                                <div class="font-medium text-slate-800">{{ optional($r->user)->name ?? '—' }}</div>
                                <div class="text-xs text-slate-400">{{ optional($r->user)->email }}</div>
                            </td>
                            <td class="max-w-[180px] truncate text-sm">{{ optional($r->course)->title ?? '—' }}</td>
                            <td class="font-semibold text-slate-700">
                                {{ strtoupper($r->currency ?? 'GBP') }} {{ number_format((float) $r->amount, 2) }}
                            </td>
                            <td class="max-w-[200px]">
                                <details>
                                    <summary class="cursor-pointer text-xs text-slate-500 hover:text-slate-700">View reason</summary>
                                    <p class="text-xs text-slate-700 mt-1 whitespace-pre-wrap">{{ $r->reason ?? '—' }}</p>
                                </details>
                            </td>
                            <td>
                                @php
                                    $badge = match($r->status) {
                                        'pending'   => 'bg-yellow-100 text-yellow-800',
                                        'approved'  => 'bg-green-100 text-green-800',
                                        'rejected'  => 'bg-red-100 text-red-800',
                                        'processed' => 'bg-blue-100 text-blue-800',
                                        default     => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                                    {{ ucfirst($r->status) }}
                                </span>
                                @if($r->decided_at)
                                <p class="text-[10px] text-slate-400 mt-0.5">by {{ optional($r->decider)->name }} {{ $r->decided_at->diffForHumans() }}</p>
                                @endif
                            </td>
                            <td class="text-xs text-slate-500 whitespace-nowrap">{{ $r->created_at->format('d M Y') }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.refunds.update', $r) }}" class="flex gap-2 items-end flex-wrap">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status"
                                            class="text-xs border border-slate-300 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                                        @foreach(\App\Models\RefundRequest::statuses() as $s)
                                        <option value="{{ $s }}" @selected($r->status === $s)>{{ ucfirst($s) }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="admin_notes" value="{{ $r->admin_notes }}"
                                           placeholder="Notes…" maxlength="2000"
                                           class="text-xs border border-slate-300 rounded-lg px-2 py-1.5 w-36 focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                                    <button type="submit"
                                            class="px-3 py-1.5 text-xs font-semibold bg-violet-600 text-white rounded-lg hover:bg-violet-700 transition-colors">
                                        Save
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $requests->links() }}
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <svg class="w-12 h-12 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/>
                </svg>
                <p class="text-slate-500 font-medium">No refund requests found</p>
                <p class="text-slate-400 text-sm mt-1">
                    @if($status)
                        No <strong>{{ $status }}</strong> refund requests at this time.
                    @else
                        No refund requests have been submitted yet.
                    @endif
                </p>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
