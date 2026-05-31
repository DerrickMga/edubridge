<x-app-layout>
    <x-slot name="title">Teacher AI Tools</x-slot>

    <div class="max-w-5xl mx-auto px-4 py-8" x-data="teacherAiTools()">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">🤖 Teacher AI Tools</h1>
                <p class="text-sm text-gray-500 mt-1">Powered by Chiedza (Azure AI) &amp; GPT-4o</p>
            </div>
            <a href="{{ route('teacher.dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Dashboard</a>
        </div>

        {{-- Tool tabs --}}
        <div class="flex gap-2 border-b border-gray-200 mb-6">
            <button @click="tab='summarise'" :class="tab==='summarise' ? 'border-b-2 border-green-600 text-green-700 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2 text-sm">📄 Lesson Summariser</button>
            <button @click="tab='notes'"     :class="tab==='notes'     ? 'border-b-2 border-green-600 text-green-700 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2 text-sm">📝 Notes Generator</button>
            <button @click="tab='plan'"      :class="tab==='plan'      ? 'border-b-2 border-green-600 text-green-700 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2 text-sm">📅 Study Plan Builder</button>
        </div>

        {{-- ===================== LESSON SUMMARISER ===================== --}}
        <div x-show="tab === 'summarise'">
            <div class="bg-white rounded-xl border border-gray-200 p-6 max-w-3xl">
                <h2 class="font-semibold text-gray-900 mb-4">Summarise a Lesson</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lesson Content / Transcript</label>
                        <textarea x-model="summariseForm.content" rows="8"
                                  placeholder="Paste your lesson notes, transcript, or topic description here…"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject (optional)</label>
                        <input x-model="summariseForm.subject" type="text" placeholder="e.g. Biology"
                               class="w-64 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                    <button @click="runSummarise"
                            :disabled="summariseLoading || !summariseForm.content.trim()"
                            class="bg-green-700 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-green-800 transition disabled:opacity-50 text-sm">
                        <span x-show="!summariseLoading">Summarise Lesson</span>
                        <span x-show="summariseLoading">Summarising…</span>
                    </button>
                </div>
            </div>

            <template x-if="summariseResult">
                <div class="mt-6 max-w-3xl space-y-4">
                    <div class="bg-white border border-gray-200 rounded-xl p-5">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-bold text-lg text-gray-900" x-text="summariseResult.title"></h3>
                            <button @click="copyToClipboard(JSON.stringify(summariseResult, null, 2))" class="text-xs text-green-600 hover:underline ml-4 shrink-0">Copy</button>
                        </div>
                        <p class="text-sm text-gray-700 whitespace-pre-wrap" x-text="summariseResult.summary"></p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white border border-gray-200 rounded-xl p-4">
                            <h4 class="font-semibold text-sm text-gray-900 mb-2">Key Points</h4>
                            <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                                <template x-for="p in (summariseResult.key_points || [])"><li x-text="p"></li></template>
                            </ul>
                        </div>
                        <div class="bg-white border border-gray-200 rounded-xl p-4">
                            <h4 class="font-semibold text-sm text-gray-900 mb-2">Vocabulary</h4>
                            <div class="space-y-1.5">
                                <template x-for="v in (summariseResult.vocabulary || [])">
                                    <div class="text-sm"><span class="font-medium text-gray-900" x-text="v.term"></span>: <span class="text-gray-600" x-text="v.definition"></span></div>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                        <h4 class="font-semibold text-sm text-yellow-800 mb-2">Homework Suggestions</h4>
                        <ul class="list-disc list-inside text-sm text-yellow-900 space-y-1">
                            <template x-for="h in (summariseResult.homework_suggestions || [])"><li x-text="h"></li></template>
                        </ul>
                    </div>
                </div>
            </template>
        </div>

        {{-- ===================== NOTES GENERATOR ===================== --}}
        <div x-show="tab === 'notes'">
            <div class="bg-white rounded-xl border border-gray-200 p-6 max-w-3xl">
                <h2 class="font-semibold text-gray-900 mb-4">Generate Topic Notes</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Topic</label>
                        <input x-model="notesForm.topic" type="text" placeholder="e.g. Cell division (Mitosis vs Meiosis)"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                            <input x-model="notesForm.subject" type="text" placeholder="e.g. Biology"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Level</label>
                            <select x-model="notesForm.level" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                                <option value="">Any</option>
                                <option>O-Level</option>
                                <option>A-Level</option>
                                <option>Primary</option>
                                <option>University</option>
                            </select>
                        </div>
                    </div>
                    <button @click="runNotes"
                            :disabled="notesLoading || !notesForm.topic.trim()"
                            class="bg-green-700 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-green-800 transition disabled:opacity-50 text-sm">
                        <span x-show="!notesLoading">Generate Notes</span>
                        <span x-show="notesLoading">Generating…</span>
                    </button>
                </div>
            </div>
            <div x-show="notesResult" class="mt-6 bg-white rounded-xl border border-gray-200 p-5 max-w-3xl">
                <div class="flex justify-between mb-3">
                    <h3 class="font-semibold text-gray-900">Generated Notes</h3>
                    <button @click="copyToClipboard(notesResult)" class="text-xs text-green-600 hover:underline">Copy</button>
                </div>
                <pre class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed font-sans" x-text="notesResult"></pre>
            </div>
        </div>

        {{-- ===================== STUDY PLAN ===================== --}}
        <div x-show="tab === 'plan'">
            <div class="bg-white rounded-xl border border-gray-200 p-6 max-w-3xl">
                <h2 class="font-semibold text-gray-900 mb-4">Build a Study Plan</h2>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                            <input x-model="planForm.subject" type="text" placeholder="e.g. Pure Mathematics"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Level</label>
                            <select x-model="planForm.level" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="">Select…</option>
                                <option>O-Level</option>
                                <option>A-Level</option>
                                <option>Primary</option>
                                <option>University</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Weeks</label>
                        <input x-model.number="planForm.weeks" type="number" min="1" max="16"
                               class="w-24 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Topics <span class="text-gray-400">(comma-separated)</span></label>
                        <input x-model="planForm.topics" type="text" placeholder="Algebra, Trigonometry, Statistics, Calculus"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <button @click="runPlan"
                            :disabled="planLoading || !planForm.subject.trim()"
                            class="bg-blue-600 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-50 text-sm">
                        <span x-show="!planLoading">Generate Study Plan</span>
                        <span x-show="planLoading">Generating…</span>
                    </button>
                </div>
            </div>
            <template x-if="planResult && planResult.weeks">
                <div class="mt-6 max-w-3xl space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-gray-900 text-lg" x-text="planResult.title"></h3>
                        <button @click="copyToClipboard(JSON.stringify(planResult, null, 2))" class="text-xs text-blue-600 hover:underline">Copy JSON</button>
                    </div>
                    <template x-for="week in planResult.weeks" :key="week.week">
                        <div class="bg-white border border-gray-200 rounded-xl p-4">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-0.5 rounded" x-text="'Week ' + week.week"></span>
                                <span class="font-semibold text-gray-800 text-sm" x-text="week.theme"></span>
                            </div>
                            <div class="text-xs text-gray-500 mb-1 font-medium uppercase tracking-wide">Goals</div>
                            <ul class="list-disc list-inside text-sm text-gray-700 mb-2">
                                <template x-for="g in week.goals"><li x-text="g"></li></template>
                            </ul>
                            <div class="text-xs text-gray-500 mb-1 font-medium uppercase tracking-wide">Activities</div>
                            <ul class="list-disc list-inside text-sm text-gray-700">
                                <template x-for="a in week.activities"><li x-text="a"></li></template>
                            </ul>
                        </div>
                    </template>
                </div>
            </template>
        </div>

    </div>{{-- /max-w --}}

