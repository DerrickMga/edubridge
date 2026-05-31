<x-app-layout>
    <x-slot name="title">Equipment Profiles</x-slot>

    <div class="space-y-6">

        <div class="page-header">
            <div>
                <h1 class="page-title">Equipment Profiles</h1>
                <p class="page-subtitle">Teacher self-declared equipment compliance status.</p>
            </div>
            <a href="{{ route('admin.equipment.index') }}" class="btn btn-secondary btn-sm">← Loan Applications</a>
        </div>

        {{-- Status filter --}}
        <div class="flex flex-wrap gap-1 border-b border-slate-200">
            @foreach(['all' => 'All Profiles', 'meets_requirements' => 'Ready to Teach', 'needs_improvement' => 'Needs Improvement', 'incomplete' => 'Incomplete'] as $key => $label)
            <a href="{{ route('admin.equipment.profiles', ['status' => $key]) }}"
               class="px-4 py-2 text-sm transition-colors {{ $status === $key ? 'border-b-2 border-emerald-500 text-emerald-700 font-semibold' : 'text-slate-500 hover:text-slate-700' }}">
                {{ $label }}
                @if(isset($counts[$key]) && $counts[$key] > 0)
                <span class="ml-1 text-xs bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded-full">{{ $counts[$key] }}</span>
                @endif
            </a>
            @endforeach
        </div>

        @if($profiles->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg></div>
            <h3 class="empty-state-title">No profiles</h3>
            <p class="empty-state-text">No equipment profiles found for the selected filter.</p>
        </div>
        @else
        <div class="card overflow-hidden">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Teacher</th>
                        <th>Internet</th>
                        <th>Device & Camera</th>
                        <th>Lighting</th>
                        <th>Headset</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($profiles as $profile)
                    @php $reqs = $profile->requirementsStatus(); @endphp
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                @if($profile->teacher->avatar_url)
                                <img src="{{ $profile->teacher->avatar_url }}" class="w-8 h-8 rounded-full object-cover">
                                @else
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white text-xs font-bold">{{ substr($profile->teacher->name, 0, 1) }}</div>
                                @endif
                                <div>
                                    <p class="font-medium text-sm text-slate-800">{{ $profile->teacher->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $profile->teacher->email }}</p>
                                </div>
                            </div>
                        </td>
                        @foreach($reqs as $req)
                        <td>
                            @if($req['met'])
                            <svg class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            @else
                            <svg class="w-5 h-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                            @endif
                        </td>
                        @endforeach
                        <td><span class="{{ $profile->statusBadgeClass() }}">{{ $profile->statusLabel() }}</span></td>
                        <td class="text-sm text-slate-500">{{ $profile->updated_at->format('d M Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-end">
            {{ $profiles->links() }}
        </div>
        @endif

    </div>
</x-app-layout>
