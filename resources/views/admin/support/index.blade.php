<x-app-layout>
    <x-slot name="title">Support Inbox — Admin</x-slot>

    <div class="space-y-6">
        <div class="page-header">
            <div>
                <h1 class="page-title">Support Inbox</h1>
                <p class="page-subtitle">Manage help requests from students and teachers.</p>
            </div>
            <a href="{{ route('admin.support.email') }}" class="btn-primary">
                <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                Compose Email
            </a>
        </div>

        {{-- Status tabs --}}
        <div class="flex gap-1 p-1 bg-slate-100 rounded-xl w-fit flex-wrap">
            @foreach([
                ['all',         'All',         $counts['all']],
                ['open',        'Open',        $counts['open']],
                ['in_progress', 'In Progress', $counts['in_progress']],
                ['waiting',     'Waiting',     $counts['waiting']],
                ['resolved',    'Resolved',    $counts['resolved']],
                ['closed',      'Closed',      $counts['closed']],
            ] as [$val, $label, $count])
            <a href="{{ route('admin.support.index', ['status' => $val]) }}"
               class="px-3 py-1.5 text-sm font-semibold rounded-lg transition-all {{ $status === $val ? 'bg-white shadow-sm text-slate-800' : 'text-slate-500 hover:text-slate-700' }}">
                {{ $label }} <span class="ml-1 opacity-60">{{ $count }}</span>
            </a>
            @endforeach
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
                            <th>User</th>
                            <th>Subject</th>
                            <th>Category</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Replies</th>
                            <th>Opened</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                        <tr class="{{ $ticket->status === 'open' ? 'bg-violet-50/30' : '' }}">
                            <td>
                                <a href="{{ route('admin.support.show', $ticket) }}" class="font-mono text-xs font-bold text-violet-600 hover:text-violet-800">
                                    {{ $ticket->ticket_number }}
                                </a>
                            </td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full overflow-hidden flex-shrink-0">
                                        @if($ticket->user?->avatar)
                                            <img src="{{ $ticket->user->avatar_url }}" class="w-full h-full object-cover" alt="">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-violet-400 to-violet-600 text-white text-[10px] font-bold flex items-center justify-center uppercase">{{ substr($ticket->user?->name ?? '?', 0, 1) }}</div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-medium text-slate-800 truncate">{{ $ticket->user?->name }}</p>
                                        <p class="text-[10px] text-slate-400 capitalize">{{ $ticket->user?->role }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('admin.support.show', $ticket) }}" class="text-sm font-medium text-slate-800 hover:text-violet-700 line-clamp-1 max-w-[200px] block">
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
            <div class="text-center py-14 text-slate-400">
                <svg class="w-8 h-8 mx-auto mb-2 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                <p class="text-sm font-medium">No tickets with this status</p>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
