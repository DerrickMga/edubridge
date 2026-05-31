<x-app-layout>
    <x-slot name="title">Chat with your AI Companion</x-slot>
    @push('head')
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3/dist/purify.min.js"></script>
    <style>
        .prose-chat h1,.prose-chat h2,.prose-chat h3{font-weight:700;margin:.6em 0 .3em}
        .prose-chat h1{font-size:1.2em}.prose-chat h2{font-size:1.1em}.prose-chat h3{font-size:1em}
        .prose-chat ul,.prose-chat ol{padding-left:1.25em;margin:.4em 0}
        .prose-chat ul{list-style:disc}.prose-chat ol{list-style:decimal}
        .prose-chat li{margin:.15em 0}
        .prose-chat p{margin:.3em 0}
        .prose-chat strong{font-weight:700}
        .prose-chat em{font-style:italic}
        .prose-chat code{background:#f1f5f9;border-radius:3px;padding:.1em .3em;font-family:monospace;font-size:.85em}
        .prose-chat pre{background:#1e293b;color:#e2e8f0;border-radius:.5em;padding:.75em;overflow-x:auto;margin:.5em 0}
        .prose-chat pre code{background:none;padding:0;color:inherit}
        .prose-chat blockquote{border-left:3px solid #10b981;padding-left:.75em;color:#475569;margin:.4em 0}
        .prose-chat table{border-collapse:collapse;width:100%;margin:.5em 0;font-size:.85em}
        .prose-chat th,.prose-chat td{border:1px solid #e2e8f0;padding:.3em .6em}
        .prose-chat th{background:#f8fafc;font-weight:600}
        .prose-notes{font-size:.875rem;line-height:1.6;color:#1e293b}
        .prose-notes h1,.prose-notes h2,.prose-notes h3{font-weight:700;margin:.8em 0 .4em;color:#0f172a}
        .prose-notes h2{font-size:1.1em;border-bottom:1px solid #e2e8f0;padding-bottom:.2em}
        .prose-notes ul,.prose-notes ol{padding-left:1.5em;margin:.4em 0}
        .prose-notes ul{list-style:disc}.prose-notes ol{list-style:decimal}
        .prose-notes li{margin:.2em 0}
        .prose-notes strong{font-weight:700}
        .prose-notes code{background:#f1f5f9;border-radius:3px;padding:.1em .3em;font-family:monospace;font-size:.85em}
        .prose-notes blockquote{border-left:3px solid #10b981;padding-left:.75em;color:#64748b;font-style:italic}
    </style>
    @endpush

    <div class="flex h-screen overflow-hidden" x-data="companionChat()" x-init="init()">

        {{-- Sidebar --}}
        <aside class="hidden md:flex flex-col w-64 bg-gray-900 text-white shrink-0">
            <div class="p-4 border-b border-gray-700">
                <div class="text-xs uppercase tracking-widest text-gray-400 mb-2">EduBridge AI</div>
                <a href="{{ route('student.companion.index') }}"
                   class="flex items-center gap-2 text-sm text-gray-300 hover:text-white">
                    &larr; All Conversations
                </a>
            </div>
            {{-- Model selector --}}
            <div class="p-4 border-b border-gray-700">
                <div class="text-xs text-gray-400 mb-2 uppercase tracking-widest">Active Model</div>
                <div class="flex rounded-lg overflow-hidden border border-gray-600">
                    <button @click="setModel('auto')"
                            :class="model==='auto' ? 'bg-green-600 text-white' : 'text-gray-300 hover:bg-gray-700'"
                            class="flex-1 py-1.5 text-xs font-medium transition">Auto</button>
                    <button @click="setModel('claude')"
                            :class="model==='claude' ? 'bg-violet-600 text-white' : 'text-gray-300 hover:bg-gray-700'"
                            class="flex-1 py-1.5 text-xs font-medium transition">Claude</button>
                    <button @click="setModel('gpt')"
                            :class="model==='gpt' ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700'"
                            class="flex-1 py-1.5 text-xs font-medium transition">GPT-4o</button>
                </div>
                <div class="mt-1.5 text-xs text-gray-500">Chiedza (Azure) always on fallback</div>
            </div>
            {{-- Tools --}}
            <div class="p-4 border-b border-gray-700">
                <div class="text-xs text-gray-400 mb-2 uppercase tracking-widest">Tools</div>
                <button @click="showPanel='notes'" :class="showPanel==='notes' ? 'bg-gray-700' : ''" class="w-full text-left text-sm text-gray-300 hover:text-white py-1.5 px-2 rounded">📝 Generate Notes</button>
                <button @click="showPanel='plan'"  :class="showPanel==='plan'  ? 'bg-gray-700' : ''" class="w-full text-left text-sm text-gray-300 hover:text-white py-1.5 px-2 rounded">📅 Study Plan</button>
                <button @click="showPanel='chat'"  :class="showPanel==='chat'  ? 'bg-gray-700' : ''" class="w-full text-left text-sm text-gray-300 hover:text-white py-1.5 px-2 rounded">💬 Chat</button>
            </div>
            {{-- Conversation info --}}
            <div class="p-4 text-xs text-gray-500 mt-auto">
                <div class="mb-1">Subject: <span class="text-gray-300">{{ $conversation->subject ?? '—' }}</span></div>
                <div class="mb-1">Level: <span class="text-gray-300">{{ $conversation->level ?? '—' }}</span></div>
                <div>Model: <span class="text-gray-300">{{ $conversation->preferred_model ?? 'auto' }}</span></div>
            </div>
        </aside>

        {{-- Main area --}}
        <div class="flex flex-col flex-1 overflow-hidden bg-gray-50">

            {{-- Header --}}
            <header class="flex items-center gap-3 px-4 py-3 bg-white border-b border-gray-200 shrink-0">
                <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm shrink-0"
                     :class="model==='gpt' ? 'bg-blue-600 text-white' : 'bg-green-700 text-white'">
                    <span x-show="model !== 'gpt'">C</span>
                    <span x-show="model === 'gpt'">G</span>
                </div>
                <div class="flex-1">
                    <div class="font-bold text-gray-900" x-text="model === 'gpt' ? 'GPT-4o' : model === 'chiedza' ? 'Chiedza' : 'Chiedza &amp; GPT-4o'"></div>
                    <div class="text-xs text-green-600">AI Study Companion &bull; Online</div>
                </div>
                <a href="{{ route('student.companion.index') }}" class="md:hidden text-gray-400 hover:text-gray-600 text-sm">&larr; Back</a>
            </header>

            {{-- ======================== CHAT PANEL ======================== --}}
            <div x-show="showPanel === 'chat'" class="flex flex-col flex-1 overflow-hidden">
                {{-- Messages --}}
                <div class="flex-1 overflow-y-auto px-4 py-4 space-y-4" id="messages">
                    @forelse($conversation->messages as $msg)
                    <div class="flex {{ $msg->role === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="relative max-w-xs md:max-w-lg lg:max-w-2xl">
                            @if($msg->role === 'assistant' && $msg->model_used)
                            <div class="text-xs mb-1 {{ $msg->model_used === 'gpt' ? 'text-blue-500' : 'text-green-600' }}">
                                {{ $msg->model_used === 'gpt' ? 'GPT-4o' : 'Chiedza' }}
                            </div>
                            @endif
                            <div class="px-4 py-3 rounded-2xl text-sm leading-relaxed
                                {{ $msg->role === 'user'
                                    ? 'bg-green-700 text-white rounded-br-sm'
                                    : ($msg->model_used === 'gpt'
                                        ? 'bg-blue-50 border border-blue-200 text-gray-800 rounded-bl-sm'
                                        : 'bg-white border border-gray-200 text-gray-800 rounded-bl-sm') }}">
                                {!! nl2br(e($msg->content)) !!}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-gray-400 py-16">
                        <div class="text-5xl mb-3">🎓</div>
                        <div class="font-medium">Hello! I'm your AI study companion.</div>
                        <div class="text-sm mt-1">Ask me anything about your studies, or use the tools in the sidebar.</div>
                    </div>
                    @endforelse

                    {{-- Typing indicator --}}
                    <div x-show="loading" class="flex justify-start">
                        <div class="bg-white border border-gray-200 rounded-2xl rounded-bl-sm px-4 py-3">
                            <div class="flex gap-1">
                                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                                <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Input --}}
                <div class="px-4 py-3 bg-white border-t border-gray-200 shrink-0">
                    {{-- File preview strip --}}
                    <div x-show="pendingFile" class="mb-2 flex items-center gap-2 bg-blue-50 border border-blue-200 rounded-lg px-3 py-2">
                        <span class="text-xs text-blue-700 font-medium" x-text="pendingFileName"></span>
                        <button @click="clearFile()" class="ml-auto text-xs text-red-500 hover:text-red-700">✕ Remove</button>
                    </div>
                    <form @submit.prevent="sendMessage" class="flex gap-2">
                        {{-- Hidden file input --}}
                        <input type="file"
                               id="fileInput"
                               accept="image/*,.pdf"
                               class="hidden"
                               @change="onFileSelected($event)"
                               x-ref="fileInput">
                        {{-- Attach button --}}
                        <button type="button"
                                @click="$refs.fileInput.click()"
                                title="Upload image or PDF"
                                :class="pendingFile ? 'text-blue-600 bg-blue-50 border-blue-300' : 'text-gray-400 hover:text-gray-600 border-gray-200'"
                                class="border rounded-xl px-3 py-3 transition shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/>
                            </svg>
                        </button>
                        <input x-model="inputMessage"
                               type="text"
                               :placeholder="pendingFile ? 'Ask about the file… (or leave blank for auto-analysis)' : 'Ask a question…'"
                               class="flex-1 border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none"
                               autocomplete="off" autofocus :disabled="loading">
                        <button type="submit"
                                :disabled="loading || (!inputMessage.trim() && !pendingFile)"
                                :class="model === 'gpt' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-green-700 hover:bg-green-800'"
                                class="text-white px-5 py-3 rounded-xl font-semibold transition disabled:opacity-50">
                            <span x-show="!loading">Send</span>
                            <span x-show="loading" class="flex items-center gap-1">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            </span>
                        </button>
                    </form>
                    <p class="text-xs text-gray-400 mt-1.5">📎 Attach an image or PDF — GPT-4o will read and explain it</p>
                </div>
            </div>

            {{-- ======================== NOTES PANEL ======================== --}}
            <div x-show="showPanel === 'notes'" class="flex-1 overflow-y-auto p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">📝 Generate Study Notes</h2>
                <div class="bg-white rounded-xl border border-gray-200 p-5 max-w-2xl">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Topic</label>
                            <input x-model="notesForm.topic" type="text" placeholder="e.g. Quadratic equations, Photosynthesis…"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Subject (optional)</label>
                                <input x-model="notesForm.subject" type="text" placeholder="e.g. Mathematics"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Level (optional)</label>
                                <select x-model="notesForm.level" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                                    <option value="">Any</option>
                                    <option>O-Level</option>
                                    <option>A-Level</option>
                                    <option>Primary</option>
                                    <option>University</option>
                                </select>
                            </div>
                        </div>
                        <button @click="generateNotes"
                                :disabled="notesLoading || !notesForm.topic.trim()"
                                class="bg-green-700 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-green-800 transition disabled:opacity-50 text-sm">
                            <span x-show="!notesLoading">Generate Notes</span>
                            <span x-show="notesLoading">Generating…</span>
                        </button>
                    </div>
                </div>
                <div x-show="notesResult" class="mt-6 bg-white rounded-xl border border-gray-200 p-5 max-w-2xl">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-gray-900">Generated Notes</h3>
                        <button @click="copyToClipboard(notesResult)" class="text-xs text-green-600 hover:underline">Copy</button>
                    </div>
                    <div class="prose-notes" x-html="renderMarkdown(notesResult)"></div>
                </div>
            </div>

            {{-- ======================== STUDY PLAN PANEL ======================== --}}
            <div x-show="showPanel === 'plan'" class="flex-1 overflow-y-auto p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">📅 Generate Study Plan</h2>
                <div class="bg-white rounded-xl border border-gray-200 p-5 max-w-2xl">
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">Number of weeks</label>
                            <input x-model.number="planForm.weeks" type="number" min="1" max="16" value="8"
                                   class="w-32 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Topics to cover <span class="text-gray-400">(one per line)</span></label>
                            <textarea x-model="planForm.topicsText" rows="4" placeholder="Algebra&#10;Trigonometry&#10;Statistics&#10;Calculus"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                        </div>
                        <button @click="generateStudyPlan"
                                :disabled="planLoading || !planForm.subject.trim()"
                                class="bg-blue-600 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-50 text-sm">
                            <span x-show="!planLoading">Generate Study Plan</span>
                            <span x-show="planLoading">Generating…</span>
                        </button>
                    </div>
                </div>
                {{-- Plan result --}}
                <template x-if="planResult && planResult.weeks">
                    <div class="mt-6 max-w-2xl space-y-3">
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
                                <div class="text-xs text-gray-600 mb-1 font-medium">Goals</div>
                                <ul class="list-disc list-inside text-sm text-gray-700 space-y-0.5 mb-2">
                                    <template x-for="g in week.goals"><li x-text="g"></li></template>
                                </ul>
                                <div class="text-xs text-gray-600 mb-1 font-medium">Activities</div>
                                <ul class="list-disc list-inside text-sm text-gray-700 space-y-0.5">
                                    <template x-for="a in week.activities"><li x-text="a"></li></template>
                                </ul>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

        </div>{{-- /main --}}
    </div>

<script>
function companionChat() {
    return {
        model: '{{ $conversation->preferred_model ?? "auto" }}',
        showPanel: 'chat',
        loading: false,
        inputMessage: '',
        pendingFile: null,
        pendingFileName: '',
        notesLoading: false,
        notesResult: '',
        notesForm: { topic: '', subject: '{{ $conversation->subject ?? "" }}', level: '{{ $conversation->level ?? "" }}' },
        planLoading: false,
        planResult: null,
        planForm: { subject: '{{ $conversation->subject ?? "" }}', level: '{{ $conversation->level ?? "" }}', weeks: 8, topicsText: '' },

        init() {
            this.scrollToBottom();
        },

        onFileSelected(event) {
            const file = event.target.files[0];
            if (!file) return;
            const maxMb = 10;
            if (file.size > maxMb * 1024 * 1024) {
                alert(`File is too large. Maximum size is ${maxMb} MB.`);
                event.target.value = '';
                return;
            }
            this.pendingFile     = file;
            this.pendingFileName = file.name;
        },

        clearFile() {
            this.pendingFile     = null;
            this.pendingFileName = '';
            if (this.$refs.fileInput) this.$refs.fileInput.value = '';
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const el = document.getElementById('messages');
                if (el) el.scrollTop = el.scrollHeight;
            });
        },

        setModel(m) {
            this.model = m;
            fetch('{{ route("student.companion.prefs", $conversation) }}', {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ preferred_model: m }),
            });
        },

        async sendMessage() {
            if ((!this.inputMessage.trim() && !this.pendingFile) || this.loading) return;

            const text = this.inputMessage;
            const file = this.pendingFile;
            this.inputMessage = '';
            this.loading = true;

            // Optimistic user message bubble
            const msgDiv = document.getElementById('messages');
            const bubble = document.createElement('div');
            bubble.className = 'flex justify-end';
            const label = file
                ? `<div class="text-xs opacity-75 mb-1">📎 ${this.escapeHtml(file.name)}</div>${text ? `<div>${this.escapeHtml(text)}</div>` : ''}`
                : this.escapeHtml(text);
            bubble.innerHTML = `<div class="max-w-xs md:max-w-lg px-4 py-3 rounded-2xl rounded-br-sm text-sm bg-green-700 text-white">${label}</div>`;
            msgDiv.appendChild(bubble);
            this.scrollToBottom();

            const modelMeta = {
                gpt:     { label: 'GPT-4o',  color: 'text-blue-500',   bg: 'bg-blue-50 border border-blue-200' },
                claude:  { label: 'Claude',  color: 'text-violet-500', bg: 'bg-violet-50 border border-violet-200' },
                chiedza: { label: 'Chiedza', color: 'text-green-600',  bg: 'bg-white border border-gray-200' },
            };

            try {
                let data;

                if (file) {
                    // Upload + vision path
                    const fd = new FormData();
                    fd.append('file', file);
                    fd.append('question', text || 'Please explain this and help me understand it.');
                    fd.append('_token', '{{ csrf_token() }}');
                    const res = await fetch('{{ route("student.companion.upload", $conversation) }}', {
                        method: 'POST',
                        headers: { 'Accept': 'application/json' },
                        body: fd,
                    });
                    data = await res.json();
                    this.clearFile();
                } else {
                    // Normal text path
                    const res = await fetch('{{ route("student.companion.send", $conversation) }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: JSON.stringify({ message: text, model: this.model }),
                    });
                    data = await res.json();
                }

                if (data.message) {
                    const modelUsed = data.model_used ?? 'gpt';
                    const meta = modelMeta[modelUsed] ?? modelMeta.chiedza;
                    const reply = document.createElement('div');
                    reply.className = 'flex justify-start';
                    reply.innerHTML = `<div class="max-w-xs md:max-w-lg">
                        <div class="text-xs mb-1 ${meta.color}">${meta.label}</div>
                        <div class="px-4 py-3 rounded-2xl rounded-bl-sm text-sm leading-relaxed ${meta.bg} text-gray-800 prose-chat">
                            ${this.renderMarkdown(data.message.content)}
                        </div>
                    </div>`;
                    msgDiv.appendChild(reply);
                    this.scrollToBottom();
                } else if (data.error) {
                    throw new Error(data.error);
                }
            } catch(e) {
                const err = document.createElement('div');
                err.className = 'flex justify-start';
                err.innerHTML = `<div class="px-4 py-3 rounded-2xl text-sm bg-red-50 border border-red-200 text-red-700">${this.escapeHtml(e.message || 'Something went wrong. Please try again.')}</div>`;
                msgDiv.appendChild(err);
            } finally {
                this.loading = false;
            }
        },

        async generateNotes() {
            if (!this.notesForm.topic.trim() || this.notesLoading) return;
            this.notesLoading = true;
            this.notesResult  = '';
            try {
                const res = await fetch('{{ route("student.companion.notes", $conversation) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify(this.notesForm),
                });
                const data = await res.json();
                this.notesResult = data.notes ?? 'Could not generate notes.';
            } catch(e) {
                this.notesResult = 'Error generating notes. Please try again.';
            } finally {
                this.notesLoading = false;
            }
        },

        async generateStudyPlan() {
            if (!this.planForm.subject.trim() || this.planLoading) return;
            this.planLoading = true;
            this.planResult  = null;
            const topics = this.planForm.topicsText.split('\n').map(t => t.trim()).filter(Boolean);
            try {
                const res = await fetch('{{ route("student.companion.study-plan", $conversation) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ ...this.planForm, topics }),
                });
                const data = await res.json();
                this.planResult = data.plan ?? {};
            } catch(e) {
                alert('Error generating plan. Please try again.');
            } finally {
                this.planLoading = false;
            }
        },

        copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => alert('Copied!'));
        },

        escapeHtml(s) {
            return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        },

        renderMarkdown(text) {
            if (typeof marked === 'undefined') {
                return this.escapeHtml(text).replace(/\n/g, '<br>');
            }
            const html = marked.parse(text ?? '', { breaks: true, gfm: true });
            return typeof DOMPurify !== 'undefined' ? DOMPurify.sanitize(html) : html;
        },
    };
}
</script>
</x-app-layout>
