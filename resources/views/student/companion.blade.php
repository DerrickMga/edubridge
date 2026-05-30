<x-app-layout>
    <x-slot name="title">Chiedza — AI Companion</x-slot>

    <div class="page-header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="page-title flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                    </span>
                    Chiedza
                </h1>
                <p class="page-subtitle">Your AI study companion — powered by Claude</p>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-[260px_1fr] gap-6 max-h-[calc(100vh-12rem)]">

        {{-- Sidebar: chat history --}}
        <div class="card overflow-hidden flex flex-col">
            <div class="p-3 border-b border-slate-100">
                <a href="{{ route('student.companion.index') }}" class="btn-primary w-full justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    New conversation
                </a>
            </div>
            <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
                @forelse($conversations ?? [] as $conv)
                <a href="{{ route('student.companion.show', $conv) }}"
                    class="flex items-start gap-2.5 px-3 py-3 hover:bg-slate-50 transition-colors {{ (isset($currentConv) && $currentConv->id === $conv->id) ? 'bg-indigo-50' : '' }}">
                    <div class="w-7 h-7 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 0 1 .865-.501 48.172 48.172 0 0 0 3.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-slate-800 truncate">{{ $conv->title ?? 'Study session' }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5">{{ $conv->created_at->diffForHumans() }}</p>
                    </div>
                </a>
                @empty
                <div class="p-4 text-center text-xs text-slate-400">No conversations yet</div>
                @endforelse
            </div>
        </div>

        {{-- Main chat area --}}
        <div class="card flex flex-col overflow-hidden">

            {{-- Messages --}}
            <div class="flex-1 overflow-y-auto p-5 space-y-4" id="chat-messages">

                @if(empty($messages))
                {{-- Welcome state --}}
                <div class="flex flex-col items-center justify-center h-full text-center py-12">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-100 to-violet-100 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                    </div>
                    <h3 class="font-bold text-slate-800 mb-2">Mhoro! I'm Chiedza 👋</h3>
                    <p class="text-slate-400 text-sm max-w-sm">I'm your AI study companion for Zimbabwe O-Level and A-Level. Ask me anything — I'll explain concepts, solve problems, and guide your revision.</p>
                    <div class="flex flex-wrap gap-2 mt-5 justify-center">
                        @foreach(['Explain quadratic equations', 'Help me with O-Level Biology', 'Tips for ZIMSEC exams', 'Explain the liberation war'] as $prompt)
                        <button type="button" onclick="document.getElementById('message-input').value = this.innerText; document.getElementById('message-input').focus()"
                            class="px-3 py-1.5 rounded-lg border border-indigo-200 bg-indigo-50 text-indigo-700 text-xs font-medium hover:bg-indigo-100 transition-colors">
                            {{ $prompt }}
                        </button>
                        @endforeach
                    </div>
                </div>
                @else
                @foreach($messages as $message)
                <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }} gap-3">
                    @if($message['role'] === 'assistant')
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center flex-shrink-0 mt-1">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                    </div>
                    @endif
                    <div class="max-w-[80%] px-4 py-3 rounded-2xl text-sm leading-relaxed
                        {{ $message['role'] === 'user'
                            ? 'bg-indigo-600 text-white rounded-tr-none'
                            : 'bg-slate-100 text-slate-800 rounded-tl-none' }}">
                        {!! nl2br(e($message['content'])) !!}
                    </div>
                    @if($message['role'] === 'user')
                    <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold flex items-center justify-center flex-shrink-0 mt-1 uppercase">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    @endif
                </div>
                @endforeach
                @endif
            </div>

            {{-- Input form --}}
            <div class="border-t border-slate-100 p-4">
                <form method="POST" action="{{ route('student.companion.store') }}" class="flex gap-3">
                    @csrf
                    <input type="hidden" name="conversation_id" value="{{ $currentConv->id ?? '' }}" />
                    <input
                        id="message-input"
                        type="text"
                        name="message"
                        placeholder="Ask Chiedza anything about your studies…"
                        required
                        class="form-input flex-1"
                        autocomplete="off"
                    />
                    <button type="submit" class="btn-primary flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                        Send
                    </button>
                </form>
                <p class="text-[10px] text-slate-300 mt-2 text-center">AI responses may be inaccurate. Always verify with your teacher or textbook.</p>
            </div>
        </div>
    </div>
</x-app-layout>
