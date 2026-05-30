<x-app-layout>
    <x-slot name="title">AI Companion</x-slot>
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Chiedza — AI Study Companion</h1>
                <p class="text-gray-500 text-sm">Your personal 24/7 tutor for O &amp; A level subjects.</p>
            </div>
            <form method="POST" action="{{ route('student.companion.store') }}">
                @csrf
                <button class="bg-green-700 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-green-800 transition">
                    + New Chat
                </button>
            </form>
        </div>

        @if($conversations->isEmpty())
            <div class="bg-white border border-dashed border-gray-300 rounded-2xl p-16 text-center">
                <div class="text-6xl mb-4">🤖</div>
                <h2 class="text-xl font-bold text-gray-900 mb-2">Start chatting with Chiedza</h2>
                <p class="text-gray-400 mb-6">Ask anything about your schoolwork — Maths, Science, English, History and more.</p>
                <form method="POST" action="{{ route('student.companion.store') }}">
                    @csrf
                    <button class="bg-green-700 text-white px-8 py-3 rounded-xl font-semibold hover:bg-green-800 transition">
                        Start first conversation
                    </button>
                </form>
            </div>
        @else
            <div class="space-y-2">
                @foreach($conversations as $conv)
                <a href="{{ route('student.companion.show', $conv) }}" class="block bg-white border border-gray-200 rounded-xl p-4 hover:border-green-400 hover:shadow-sm transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $conv->title ?? 'Conversation' }}</div>
                            <div class="text-xs text-gray-400">{{ $conv->updated_at->diffForHumans() }} &middot; {{ $conv->messages()->count() }} messages</div>
                        </div>
                        <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </a>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
