<x-app-layout>
    <x-slot name="title">Ticket {{ $ticket->ticket_number }}</x-slot>

    <div class="space-y-6 max-w-3xl">

        {{-- Back --}}
        <a href="{{ route('support.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-violet-600 font-medium">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            My Tickets
        </a>

        @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
        @endif

        {{-- Ticket header card --}}
        <div class="card p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-mono text-xs font-bold text-violet-600 bg-violet-50 px-2 py-0.5 rounded">{{ $ticket->ticket_number }}</span>
                        <span class="badge {{ $ticket->statusBadgeClass() }} capitalize">{{ str_replace('_', ' ', $ticket->status) }}</span>
                        <span class="badge {{ $ticket->priorityBadgeClass() }} capitalize">{{ $ticket->priority }}</span>
                    </div>
                    <h1 class="text-lg font-bold text-slate-800 mt-1">{{ $ticket->subject }}</h1>
                    <p class="text-xs text-slate-400 mt-1">
                        <span class="capitalize">{{ $ticket->category }}</span> &middot; Opened {{ $ticket->created_at->format('d M Y, H:i') }}
                        @if($ticket->resolved_at) &middot; Resolved {{ $ticket->resolved_at->format('d M Y') }} @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Conversation thread --}}
        <div class="space-y-4">

            {{-- Original message --}}
            <div class="flex gap-3">
                <div class="w-9 h-9 rounded-full overflow-hidden flex-shrink-0">
                    @if($ticket->user?->avatar)
                        <img src="{{ $ticket->user->avatar_url }}" class="w-full h-full object-cover" alt="">
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-violet-400 to-violet-600 text-white text-sm font-bold flex items-center justify-center uppercase">{{ substr($ticket->user?->name ?? '?', 0, 1) }}</div>
                    @endif
                </div>
                <div class="flex-1">
                    <div class="bg-white rounded-2xl rounded-tl-sm border border-slate-100 px-4 py-3 shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-semibold text-slate-800">{{ $ticket->user?->name }}</p>
                            <p class="text-xs text-slate-400">{{ $ticket->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <p class="text-sm text-slate-700 whitespace-pre-line">{{ $ticket->message }}</p>
                    </div>
                </div>
            </div>

            {{-- Replies --}}
            @foreach($ticket->replies as $reply)
            @php $isAdmin = $reply->is_admin_reply; @endphp
            <div class="flex gap-3 {{ $isAdmin ? 'flex-row-reverse' : '' }}">
                <div class="w-9 h-9 rounded-full overflow-hidden flex-shrink-0">
                    @if($reply->user?->avatar)
                        <img src="{{ $reply->user->avatar_url }}" class="w-full h-full object-cover" alt="">
                    @elseif($isAdmin)
                        <div class="w-full h-full bg-gradient-to-br from-emerald-400 to-emerald-600 text-white text-xs font-bold flex items-center justify-center uppercase">A</div>
                    @else
                        <div class="w-full h-full bg-gradient-to-br from-violet-400 to-violet-600 text-white text-sm font-bold flex items-center justify-center uppercase">{{ substr($reply->user?->name ?? '?', 0, 1) }}</div>
                    @endif
                </div>
                <div class="flex-1">
                    <div class="rounded-2xl border px-4 py-3 shadow-sm {{ $isAdmin ? 'bg-emerald-50 border-emerald-100 rounded-tr-sm' : 'bg-white border-slate-100 rounded-tl-sm' }}">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-sm font-semibold {{ $isAdmin ? 'text-emerald-800' : 'text-slate-800' }}">
                                {{ $isAdmin ? 'EduBridge Support' : $reply->user?->name }}
                            </p>
                            <p class="text-xs text-slate-400">{{ $reply->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <p class="text-sm whitespace-pre-line {{ $isAdmin ? 'text-emerald-900' : 'text-slate-700' }}">{{ $reply->message }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Reply form --}}
        @if($ticket->isOpen())
        <div class="card p-5">
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Add a Reply</h3>
            <form method="POST" action="{{ route('support.reply', $ticket) }}" class="space-y-3">
                @csrf
                <textarea name="message" rows="4" required class="form-input" placeholder="Type your message…">{{ old('message') }}</textarea>
                @error('message')<p class="form-error">{{ $message }}</p>@enderror
                <div class="flex justify-end">
                    <button type="submit" class="btn-primary">Send Reply</button>
                </div>
            </form>
        </div>
        @else
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-center text-sm text-slate-500">
            This ticket is <strong class="capitalize">{{ $ticket->status }}</strong>. Replies are disabled.
        </div>
        @endif

    </div>
</x-app-layout>
