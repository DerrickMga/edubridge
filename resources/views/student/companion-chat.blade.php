<x-app-layout>
    <x-slot name="title">Chat with Chiedza</x-slot>
    <div class="max-w-3xl mx-auto px-4 py-6 flex flex-col" style="height: calc(100vh - 4rem)">
        <div class="flex items-center gap-3 mb-4">
            <a href="{{ route('student.companion.index') }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
            <div class="w-9 h-9 rounded-full bg-green-700 text-white flex items-center justify-center font-bold text-sm">C</div>
            <div>
                <div class="font-bold text-gray-900">Chiedza</div>
                <div class="text-xs text-green-600">AI Study Companion &bull; Online</div>
            </div>
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto space-y-4 mb-4" id="messages">
            @foreach($conversation->messages as $msg)
            <div class="flex {{ $msg->role === 'user' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-xs md:max-w-md lg:max-w-lg px-4 py-3 rounded-2xl text-sm
                    {{ $msg->role === 'user' ? 'bg-green-700 text-white rounded-br-sm' : 'bg-white border border-gray-200 text-gray-800 rounded-bl-sm' }}">
                    {!! nl2br(e($msg->content)) !!}
                </div>
            </div>
            @endforeach
        </div>

        {{-- Input --}}
        <form action="{{ route('student.companion.send', $conversation) }}" method="POST" class="flex gap-2">
            @csrf
            <input
                type="text"
                name="message"
                placeholder="Ask Chiedza anything..."
                class="flex-1 border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none"
                autocomplete="off"
                autofocus
                required
            >
            <button type="submit" class="bg-green-700 text-white px-5 py-3 rounded-xl font-semibold hover:bg-green-800 transition">
                Send
            </button>
        </form>
    </div>

    <script>
        // Auto-scroll to bottom on load
        const msgs = document.getElementById('messages');
        if (msgs) msgs.scrollTop = msgs.scrollHeight;
    </script>
</x-app-layout>
