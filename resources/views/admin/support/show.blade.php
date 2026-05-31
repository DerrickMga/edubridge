<x-app-layout>
    <x-slot name="title">Ticket {{ $ticket->ticket_number }} — Admin</x-slot>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- LEFT: Conversation --}}
        <div class="xl:col-span-2 space-y-5">

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.support.index') }}" class="text-sm text-slate-500 hover:text-violet-600 font-medium">
                    ← Support Inbox
                </a>
            </div>

            @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
            @endif

            {{-- Ticket header --}}
            <div class="card p-5">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-mono text-xs font-bold text-violet-600 bg-violet-50 px-2 py-0.5 rounded">{{ $ticket->ticket_number }}</span>
                            <span class="badge {{ $ticket->statusBadgeClass() }} capitalize">{{ str_replace('_', ' ', $ticket->status) }}</span>
                            <span class="badge {{ $ticket->priorityBadgeClass() }} capitalize">{{ $ticket->priority }}</span>
                        </div>
                        <h1 class="text-base font-bold text-slate-800 mt-1">{{ $ticket->subject }}</h1>
                        <p class="text-xs text-slate-400 mt-0.5">
                            <span class="capitalize">{{ $ticket->category }}</span> &middot;
                            Opened {{ $ticket->created_at->format('d M Y, H:i') }}
                            by <strong>{{ $ticket->user?->name }}</strong>
                            @if($ticket->resolved_at) &middot; Resolved {{ $ticket->resolved_at->format('d M Y') }} @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- Thread --}}
            <div class="space-y-4">
                {{-- Original --}}
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
                                <p class="text-sm font-semibold text-slate-800">{{ $ticket->user?->name }} <span class="text-xs font-normal text-slate-400 capitalize ml-1">{{ $ticket->user?->role }}</span></p>
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
                            <div class="w-full h-full bg-gradient-to-br from-emerald-400 to-emerald-600 text-white text-xs font-bold flex items-center justify-center uppercase">{{ substr($reply->user?->name ?? 'A', 0, 1) }}</div>
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-violet-400 to-violet-600 text-white text-sm font-bold flex items-center justify-center uppercase">{{ substr($reply->user?->name ?? '?', 0, 1) }}</div>
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="rounded-2xl border px-4 py-3 shadow-sm {{ $isAdmin ? 'bg-emerald-50 border-emerald-100 rounded-tr-sm' : 'bg-white border-slate-100 rounded-tl-sm' }}">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm font-semibold {{ $isAdmin ? 'text-emerald-800' : 'text-slate-800' }}">
                                    {{ $reply->user?->name }}
                                    @if($isAdmin)<span class="text-xs font-normal ml-1 opacity-60">Support Agent</span>@endif
                                </p>
                                <p class="text-xs text-slate-400">{{ $reply->created_at->format('d M Y, H:i') }}</p>
                            </div>
                            <p class="text-sm whitespace-pre-line {{ $isAdmin ? 'text-emerald-900' : 'text-slate-700' }}">{{ $reply->message }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Admin reply form --}}
            <div class="card p-5">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Reply to Ticket</h3>
                <form method="POST" action="{{ route('admin.support.reply', $ticket) }}" class="space-y-3">
                    @csrf
                    <textarea name="message" rows="5" required class="form-input" placeholder="Type your reply…">{{ old('message') }}</textarea>
                    @error('message')<p class="form-error">{{ $message }}</p>@enderror
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <div class="flex items-center gap-2">
                            <label class="text-xs text-slate-500 font-medium">After reply, set status to:</label>
                            <select name="status" class="form-input py-1.5 text-xs w-auto">
                                <option value="waiting">Waiting for user</option>
                                <option value="in_progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <button type="submit" class="btn-primary">Send Reply</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- RIGHT: Ticket details + management --}}
        <div class="space-y-5">

            {{-- User info --}}
            <div class="card p-5">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">User</h3>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0">
                        @if($ticket->user?->avatar)
                            <img src="{{ $ticket->user->avatar_url }}" class="w-full h-full object-cover" alt="">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-violet-400 to-violet-600 text-white font-bold flex items-center justify-center uppercase">{{ substr($ticket->user?->name ?? '?', 0, 1) }}</div>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ $ticket->user?->name }}</p>
                        <p class="text-xs text-slate-400">{{ $ticket->user?->email }}</p>
                        <p class="text-xs text-slate-400 capitalize">{{ $ticket->user?->role }}</p>
                    </div>
                </div>
            </div>

            {{-- Status management --}}
            <div class="card p-5">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Manage Ticket</h3>
                <form method="POST" action="{{ route('admin.support.status', $ticket) }}" class="space-y-3">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="form-label">Status</label>
                        <select name="status" class="form-input">
                            @foreach(['open','in_progress','waiting','resolved','closed'] as $s)
                            <option value="{{ $s }}" {{ $ticket->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Assigned To</label>
                        <select name="assigned_to" class="form-input">
                            <option value="">— Unassigned —</option>
                            @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" {{ $ticket->assigned_to === $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Internal Notes</label>
                        <textarea name="admin_notes" rows="3" class="form-input text-xs" placeholder="Notes visible only to admins…">{{ old('admin_notes', $ticket->admin_notes) }}</textarea>
                    </div>

                    <button type="submit" class="btn-secondary w-full">Update Ticket</button>
                </form>
            </div>

            {{-- Ticket meta --}}
            <div class="card p-5 space-y-2 text-sm">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Details</h3>
                <div class="flex justify-between"><span class="text-slate-400">Category</span><span class="capitalize font-medium text-slate-700">{{ $ticket->category }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Priority</span><span class="badge {{ $ticket->priorityBadgeClass() }} capitalize">{{ $ticket->priority }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Replies</span><span class="font-medium text-slate-700">{{ $ticket->replies->count() }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Opened</span><span class="text-slate-700 text-xs">{{ $ticket->created_at->format('d M Y') }}</span></div>
                @if($ticket->resolved_at)
                <div class="flex justify-between"><span class="text-slate-400">Resolved</span><span class="text-slate-700 text-xs">{{ $ticket->resolved_at->format('d M Y') }}</span></div>
                @endif
                @if($ticket->assignee)
                <div class="flex justify-between"><span class="text-slate-400">Assigned</span><span class="text-slate-700 text-xs">{{ $ticket->assignee->name }}</span></div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
