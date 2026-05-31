<x-app-layout>
    <x-slot name="title">AI Tools</x-slot>

    @push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3/dist/purify.min.js"></script>
    <style>
        .prose-ai { line-height: 1.7; }
        .prose-ai h1,.prose-ai h2,.prose-ai h3 { font-weight: 700; margin-top: 1rem; margin-bottom: 0.4rem; }
        .prose-ai p { margin-bottom: 0.75rem; }
        .prose-ai ul,.prose-ai ol { padding-left: 1.4rem; margin-bottom: 0.75rem; }
        .prose-ai li { margin-bottom: 0.25rem; }
        .prose-ai code { background: #f1f5f9; padding: 0.1em 0.4em; border-radius: 4px; font-size: 0.85em; }
        .prose-ai pre { background: #f1f5f9; padding: 1rem; border-radius: 8px; overflow-x: auto; margin-bottom: 1rem; }
        .prose-ai strong { font-weight: 700; }
        .tab-btn { border-bottom: 2px solid transparent; }
        .tab-btn.active { border-bottom-color: #7c3aed; color: #7c3aed; }
    </style>
    @endpush

    <div class="page-header flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="page-title">AI Tools</h1>
            <p class="page-subtitle">Test AI models, generate content, and monitor usage across EduBridge</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold px-4 py-2 rounded-lg transition">
            ← Dashboard
        </a>
    </div>

    {{-- Stats row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4">
            <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Total Conversations</p>
            <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['total_conversations']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4">
            <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">Total Messages</p>
            <p class="text-2xl font-bold text-slate-800">{{ number_format($stats['total_messages']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4">
            <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">This Week (convos)</p>
            <p class="text-2xl font-bold text-violet-700">{{ number_format($stats['week_conversations']) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-4">
            <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mb-1">This Week (msgs)</p>
            <p class="text-2xl font-bold text-violet-700">{{ number_format($stats['week_messages']) }}</p>
        </div>
    </div>

    {{-- Activity chart + model stats --}}
    <div class="grid lg:grid-cols-3 gap-6 mb-6">
        <div class="card lg:col-span-2 p-6">
            <h2 class="section-title mb-4">AI Activity — Last 14 Days</h2>
            <canvas id="activityChart" height="80"></canvas>
        </div>
        <div class="card p-6">
            <h2 class="section-title mb-4">Model Breakdown (30d)</h2>
            @php
                $mc = ['chiedza'=>['bg-green-500','text-green-700'],'gpt'=>['bg-blue-500','text-blue-700']];
                $total = array_sum($modelStats) ?: 1;
            @endphp
            @forelse($modelStats as $m => $c)
            @php $col = $mc[$m] ?? ['bg-slate-400','text-slate-700']; @endphp
            <div class="mb-4">
                <div class="flex justify-between mb-1">
                    <span class="text-xs font-semibold {{ $col[1] }} flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full {{ $col[0] }}"></span>{{ ucfirst($m) }}
                    </span>
                    <span class="text-xs font-bold text-slate-700">{{ number_format($c) }}</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2">
                    <div class="{{ $col[0] }} h-2 rounded-full" style="width:{{ round($c/$total*100) }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-sm text-slate-400 py-4 text-center">No data yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Tool Tabs --}}
    <div class="card" x-data="aiTools()">
        {{-- Tab nav --}}
        <div class="border-b border-slate-100 px-6 flex gap-6 overflow-x-auto">
            <button @click="tab='playground'" :class="tab==='playground' ? 'active' : ''" class="tab-btn text-sm font-semibold text-slate-500 hover:text-slate-700 py-4 whitespace-nowrap">
                🤖 AI Playground
            </button>
            <button @click="tab='broadcast'" :class="tab==='broadcast' ? 'active' : ''" class="tab-btn text-sm font-semibold text-slate-500 hover:text-slate-700 py-4 whitespace-nowrap">
                📢 Announcement Drafter
            </button>
            <button @click="tab='course'" :class="tab==='course' ? 'active' : ''" class="tab-btn text-sm font-semibold text-slate-500 hover:text-slate-700 py-4 whitespace-nowrap">
                📚 Course Description
            </button>
            <button @click="tab='quiz'" :class="tab==='quiz' ? 'active' : ''" class="tab-btn text-sm font-semibold text-slate-500 hover:text-slate-700 py-4 whitespace-nowrap">
                ✏️ Quiz Generator
            </button>
            <button @click="tab='activity'" :class="tab==='activity' ? 'active' : ''" class="tab-btn text-sm font-semibold text-slate-500 hover:text-slate-700 py-4 whitespace-nowrap">
                📊 Activity Log
            </button>
        </div>

        <div class="p-6">

            {{-- ===================== PLAYGROUND ===================== --}}
            <div x-show="tab==='playground'">
                <h3 class="text-base font-bold text-slate-800 mb-4">Test any AI model with a custom prompt</h3>
                <div class="grid lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Model</label>
                            <div class="flex gap-2">
                                <button @click="playForm.model='gpt'" :class="playForm.model==='gpt' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600'" class="px-4 py-2 rounded-lg text-sm font-semibold transition">GPT-4o</button>
                                <button @click="playForm.model='chiedza'" :class="playForm.model==='chiedza' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600'" class="px-4 py-2 rounded-lg text-sm font-semibold transition">Chiedza</button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">System Prompt <span class="font-normal text-slate-400">(optional)</span></label>
                            <textarea x-model="playForm.system" rows="3"
                                placeholder="You are an AI assistant for EduBridge…"
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-violet-500 outline-none resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Message</label>
                            <textarea x-model="playForm.message" rows="5"
                                placeholder="Enter your prompt here…"
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-violet-500 outline-none resize-none"></textarea>
                        </div>
                        <button @click="testPrompt()" :disabled="playLoading || !playForm.message.trim()"
                            class="w-full bg-violet-600 hover:bg-violet-700 text-white font-semibold py-3 rounded-xl transition disabled:opacity-50">
                            <span x-show="!playLoading">Send to <span x-text="playForm.model.toUpperCase()"></span> →</span>
                            <span x-show="playLoading">Generating…</span>
                        </button>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-2">Response <span class="font-normal text-slate-400" x-show="playModel" x-text="'— '+playModel"></span></label>
                        <div x-show="!playResult && !playError" class="h-64 flex items-center justify-center border-2 border-dashed border-slate-200 rounded-xl text-slate-400 text-sm">
                            Response will appear here
                        </div>
                        <div x-show="playError" class="bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-600" x-text="playError"></div>
                        <div x-show="playResult" class="border border-slate-200 rounded-xl p-4 text-sm text-slate-700 prose-ai overflow-y-auto max-h-80" x-html="playResult"></div>
                    </div>
                </div>
            </div>

            {{-- ===================== BROADCAST ===================== --}}
            <div x-show="tab==='broadcast'">
                <h3 class="text-base font-bold text-slate-800 mb-1">Announcement Drafter</h3>
                <p class="text-sm text-slate-500 mb-5">Use AI to draft a platform announcement. Review, edit, then copy to use in your system.</p>
                <div class="grid lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Topic / Subject</label>
                            <textarea x-model="broadForm.topic" rows="3"
                                placeholder="e.g. New maths courses available, Exam tips for O-Level students, Holiday schedule…"
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-violet-500 outline-none resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Tone</label>
                            <div class="flex gap-2">
                                <template x-for="t in ['formal','friendly','motivational']">
                                    <button @click="broadForm.tone=t" :class="broadForm.tone===t ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600'" class="px-4 py-2 rounded-lg text-sm font-semibold capitalize transition" x-text="t"></button>
                                </template>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Model</label>
                            <div class="flex gap-2">
                                <button @click="broadForm.model='gpt'" :class="broadForm.model==='gpt' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600'" class="px-4 py-2 rounded-lg text-sm font-semibold transition">GPT-4o</button>
                                <button @click="broadForm.model='chiedza'" :class="broadForm.model==='chiedza' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600'" class="px-4 py-2 rounded-lg text-sm font-semibold transition">Chiedza</button>
                            </div>
                        </div>
                        <button @click="broadcastDraft()" :disabled="broadLoading || !broadForm.topic.trim()"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl transition disabled:opacity-50">
                            <span x-show="!broadLoading">Draft Announcement →</span>
                            <span x-show="broadLoading">Drafting…</span>
                        </button>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-semibold text-slate-500 uppercase">Draft</label>
                            <button x-show="broadResult" @click="copyText(broadResult)" class="text-xs text-indigo-600 hover:underline">Copy</button>
                        </div>
                        <div x-show="!broadResult && !broadError" class="h-64 flex items-center justify-center border-2 border-dashed border-slate-200 rounded-xl text-slate-400 text-sm">
                            Draft will appear here
                        </div>
                        <div x-show="broadError" class="bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-600" x-text="broadError"></div>
                        <div x-show="broadResult" class="border border-slate-200 rounded-xl p-4 text-sm text-slate-700 prose-ai overflow-y-auto max-h-80" x-html="broadResult"></div>
                    </div>
                </div>
            </div>

            {{-- ===================== COURSE DESCRIPTION ===================== --}}
            <div x-show="tab==='course'">
                <h3 class="text-base font-bold text-slate-800 mb-1">Course Description Generator</h3>
                <p class="text-sm text-slate-500 mb-5">Generate a compelling course description with learning outcomes.</p>
                <div class="grid lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Course Title</label>
                            <input x-model="courseForm.title" type="text" placeholder="e.g. Advanced Mathematics for O-Level"
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Subject</label>
                            <input x-model="courseForm.subject" type="text" placeholder="e.g. Mathematics"
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Level</label>
                            <input x-model="courseForm.level" type="text" placeholder="e.g. O-Level / Grade 10"
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Model</label>
                            <div class="flex gap-2">
                                <button @click="courseForm.model='gpt'" :class="courseForm.model==='gpt' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600'" class="px-4 py-2 rounded-lg text-sm font-semibold transition">GPT-4o</button>
                                <button @click="courseForm.model='chiedza'" :class="courseForm.model==='chiedza' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600'" class="px-4 py-2 rounded-lg text-sm font-semibold transition">Chiedza</button>
                            </div>
                        </div>
                        <button @click="generateCourse()" :disabled="courseLoading || !courseForm.title.trim()"
                            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-xl transition disabled:opacity-50">
                            <span x-show="!courseLoading">Generate Description →</span>
                            <span x-show="courseLoading">Generating…</span>
                        </button>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-semibold text-slate-500 uppercase">Output</label>
                            <button x-show="courseResult" @click="copyText(courseResult)" class="text-xs text-emerald-600 hover:underline">Copy</button>
                        </div>
                        <div x-show="!courseResult && !courseError" class="h-64 flex items-center justify-center border-2 border-dashed border-slate-200 rounded-xl text-slate-400 text-sm">
                            Description will appear here
                        </div>
                        <div x-show="courseError" class="bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-600" x-text="courseError"></div>
                        <div x-show="courseResult" class="border border-slate-200 rounded-xl p-4 text-sm text-slate-700 prose-ai overflow-y-auto max-h-80" x-html="courseResult"></div>
                    </div>
                </div>
            </div>

            {{-- ===================== QUIZ GENERATOR ===================== --}}
            <div x-show="tab==='quiz'">
                <h3 class="text-base font-bold text-slate-800 mb-1">Quiz Generator</h3>
                <p class="text-sm text-slate-500 mb-5">Generate multiple-choice questions ready for import or use.</p>
                <div class="grid lg:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Topic</label>
                            <input x-model="quizForm.topic" type="text" placeholder="e.g. Photosynthesis, Quadratic equations…"
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Level</label>
                            <input x-model="quizForm.level" type="text" placeholder="e.g. A-Level, Primary 6…"
                                class="w-full border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Number of Questions</label>
                            <input x-model.number="quizForm.questions" type="number" min="3" max="15" value="5"
                                class="w-32 border border-slate-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Model</label>
                            <div class="flex gap-2">
                                <button @click="quizForm.model='gpt'" :class="quizForm.model==='gpt' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600'" class="px-4 py-2 rounded-lg text-sm font-semibold transition">GPT-4o</button>
                                <button @click="quizForm.model='chiedza'" :class="quizForm.model==='chiedza' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600'" class="px-4 py-2 rounded-lg text-sm font-semibold transition">Chiedza</button>
                            </div>
                        </div>
                        <button @click="generateQuiz()" :disabled="quizLoading || !quizForm.topic.trim()"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition disabled:opacity-50">
                            <span x-show="!quizLoading">Generate Quiz →</span>
                            <span x-show="quizLoading">Generating…</span>
                        </button>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-semibold text-slate-500 uppercase">Questions</label>
                            <button x-show="quizQuestions.length" @click="copyJson()" class="text-xs text-blue-600 hover:underline">Copy JSON</button>
                        </div>
                        <div x-show="!quizQuestions.length && !quizError" class="h-64 flex items-center justify-center border-2 border-dashed border-slate-200 rounded-xl text-slate-400 text-sm">
                            Questions will appear here
                        </div>
                        <div x-show="quizError" class="bg-red-50 border border-red-200 rounded-xl p-4 text-sm text-red-600" x-text="quizError"></div>
                        <div x-show="quizQuestions.length" class="space-y-3 overflow-y-auto max-h-96">
                            <template x-for="(q, i) in quizQuestions" :key="i">
                                <div class="border border-slate-200 rounded-xl p-4">
                                    <p class="font-semibold text-slate-800 text-sm mb-2" x-text="(i+1)+'. '+q.question"></p>
                                    <div class="space-y-1">
                                        <template x-for="(opt, j) in q.options" :key="j">
                                            <p class="text-xs px-3 py-1.5 rounded-lg"
                                               :class="opt === q.answer ? 'bg-emerald-100 text-emerald-700 font-semibold' : 'text-slate-600'"
                                               x-text="opt"></p>
                                        </template>
                                    </div>
                                    <p x-show="q.explanation" class="text-xs text-slate-400 mt-2 italic" x-text="'💡 '+q.explanation"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===================== ACTIVITY LOG ===================== --}}
            <div x-show="tab==='activity'">
                <h3 class="text-base font-bold text-slate-800 mb-4">Recent AI Conversations</h3>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead><tr><th>Student</th><th>Subject</th><th>Model</th><th>Messages</th><th>Last Active</th></tr></thead>
                        <tbody>
                            @forelse($recentConversations as $conv)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-violet-100 text-violet-700 text-xs font-bold flex items-center justify-center uppercase flex-shrink-0">{{ substr($conv->user->name ?? '?', 0, 1) }}</div>
                                        <span class="text-sm font-medium text-slate-800">{{ $conv->user->name ?? '—' }}</span>
                                    </div>
                                </td>
                                <td class="text-xs text-slate-600">{{ $conv->subject ?: '—' }}</td>
                                <td>
                                    @php $m = $conv->preferred_model ?? 'auto'; @endphp
                                    <span class="{{ match($m) { 'gpt' => 'badge-blue', 'chiedza' => 'badge-green', default => 'badge-amber' } }}">{{ $m }}</span>
                                </td>
                                <td class="font-semibold text-slate-700 text-center">{{ $conv->messages_count ?? '—' }}</td>
                                <td class="text-xs text-slate-400">{{ $conv->updated_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-8 text-slate-400 text-sm">No conversations yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Top AI users --}}
                @if($topAiUsers->count())
                <h3 class="text-base font-bold text-slate-800 mt-6 mb-3">Most Active AI Students</h3>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                    @foreach($topAiUsers as $i => $item)
                    <div class="bg-slate-50 rounded-xl p-3 text-center">
                        <div class="w-10 h-10 rounded-full bg-violet-100 text-violet-700 font-bold text-sm flex items-center justify-center uppercase mx-auto mb-2">{{ substr($item->user->name ?? '?', 0, 1) }}</div>
                        <p class="text-xs font-semibold text-slate-700 truncate">{{ $item->user->name ?? '—' }}</p>
                        <p class="text-lg font-bold text-violet-700">{{ $item->conv_count }}</p>
                        <p class="text-xs text-slate-400">conversations</p>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

        </div>{{-- /p-6 --}}
    </div>{{-- /card --}}

    @push('scripts')
    <script>
    new Chart(document.getElementById('activityChart'), {
        type: 'line',
        data: {
            labels: @json($activityLabels),
            datasets: [{
                label: 'AI Messages',
                data: @json($activityData),
                borderColor: 'rgba(124,58,237,1)',
                backgroundColor: 'rgba(124,58,237,0.08)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: 'rgba(124,58,237,1)',
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } },
                x: { grid: { display: false } }
            }
        }
    });

    function aiTools() {
        return {
            tab: 'playground',
            // Playground
            playForm:   { model: 'gpt', system: '', message: '' },
            playLoading: false, playResult: '', playError: '', playModel: '',
            // Broadcast
            broadForm:   { topic: '', tone: 'friendly', model: 'gpt' },
            broadLoading: false, broadResult: '', broadError: '',
            // Course
            courseForm:   { title: '', subject: '', level: '', model: 'gpt' },
            courseLoading: false, courseResult: '', courseError: '',
            // Quiz
            quizForm:     { topic: '', level: '', questions: 5, model: 'gpt' },
            quizLoading: false, quizQuestions: [], quizRaw: '', quizError: '',

            renderMd(text) {
                if (!text) return '';
                return DOMPurify.sanitize(marked.parse(text));
            },
            copyText(html) {
                const tmp = document.createElement('div');
                tmp.innerHTML = html;
                navigator.clipboard.writeText(tmp.innerText).then(() => alert('Copied!'));
            },
            copyJson() {
                navigator.clipboard.writeText(JSON.stringify(this.quizQuestions, null, 2)).then(() => alert('JSON copied!'));
            },

            async testPrompt() {
                this.playLoading = true; this.playResult = ''; this.playError = '';
                try {
                    const r = await fetch('{{ route("admin.ai-tools.test") }}', {
                        method: 'POST',
                        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}', 'Accept':'application/json' },
                        body: JSON.stringify(this.playForm),
                    });
                    const d = await r.json();
                    if (d.error) { this.playError = d.error; } else {
                        this.playResult = this.renderMd(d.response);
                        this.playModel  = d.model;
                    }
                } catch(e) { this.playError = e.message; } finally { this.playLoading = false; }
            },

            async broadcastDraft() {
                this.broadLoading = true; this.broadResult = ''; this.broadError = '';
                try {
                    const r = await fetch('{{ route("admin.ai-tools.broadcast") }}', {
                        method: 'POST',
                        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}', 'Accept':'application/json' },
                        body: JSON.stringify(this.broadForm),
                    });
                    const d = await r.json();
                    if (d.error) { this.broadError = d.error; } else { this.broadResult = this.renderMd(d.draft); }
                } catch(e) { this.broadError = e.message; } finally { this.broadLoading = false; }
            },

            async generateCourse() {
                this.courseLoading = true; this.courseResult = ''; this.courseError = '';
                try {
                    const r = await fetch('{{ route("admin.ai-tools.course-desc") }}', {
                        method: 'POST',
                        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}', 'Accept':'application/json' },
                        body: JSON.stringify(this.courseForm),
                    });
                    const d = await r.json();
                    if (d.error) { this.courseError = d.error; } else { this.courseResult = this.renderMd(d.description); }
                } catch(e) { this.courseError = e.message; } finally { this.courseLoading = false; }
            },

            async generateQuiz() {
                this.quizLoading = true; this.quizQuestions = []; this.quizError = '';
                try {
                    const r = await fetch('{{ route("admin.ai-tools.quiz") }}', {
                        method: 'POST',
                        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}', 'Accept':'application/json' },
                        body: JSON.stringify(this.quizForm),
                    });
                    const d = await r.json();
                    if (d.error) { this.quizError = d.error; }
                    else if (!d.questions || !d.questions.length) { this.quizError = 'Could not parse questions. Raw: ' + (d.raw || ''); }
                    else { this.quizQuestions = d.questions; }
                } catch(e) { this.quizError = e.message; } finally { this.quizLoading = false; }
            },
        };
    }
    </script>
    @endpush
</x-app-layout>
