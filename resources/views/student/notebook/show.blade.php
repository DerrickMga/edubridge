<x-app-layout>
<x-slot name="title">{{ $notebook->title }}</x-slot>

@push('head')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css">
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/contrib/auto-render.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3/dist/purify.min.js"></script>
<style>
.prose-nb{font-size:.9rem;line-height:1.7;color:#1e293b}
.prose-nb h1,.prose-nb h2,.prose-nb h3{font-weight:700;color:#0f172a;margin:.9em 0 .4em}
.prose-nb h1{font-size:1.3em}.prose-nb h2{font-size:1.15em;border-bottom:1px solid #e2e8f0;padding-bottom:.3em}
.prose-nb h3{font-size:1.05em}
.prose-nb ul,.prose-nb ol{padding-left:1.5em;margin:.4em 0}
.prose-nb ul{list-style:disc}.prose-nb ol{list-style:decimal}
.prose-nb li{margin:.25em 0}
.prose-nb strong{font-weight:700}
.prose-nb code{background:#f1f5f9;border-radius:3px;padding:.1em .35em;font-family:monospace;font-size:.85em}
.prose-nb pre{background:#1e293b;color:#e2e8f0;border-radius:.6em;padding:.9em;overflow-x:auto;margin:.6em 0}
.prose-nb pre code{background:none;padding:0;color:inherit}
.prose-nb blockquote{border-left:3px solid #10b981;padding-left:.8em;color:#64748b;font-style:italic;margin:.5em 0}
.prose-nb table{border-collapse:collapse;width:100%;margin:.6em 0;font-size:.85em}
.prose-nb th,.prose-nb td{border:1px solid #e2e8f0;padding:.35em .7em}
.prose-nb th{background:#f8fafc;font-weight:600}
</style>
@endpush

{{-- Back + actions --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <a href="{{ route('student.notebook.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-800 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        My Notebook
    </a>
    <div class="flex items-center gap-2">
        @if($notebook->conversation_id)
        <a href="{{ route('student.companion.show', $notebook->conversation_id) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 hover:bg-slate-50 text-sm text-slate-600 transition-colors">
            Open in Companion ↗
        </a>
        @endif
        <form action="{{ route('student.notebook.destroy', $notebook) }}" method="POST"
              onsubmit="return confirm('Delete this from your notebook?')">
            @csrf @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-50 text-sm text-red-600 transition-colors">
                Delete
            </button>
        </form>
    </div>
</div>

{{-- Header card --}}
<div class="card p-5 mb-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="text-xl">{{ $notebook->type_icon }}</span>
                <span class="text-xs font-semibold uppercase tracking-wide
                    {{ match($notebook->type) {
                        'notes'         => 'text-emerald-600',
                        'study_plan'    => 'text-blue-600',
                        'advanced_plan' => 'text-violet-600',
                        default         => 'text-slate-400',
                    } }}">{{ $notebook->type_label }}</span>
                @if($notebook->is_pinned)<span class="text-amber-400">📌</span>@endif
            </div>
            <h1 class="text-xl font-bold text-slate-900 mb-1">{{ $notebook->title }}</h1>
            <div class="flex flex-wrap gap-2">
                @if($notebook->subject)<span class="badge badge-blue">{{ $notebook->subject }}</span>@endif
                @if($notebook->level)<span class="badge badge-slate">{{ $notebook->level }}</span>@endif
                @if($notebook->topic)<span class="badge badge-green">{{ $notebook->topic }}</span>@endif
            </div>
        </div>
        <p class="text-xs text-slate-400">Saved {{ $notebook->created_at->format('d M Y, g:i a') }}</p>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     NOTES view
══════════════════════════════════════════════════════ --}}
@if($notebook->type === 'notes')
<div class="card p-5 md:p-8">
    <div id="notes-content" class="prose-nb" data-md="{{ $notebook->content }}"></div>
    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center gap-3">
        <button onclick="copyNotes()" class="text-xs text-slate-500 hover:text-slate-800 border border-slate-200 rounded-lg px-3 py-1.5 transition-colors">
            Copy text
        </button>
        <button onclick="printNotes()" class="text-xs text-slate-500 hover:text-slate-800 border border-slate-200 rounded-lg px-3 py-1.5 transition-colors">
            Print / PDF
        </button>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     STUDY PLAN view
══════════════════════════════════════════════════════ --}}
@elseif($notebook->type === 'study_plan')
@php $plan = $notebook->plan; @endphp
@if(isset($plan['weeks']))
<div class="space-y-4">
    @if(isset($plan['title']))
    <div class="card p-4 bg-blue-50 border-blue-200">
        <p class="font-semibold text-blue-900">{{ $plan['title'] }}</p>
        <p class="text-blue-700 text-sm">{{ $plan['subject'] ?? '' }} · {{ $plan['level'] ?? '' }}</p>
    </div>
    @endif
    @foreach($plan['weeks'] as $week)
    <div class="card overflow-hidden">
        <div class="flex items-center gap-3 px-4 py-3 bg-blue-600">
            <span class="text-white font-extrabold text-sm">Week {{ $week['week'] }}</span>
            <span class="text-blue-200 text-sm">{{ $week['theme'] ?? '' }}</span>
        </div>
        <div class="p-4 space-y-3">
            @if(!empty($week['goals']))
            <div>
                <p class="text-xs font-semibold uppercase text-slate-500 mb-1">Goals</p>
                <ul class="list-disc list-inside text-sm text-slate-700 space-y-0.5">
                    @foreach($week['goals'] as $g)<li>{{ $g }}</li>@endforeach
                </ul>
            </div>
            @endif
            @if(!empty($week['activities']))
            <div>
                <p class="text-xs font-semibold uppercase text-slate-500 mb-1">Activities</p>
                <ul class="list-disc list-inside text-sm text-slate-700 space-y-0.5">
                    @foreach($week['activities'] as $a)<li>{{ $a }}</li>@endforeach
                </ul>
            </div>
            @endif
            @if(!empty($week['resources']))
            <div>
                <p class="text-xs font-semibold uppercase text-slate-500 mb-1">Resources</p>
                <ul class="list-disc list-inside text-sm text-slate-700 space-y-0.5">
                    @foreach($week['resources'] as $r)<li>{{ $r }}</li>@endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
    @endforeach
</div>
@else
<div class="card p-5 prose-nb" data-md="{{ $notebook->content }}"></div>
@endif

{{-- ══════════════════════════════════════════════════════
     ADVANCED PLAN view
══════════════════════════════════════════════════════ --}}
@else
@php $plan = $notebook->plan; @endphp
@if(isset($plan['error']))
<div class="card p-5 text-red-600 text-sm">{{ $plan['error'] }}</div>
@elseif(isset($plan['weeks']))

{{-- Plan header --}}
<div class="card p-5 mb-6 bg-gradient-to-r from-violet-600 to-indigo-600 text-white">
    <p class="text-xs uppercase tracking-widest text-violet-200 mb-1">Advanced Study Plan</p>
    <h2 class="text-xl font-extrabold mb-1">{{ $plan['title'] ?? $notebook->title }}</h2>
    <p class="text-violet-200 text-sm">
        {{ $plan['subject'] ?? '' }} · {{ $plan['level'] ?? '' }} · {{ $plan['exam_board'] ?? 'ZIMSEC' }} · {{ count($plan['weeks']) }} weeks
    </p>
    @if(!empty($plan['overview']))
    <p class="mt-3 text-sm text-white/90">{{ $plan['overview'] }}</p>
    @endif
</div>

{{-- Exam strategy --}}
@if(!empty($plan['exam_strategy']))
<div class="card p-4 mb-6 border-amber-200 bg-amber-50" x-data="{open:false}">
    <button @click="open=!open" class="w-full text-left flex items-center justify-between">
        <p class="font-semibold text-amber-800 text-sm">🎯 Exam Strategy</p>
        <svg class="w-4 h-4 text-amber-600 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
    </button>
    <div x-show="open" x-cloak class="mt-3 space-y-3">
        @if(!empty($plan['exam_strategy']['time_management']))
        <p class="text-sm text-amber-900"><strong>Time management:</strong> {{ $plan['exam_strategy']['time_management'] }}</p>
        @endif
        @if(!empty($plan['exam_strategy']['common_exam_mistakes']))
        <div>
            <p class="text-xs font-semibold text-amber-700 uppercase mb-1">Common exam mistakes</p>
            <ul class="list-disc list-inside text-sm text-amber-900 space-y-0.5">
                @foreach($plan['exam_strategy']['common_exam_mistakes'] as $m)<li>{{ $m }}</li>@endforeach
            </ul>
        </div>
        @endif
        @if(!empty($plan['exam_strategy']['mark_scheme_tips']))
        <div>
            <p class="text-xs font-semibold text-amber-700 uppercase mb-1">Mark scheme tips</p>
            <ul class="list-disc list-inside text-sm text-amber-900 space-y-0.5">
                @foreach($plan['exam_strategy']['mark_scheme_tips'] as $t)<li>{{ $t }}</li>@endforeach
            </ul>
        </div>
        @endif
    </div>
</div>
@endif

{{-- Weeks --}}
@foreach($plan['weeks'] as $weekIdx => $week)
<div class="card overflow-hidden mb-6" x-data="{weekOpen: {{ $weekIdx === 0 ? 'true' : 'false' }}}">
    {{-- Week header --}}
    <button @click="weekOpen=!weekOpen"
            class="w-full flex items-center justify-between px-5 py-4 bg-gradient-to-r from-violet-50 to-indigo-50 border-b border-violet-100">
        <div class="flex items-center gap-3">
            <span class="w-8 h-8 rounded-full bg-violet-600 text-white text-sm font-extrabold flex items-center justify-center">
                {{ $week['week'] }}
            </span>
            <div class="text-left">
                <p class="font-bold text-slate-900 text-sm">{{ $week['theme'] ?? 'Week '.$week['week'] }}</p>
                @if(!empty($week['overview']))
                <p class="text-xs text-slate-500 mt-0.5">{{ Str::limit($week['overview'], 80) }}</p>
                @endif
            </div>
        </div>
        <svg class="w-4 h-4 text-slate-400 transition-transform" :class="weekOpen && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
    </button>

    <div x-show="weekOpen" x-cloak class="p-5 space-y-4">
        {{-- Goals + concepts --}}
        <div class="grid sm:grid-cols-2 gap-4">
            @if(!empty($week['goals']))
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">✅ Goals</p>
                <ul class="space-y-1">
                    @foreach($week['goals'] as $g)
                    <li class="flex items-start gap-1.5 text-sm text-slate-700">
                        <span class="text-emerald-500 mt-0.5">•</span>{{ $g }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
            @if(!empty($week['key_concepts']))
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">🔑 Key Concepts</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($week['key_concepts'] as $c)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-violet-100 text-violet-700">{{ $c }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        @if(!empty($week['common_mistakes']))
        <div class="bg-red-50 border border-red-100 rounded-xl p-3">
            <p class="text-xs font-bold text-red-600 uppercase tracking-wide mb-1.5">⚠ Common Mistakes This Week</p>
            <ul class="space-y-0.5">
                @foreach($week['common_mistakes'] as $m)
                <li class="text-sm text-red-800 flex items-start gap-1.5"><span>•</span>{{ $m }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Daily breakdown --}}
        @if(!empty($week['days']))
        <div>
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-3">📅 Daily Breakdown</p>
            <div class="space-y-3">
                @foreach($week['days'] as $day)
                <div class="border border-slate-200 rounded-xl overflow-hidden" x-data="{dayOpen:false}">
                    <button @click="dayOpen=!dayOpen"
                            class="w-full px-4 py-3 flex items-center justify-between text-left bg-slate-50 hover:bg-slate-100 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-lg bg-blue-600 text-white text-xs font-bold flex items-center justify-center shrink-0">
                                {{ substr($day['day'] ?? '', 0, 2) }}
                            </span>
                            <div>
                                <span class="text-sm font-semibold text-slate-800">{{ $day['day'] ?? '' }}</span>
                                @if(!empty($day['focus']))
                                <span class="text-xs text-slate-500 ml-1.5">— {{ $day['focus'] }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if(!empty($day['duration_minutes']))
                            <span class="text-xs text-slate-400">{{ $day['duration_minutes'] }}min</span>
                            @endif
                            <svg class="w-4 h-4 text-slate-400 transition-transform" :class="dayOpen && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                        </div>
                    </button>

                    <div x-show="dayOpen" x-cloak class="p-4 space-y-4 border-t border-slate-100">
                        @if(!empty($day['content_summary']))
                        <p class="text-sm text-slate-600 bg-blue-50 rounded-lg p-3">{{ $day['content_summary'] }}</p>
                        @endif

                        @if(!empty($day['objectives']))
                        <div>
                            <p class="text-xs font-bold uppercase text-slate-500 mb-1.5">Objectives</p>
                            <ul class="list-disc list-inside text-sm text-slate-700 space-y-0.5">
                                @foreach($day['objectives'] as $o)<li>{{ $o }}</li>@endforeach
                            </ul>
                        </div>
                        @endif

                        {{-- Worked Examples --}}
                        @if(!empty($day['worked_examples']))
                        <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4">
                            <p class="text-xs font-bold uppercase text-emerald-700 mb-3">✏️ Worked Examples</p>
                            <div class="space-y-4">
                                @foreach($day['worked_examples'] as $i => $eg)
                                <div>
                                    <p class="text-sm font-semibold text-slate-800 mb-1">
                                        Q{{ $i + 1 }}@if(!empty($eg['marks'])) <span class="text-emerald-600 text-xs font-normal">[{{ $eg['marks'] }} marks]</span>@endif
                                    </p>
                                    <p class="text-sm text-slate-700 mb-2 bg-white rounded-lg px-3 py-2 border border-emerald-100">{{ $eg['question'] }}</p>
                                    <div class="bg-emerald-100 rounded-lg px-3 py-2">
                                        <p class="text-xs font-semibold text-emerald-700 mb-0.5">Solution:</p>
                                        <p class="text-sm text-emerald-900 whitespace-pre-line">{{ $eg['solution'] }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Practice Questions --}}
                        @if(!empty($day['practice_questions']))
                        <div class="bg-amber-50 border border-amber-100 rounded-xl p-4">
                            <p class="text-xs font-bold uppercase text-amber-700 mb-3">📋 Practice Questions</p>
                            <div class="space-y-3">
                                @foreach($day['practice_questions'] as $i => $pq)
                                <div x-data="{showHint:false}">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex-1">
                                            <span class="inline-block text-xs font-bold bg-amber-200 text-amber-800 rounded px-1.5 mr-1">
                                                {{ strtoupper($pq['difficulty'] ?? 'medium') }}
                                            </span>
                                            <span class="text-sm text-slate-800">{{ $pq['question'] }}</span>
                                        </div>
                                        @if(!empty($pq['hint']))
                                        <button @click="showHint=!showHint"
                                                class="text-xs text-amber-600 border border-amber-300 rounded-lg px-2 py-0.5 hover:bg-amber-100 transition-colors shrink-0">
                                            Hint
                                        </button>
                                        @endif
                                    </div>
                                    @if(!empty($pq['hint']))
                                    <div x-show="showHint" x-cloak class="mt-1.5 text-xs text-amber-700 bg-amber-100 rounded-lg px-3 py-1.5">
                                        💡 {{ $pq['hint'] }}
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Textbook References --}}
                        @if(!empty($day['textbook_refs']))
                        <div>
                            <p class="text-xs font-bold uppercase text-slate-500 mb-2">📚 Textbook References</p>
                            <div class="space-y-2">
                                @foreach($day['textbook_refs'] as $ref)
                                <div class="flex items-start gap-2 bg-slate-50 rounded-lg px-3 py-2 border border-slate-200 text-sm">
                                    <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/></svg>
                                    <div>
                                        <span class="font-semibold text-slate-700">{{ $ref['book'] }}</span>
                                        @if(!empty($ref['chapter'])) · {{ $ref['chapter'] }}@endif
                                        @if(!empty($ref['pages'])) · <span class="text-blue-600">{{ $ref['pages'] }}</span>@endif
                                        @if(!empty($ref['topic_in_book']))<p class="text-xs text-slate-400">{{ $ref['topic_in_book'] }}</p>@endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- YouTube Results --}}
                        @if(!empty($day['youtube_results']))
                        <div>
                            <p class="text-xs font-bold uppercase text-slate-500 mb-2">🎬 Recommended Videos</p>
                            <div class="space-y-3">
                                @foreach($day['youtube_results'] as $yqGroup)
                                @if(!empty($yqGroup['videos']))
                                <div>
                                    <p class="text-xs text-slate-400 mb-1.5">{{ $yqGroup['purpose'] ?? $yqGroup['query'] }}</p>
                                    <div class="grid sm:grid-cols-2 gap-2">
                                        @foreach($yqGroup['videos'] as $vid)
                                        <a href="{{ $vid['url'] }}" target="_blank" rel="noopener"
                                           class="flex items-start gap-2 bg-white border border-slate-200 rounded-xl overflow-hidden hover:border-red-300 hover:shadow-sm transition-all group">
                                            @if(!empty($vid['thumbnail']))
                                            <img src="{{ $vid['thumbnail'] }}" alt=""
                                                 class="w-24 h-16 object-cover shrink-0 group-hover:opacity-90 transition-opacity">
                                            @else
                                            <div class="w-24 h-16 bg-red-100 flex items-center justify-center shrink-0">
                                                <span class="text-red-500 text-xl">▶</span>
                                            </div>
                                            @endif
                                            <div class="p-1.5 min-w-0">
                                                <p class="text-xs font-semibold text-slate-800 line-clamp-2 group-hover:text-red-600 transition-colors">{{ $vid['title'] }}</p>
                                                <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $vid['channel'] }}</p>
                                            </div>
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                                @endforeach
                            </div>
                        </div>
                        @endif

                    </div>{{-- /day content --}}
                </div>{{-- /day card --}}
                @endforeach
            </div>
        </div>
        @endif

        {{-- Weekly self-assessment --}}
        @if(!empty($week['weekly_self_assessment']))
        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-3">
            <p class="text-xs font-bold text-indigo-700 uppercase mb-2">🧠 Weekly Self-Assessment</p>
            <ul class="space-y-1">
                @foreach($week['weekly_self_assessment'] as $q)
                <li class="flex items-start gap-1.5 text-sm text-indigo-900"><span>?</span>{{ $q }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(!empty($week['revision_tips']))
        <div>
            <p class="text-xs font-bold uppercase text-slate-500 mb-1.5">💡 Revision Tips</p>
            <ul class="list-disc list-inside text-sm text-slate-600 space-y-0.5">
                @foreach($week['revision_tips'] as $t)<li>{{ $t }}</li>@endforeach
            </ul>
        </div>
        @endif

    </div>{{-- /week content --}}
</div>{{-- /week card --}}
@endforeach

@endif
@endif

{{-- Render markdown for notes --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Render markdown for notes
    const el = document.getElementById('notes-content');
    if (el && el.dataset.md) {
        const html = marked.parse(el.dataset.md, { breaks: true, gfm: true });
        el.innerHTML = typeof DOMPurify !== 'undefined' ? DOMPurify.sanitize(html) : html;
        el.removeAttribute('data-md');
    }
    // Render LaTeX math
    if (window.renderMathInElement) {
        renderMathInElement(document.body, {
            delimiters: [
                {left: '\\(', right: '\\)', display: false},
                {left: '\\[', right: '\\]', display: true},
            ],
            throwOnError: false,
        });
    }
});

function copyNotes() {
    const el = document.getElementById('notes-content');
    if (el) navigator.clipboard.writeText(el.innerText);
}

function printNotes() {
    window.print();
}
</script>

</x-app-layout>
