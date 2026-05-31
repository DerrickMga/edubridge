<x-app-layout>
<x-slot name="title">My Notebook</x-slot>

@if(session('success'))
<div class="mb-5 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
    {{ session('success') }}
</div>
@endif

<div class="page-header flex flex-wrap items-start justify-between gap-3 mb-6">
    <div>
        <h1 class="page-title">📓 My Notebook</h1>
        <p class="page-subtitle">All your saved notes, study plans, and advanced learning guides.</p>
    </div>
    <a href="{{ route('student.companion.index') }}"
       class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-700 text-sm font-semibold transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
        Open AI Companion
    </a>
</div>

{{-- Type filter tabs --}}
<div class="flex flex-wrap gap-2 mb-6">
    @foreach([
        ['all',           'All',                  $counts['all']],
        ['notes',         '📝 Notes',              $counts['notes']],
        ['study_plan',    '📅 Study Plans',        $counts['study_plan']],
        ['advanced_plan', '🗺️ Advanced Plans',    $counts['advanced_plan']],
    ] as [$key, $label, $count])
    <a href="{{ route('student.notebook.index', ['type' => $key]) }}"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-medium border transition-colors
              {{ $type === $key ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">
        {{ $label }}
        <span class="{{ $type === $key ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }} text-xs px-1.5 py-0.5 rounded-full font-bold">
            {{ $count }}
        </span>
    </a>
    @endforeach
</div>

@if($notebooks->isEmpty())
<div class="card p-12 text-center">
    <p class="text-4xl mb-3">📓</p>
    <p class="text-slate-700 font-semibold mb-1">Your notebook is empty</p>
    <p class="text-slate-400 text-sm mb-4">Generate notes or a study plan in the AI Companion, then save them here.</p>
    <a href="{{ route('student.companion.index') }}"
       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-colors">
        Open AI Companion →
    </a>
</div>
@else
<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($notebooks as $nb)
    <div class="card flex flex-col group relative">
        {{-- Pin indicator --}}
        @if($nb->is_pinned)
        <div class="absolute top-3 right-3">
            <span class="text-amber-400 text-xs font-bold">📌</span>
        </div>
        @endif

        <a href="{{ route('student.notebook.show', $nb) }}" class="flex-1 p-4 block">
            {{-- Type badge --}}
            <div class="flex items-center gap-2 mb-2">
                <span class="text-lg">{{ $nb->type_icon }}</span>
                <span class="text-xs font-semibold uppercase tracking-wide
                    {{ match($nb->type) {
                        'notes'         => 'text-emerald-600',
                        'study_plan'    => 'text-blue-600',
                        'advanced_plan' => 'text-violet-600',
                        default         => 'text-slate-400',
                    } }}">
                    {{ $nb->type_label }}
                </span>
            </div>

            {{-- Title --}}
            <h3 class="font-bold text-slate-900 text-sm leading-snug mb-1 line-clamp-2 group-hover:text-blue-600 transition-colors">
                {{ $nb->title }}
            </h3>

            {{-- Meta --}}
            <div class="flex flex-wrap gap-1.5 mb-3">
                @if($nb->subject)
                <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">{{ $nb->subject }}</span>
                @endif
                @if($nb->level)
                <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">{{ $nb->level }}</span>
                @endif
                @if($nb->topic)
                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-600">{{ Str::limit($nb->topic, 30) }}</span>
                @endif
            </div>

            {{-- Preview --}}
            @if($nb->type === 'notes')
            <p class="text-xs text-slate-400 line-clamp-3">{{ Str::limit(strip_tags($nb->content), 120) }}</p>
            @else
            @php $plan = $nb->plan; @endphp
            <p class="text-xs text-slate-400">
                {{ count($plan['weeks'] ?? []) }} weeks
                @if(isset($plan['overview'])) · {{ Str::limit($plan['overview'], 80) }}@endif
            </p>
            @endif

            {{-- YouTube indicator --}}
            @if($nb->youtube_videos)
            <p class="text-xs text-red-500 mt-1.5">🎬 {{ count($nb->youtube_videos) }} YouTube video group(s)</p>
            @endif
        </a>

        {{-- Footer --}}
        <div class="border-t border-slate-100 px-4 py-2 flex items-center justify-between">
            <span class="text-xs text-slate-400">{{ $nb->created_at->diffForHumans() }}</span>
            <div class="flex items-center gap-2">
                <button
                    onclick="event.preventDefault(); pinItem({{ $nb->id }}, this)"
                    class="text-xs text-slate-400 hover:text-amber-500 transition-colors"
                    title="{{ $nb->is_pinned ? 'Unpin' : 'Pin' }}">
                    {{ $nb->is_pinned ? '📌' : '📍' }}
                </button>
                <form action="{{ route('student.notebook.destroy', $nb) }}" method="POST"
                      onsubmit="return confirm('Delete this item from your notebook?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-slate-400 hover:text-red-500 transition-colors">🗑</button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-6">
    {{ $notebooks->links() }}
</div>
@endif

<script>
async function pinItem(id, btn) {
    const res = await fetch(`/student/notebook/${id}/pin`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
    });
    if (res.ok) window.location.reload();
}
</script>

</x-app-layout>
