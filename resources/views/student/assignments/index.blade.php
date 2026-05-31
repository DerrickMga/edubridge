<x-app-layout>
    <x-slot name="title">My Assignments</x-slot>

    <div class="page-header">
        <h1 class="page-title">My Assignments</h1>
        <p class="page-subtitle">Track your upcoming, submitted, and overdue work.</p>
    </div>

    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
        <svg class="w-4 h-4 flex-shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @php $totalCount = $pending->count() + $submitted->count() + $overdue->count(); @endphp

    @if($totalCount === 0)
    <div class="card empty-state">
        <span class="empty-state-icon">📋</span>
        <p class="empty-state-title">No assignments yet</p>
        <p class="empty-state-text">Your teachers haven't posted any assignments. Check back soon.</p>
    </div>
    @else

    {{-- Overdue --}}
    @if($overdue->isNotEmpty())
    <h2 class="section-title text-red-600 mb-3">Overdue ({{ $overdue->count() }})</h2>
    <div class="space-y-3 mb-7">
        @foreach($overdue as $a)
        <a href="{{ route('student.assignments.show', $a) }}"
           class="card flex items-center gap-4 p-4 hover:shadow-card-hover transition-all duration-200 border-l-4 border-red-400">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="badge-red text-xs">Overdue</span>
                    <span class="text-xs text-slate-400">{{ $a->course->title }}</span>
                </div>
                <p class="font-semibold text-slate-900 truncate">{{ $a->title }}</p>
                <p class="text-xs text-red-500 mt-0.5">Due {{ $a->due_at->diffForHumans() }}</p>
            </div>
            <div class="text-xs text-slate-400 flex-shrink-0">{{ ucfirst($a->type) }} · {{ $a->max_score }} pts</div>
            <svg class="w-4 h-4 text-slate-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        </a>
        @endforeach
    </div>
    @endif

    {{-- Pending --}}
    @if($pending->isNotEmpty())
    <h2 class="section-title mb-3">Pending ({{ $pending->count() }})</h2>
    <div class="space-y-3 mb-7">
        @foreach($pending as $a)
        <a href="{{ route('student.assignments.show', $a) }}"
           class="card flex items-center gap-4 p-4 hover:shadow-card-hover transition-all duration-200 border-l-4 border-amber-400">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="badge-amber text-xs">To do</span>
                    <span class="text-xs text-slate-400">{{ $a->course->title }}</span>
                </div>
                <p class="font-semibold text-slate-900 truncate">{{ $a->title }}</p>
                @if($a->due_at)
                <p class="text-xs text-slate-400 mt-0.5">Due {{ $a->due_at->format('D, d M Y') }}</p>
                @else
                <p class="text-xs text-slate-400 mt-0.5">No deadline</p>
                @endif
            </div>
            <div class="text-xs text-slate-400 flex-shrink-0">{{ ucfirst($a->type) }} · {{ $a->max_score }} pts</div>
            <svg class="w-4 h-4 text-slate-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        </a>
        @endforeach
    </div>
    @endif

    {{-- Submitted --}}
    @if($submitted->isNotEmpty())
    <h2 class="section-title mb-3">Submitted ({{ $submitted->count() }})</h2>
    <div class="space-y-3">
        @foreach($submitted as $a)
        @php $sub = $a->my_submission; @endphp
        <a href="{{ route('student.assignments.show', $a) }}"
           class="card flex items-center gap-4 p-4 hover:shadow-card-hover transition-all duration-200 border-l-4 {{ $sub->score !== null ? 'border-emerald-400' : 'border-slate-200' }}">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-0.5">
                    @if($sub->score !== null)
                    <span class="badge-green text-xs">Graded {{ $sub->score }}/{{ $a->max_score }}</span>
                    @else
                    <span class="badge-blue text-xs">Submitted</span>
                    @endif
                    <span class="text-xs text-slate-400">{{ $a->course->title }}</span>
                </div>
                <p class="font-semibold text-slate-900 truncate">{{ $a->title }}</p>
                <p class="text-xs text-slate-400 mt-0.5">Submitted {{ $sub->submitted_at?->diffForHumans() }}</p>
            </div>
            <div class="text-xs text-slate-400 flex-shrink-0">{{ ucfirst($a->type) }}</div>
            <svg class="w-4 h-4 text-slate-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        </a>
        @endforeach
    </div>
    @endif

    @endif
</x-app-layout>
