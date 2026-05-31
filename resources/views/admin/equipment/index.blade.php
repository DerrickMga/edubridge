<x-app-layout>
    <x-slot name="title">Equipment Loans</x-slot>

    <div class="space-y-6">

        {{-- Header --}}
        <div class="page-header">
            <div>
                <h1 class="page-title">Equipment Loans</h1>
                <p class="page-subtitle">Manage teacher equipment loan applications and track compliance.</p>
            </div>
            <a href="{{ route('admin.equipment.profiles') }}" class="btn btn-secondary btn-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                Equipment Profiles
            </a>
        </div>

        {{-- Compliance stat cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="stat-card">
                <p class="text-2xl font-bold text-slate-800">{{ $profileStats['ready'] }}</p>
                <p class="text-xs text-slate-500 mt-1">Ready to Teach</p>
                <p class="text-xs text-emerald-500 font-medium">Meets requirements</p>
            </div>
            <div class="stat-card">
                <p class="text-2xl font-bold text-amber-600">{{ $profileStats['needs_work'] }}</p>
                <p class="text-xs text-slate-500 mt-1">Needs Improvement</p>
            </div>
            <div class="stat-card">
                <p class="text-2xl font-bold text-slate-400">{{ $profileStats['incomplete'] }}</p>
                <p class="text-xs text-slate-500 mt-1">Incomplete Profiles</p>
            </div>
            <div class="stat-card">
                <p class="text-2xl font-bold text-red-500">{{ $profileStats['no_profile'] }}</p>
                <p class="text-xs text-slate-500 mt-1">No Profile Submitted</p>
            </div>
        </div>

        {{-- Loan filter tabs --}}
        <div class="flex flex-wrap gap-1 border-b border-slate-200">
            @foreach(['pending' => 'Pending', 'under_review' => 'Under Review', 'approved' => 'Approved', 'disbursed' => 'Disbursed', 'repaying' => 'Repaying', 'completed' => 'Completed', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
            <a href="{{ route('admin.equipment.index', ['status' => $key]) }}"
               class="px-4 py-2 text-sm transition-colors {{ $status === $key ? 'border-b-2 border-emerald-500 text-emerald-700 font-semibold' : 'text-slate-500 hover:text-slate-700' }}">
                {{ $label }}
                @if(isset($counts[$key]) && $counts[$key] > 0)
                <span class="ml-1 text-xs font-medium bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded-full">{{ $counts[$key] }}</span>
                @endif
            </a>
            @endforeach
        </div>

        {{-- Flash --}}
        @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- Loans table --}}
        @if($loans->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25M9 16.5v.75m3-3v3M15 12v5.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
            </div>
            <h3 class="empty-state-title">No applications</h3>
            <p class="empty-state-text">No loan applications with status "{{ $status }}" at this time.</p>
        </div>
        @else
        <div class="card overflow-hidden">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Teacher</th>
                        <th>Items Requested</th>
                        <th>Amount</th>
                        <th>Period</th>
                        <th>Submitted</th>
                        <th>Status</th>
                        <th>Equipment</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($loans as $loan)
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                @if($loan->teacher->avatar_url)
                                <img src="{{ $loan->teacher->avatar_url }}" class="w-8 h-8 rounded-full object-cover" alt="">
                                @else
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white text-xs font-bold">{{ substr($loan->teacher->name, 0, 1) }}</div>
                                @endif
                                <div>
                                    <p class="font-medium text-sm text-slate-800">{{ $loan->teacher->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $loan->reference_number }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                @foreach(array_slice($loan->requestedItemLabels(), 0, 3) as $itemLabel)
                                <span class="badge-slate text-xs">{{ $itemLabel }}</span>
                                @endforeach
                                @if(count($loan->requestedItemLabels()) > 3)
                                <span class="badge-slate text-xs">+{{ count($loan->requestedItemLabels()) - 3 }} more</span>
                                @endif
                            </div>
                        </td>
                        <td class="font-semibold text-slate-800">${{ number_format($loan->amount_requested_usd, 2) }}</td>
                        <td class="text-sm text-slate-600">{{ $loan->repayment_period_months }}mo</td>
                        <td class="text-sm text-slate-500">{{ $loan->created_at->format('d M Y') }}</td>
                        <td><span class="{{ $loan->statusBadgeClass() }}">{{ $loan->statusLabel() }}</span></td>
                        <td>
                            @if($loan->teacher->equipmentProfile)
                            <span class="{{ $loan->teacher->equipmentProfile->statusBadgeClass() }} text-xs">{{ $loan->teacher->equipmentProfile->statusLabel() }}</span>
                            @else
                            <span class="badge-slate text-xs">No Profile</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.equipment.show', $loan) }}" class="btn btn-secondary btn-xs">Review</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-end">
            {{ $loans->links() }}
        </div>
        @endif

    </div>
</x-app-layout>
