<x-app-layout>
    <x-slot name="title">Teacher Verifications</x-slot>

    <div class="space-y-6">

        <div class="page-header">
            <div>
                <h1 class="page-title">KYC Verifications</h1>
                <p class="page-subtitle">Review and process teacher identity verification submissions.</p>
            </div>
            <div class="flex items-center gap-2 text-sm font-medium text-amber-600 bg-amber-50 border border-amber-200 rounded-xl px-4 py-2">
                {{ $counts['pending'] }} pending review
            </div>
        </div>

        {{-- Status filter tabs --}}
        <div class="flex gap-1 p-1 bg-slate-100 rounded-xl w-fit">
            @foreach([
                ['all',                'All',             $counts['all']],
                ['pending',            'Pending',         $counts['pending']],
                ['approved',           'Approved',        $counts['approved']],
                ['rejected',           'Rejected',        $counts['rejected']],
                ['needs_resubmission', 'Resubmit',        $counts['needs_resubmission']],
            ] as [$val, $label, $count])
            <a href="{{ route('admin.verifications.index', ['status' => $val]) }}"
               class="px-3 py-1.5 text-sm font-semibold rounded-lg transition-all {{ request('status', 'all') === $val ? 'bg-white shadow-sm text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
                {{ $label }} <span class="ml-1 opacity-60">{{ $count }}</span>
            </a>
            @endforeach
        </div>

        <div class="card overflow-hidden">
            @if($verifications->count())
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Teacher</th>
                            <th>Submitted</th>
                            <th>Legal Name</th>
                            <th>Status</th>
                            <th>Reviewed By</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($verifications as $v)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl overflow-hidden flex-shrink-0">
                                        @if($v->teacher?->avatar)
                                            <img src="{{ $v->teacher->avatar_url }}" class="w-full h-full object-cover" alt="">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white text-xs font-black uppercase">{{ substr($v->teacher?->name ?? '?', 0, 1) }}</div>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-800 text-sm">{{ $v->teacher?->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $v->teacher?->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="text-xs text-slate-500">
                                {{ $v->submitted_at?->format('d M Y') ?? $v->created_at->format('d M Y') }}
                            </td>
                            <td class="text-sm text-slate-700">{{ $v->full_legal_name }}</td>
                            <td><span class="{{ $v->statusBadgeClass() }}">{{ ucwords(str_replace('_', ' ', $v->status)) }}</span></td>
                            <td class="text-xs text-slate-400">{{ $v->reviewer?->name ?? '—' }}</td>
                            <td>
                                <a href="{{ route('admin.verifications.show', $v) }}" class="btn btn-secondary btn-xs">Review</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-3">{{ $verifications->withQueryString()->links() }}</div>
            @else
            <div class="empty-state">
                <span class="empty-state-icon">🔍</span>
                <p class="empty-state-title">No verifications found</p>
                <p class="empty-state-text">No teacher verification requests match the selected filter.</p>
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