<script>
function teacherAiTools() {
    return {
        tab: 'summarise',
        summariseLoading: false, summariseResult: null,
        summariseForm: { content: '', subject: '' },
        notesLoading: false,    notesResult: '',
        notesForm: { topic: '', subject: '', level: '' },
        planLoading: false,     planResult: null,
        planForm: { subject: '', level: '', weeks: 8, topics: '' },

        async runSummarise() {
            if (!this.summariseForm.content.trim() || this.summariseLoading) return;
            this.summariseLoading = true; this.summariseResult = null;
            try {
                const res = await fetch('{{ route("teacher.ai-tools.summarise") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify(this.summariseForm),
                });
                const data = await res.json();
                this.summariseResult = data.summary ?? {};
            } catch(e) { alert('Error: ' + e.message); }
            finally { this.summariseLoading = false; }
        },

        async runNotes() {
            if (!this.notesForm.topic.trim() || this.notesLoading) return;
            this.notesLoading = true; this.notesResult = '';
            try {
                const res = await fetch('{{ route("teacher.ai-tools.notes") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify(this.notesForm),
                });
                const data = await res.json();
                this.notesResult = data.notes ?? '';
            } catch(e) { alert('Error: ' + e.message); }
            finally { this.notesLoading = false; }
        },

        async runPlan() {
            if (!this.planForm.subject.trim() || this.planLoading) return;
            this.planLoading = true; this.planResult = null;
            try {
                const res = await fetch('{{ route("teacher.ai-tools.study-plan") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify(this.planForm),
                });
                const data = await res.json();
                this.planResult = data.plan ?? {};
            } catch(e) { alert('Error: ' + e.message); }
            finally { this.planLoading = false; }
        },

        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => alert('Copied to clipboard!'));
        },
    };
}
</script>
</x-app-layout>
