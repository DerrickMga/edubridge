<x-app-layout>
    <x-slot name="title">My Support Tickets</x-slot>

    <div class="space-y-6">
        <div class="page-header">
            <div>
                <h1 class="page-title">My Support Tickets</h1>
                <p class="page-subtitle">Track the status of your help requests.</p>
            </div>
            <button onclick="document.getElementById('newTicketModal').classList.remove('hidden')"
                    class="btn-primary">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                New Ticket
            </button>
        </div>

        @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
        @endif

        <div class="card overflow-hidden">
            @if($tickets->count())
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Ticket</th>
                            <th>Subject</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Replies</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                        <tr>
                            <td>
                                <a href="{{ route('support.show', $ticket) }}" class="font-mono text-xs font-bold text-violet-600 hover:text-violet-800">
                                    {{ $ticket->ticket_number }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('support.show', $ticket) }}" class="text-sm font-medium text-slate-800 hover:text-violet-700 line-clamp-1">
                                    {{ $ticket->subject }}
                                </a>
                            </td>
                            <td><span class="text-xs text-slate-500 capitalize">{{ $ticket->category }}</span></td>
                            <td><span class="badge {{ $ticket->priorityBadgeClass() }} capitalize">{{ $ticket->priority }}</span></td>
                            <td><span class="badge {{ $ticket->statusBadgeClass() }} capitalize">{{ str_replace('_', ' ', $ticket->status) }}</span></td>
                            <td class="text-sm text-slate-500">{{ $ticket->replies_count }}</td>
                            <td class="text-xs text-slate-400">{{ $ticket->created_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100">
                {{ $tickets->links() }}
            </div>
            @else
            <div class="text-center py-16 text-slate-400">
                <svg class="w-10 h-10 mx-auto mb-3 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"/></svg>
                <p class="text-sm font-medium">No support tickets yet</p>
                <p class="text-xs mt-1">Use the support widget or the button above to raise a request.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- New Ticket Modal --}}
    <div id="newTicketModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
         x-data="{}" @keydown.escape.window="document.getElementById('newTicketModal').classList.add('hidden')">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden" @click.stop>
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-800">New Support Ticket</h2>
                <button onclick="document.getElementById('newTicketModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('support.store') }}" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="form-label">Subject <span class="text-red-500">*</span></label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required class="form-input" placeholder="Brief description of your issue">
                    @error('subject')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Category</label>
                        <select name="category" class="form-input">
                            <option value="general">General</option>
                            <option value="technical">Technical</option>
                            <option value="billing">Billing</option>
                            <option value="course">Course</option>
                            <option value="complaint">Complaint</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-input">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="form-label">Message <span class="text-red-500">*</span></label>
                    <textarea name="message" rows="5" required class="form-input" placeholder="Please describe your issue in detail...">{{ old('message') }}</textarea>
                    @error('message')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('newTicketModal').classList.add('hidden')" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Submit Ticket</button>
                </div>
            </form>
        </div>
    </div>

    @if($errors->any())
    @push('scripts')
    <script>document.getElementById('newTicketModal').classList.remove('hidden');</script>
    @endpush
    @endif
</x-app-layout>
