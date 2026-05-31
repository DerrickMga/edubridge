<x-app-layout>
    <x-slot name="title">Session Report — {{ $session->title }}</x-slot>

    <div class="mb-6">
        <a href="{{ route('admin.session-reports.index') }}" class="text-sm text-blue-600 hover:text-blue-800">← All reports</a>
        <h1 class="page-title mt-2">{{ $session->title }}</h1>
        <p class="page-subtitle">
            {{ $session->course->title ?? '—' }} · {{ $session->teacher->name ?? '—' }} ·
            {{ $session->scheduled_at->format('D d M Y g:i a') }} · {{ $session->duration_minutes }} min
        </p>
    </div>

    @if(session('success'))
    <div class="mb-4 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="grid lg:grid-cols-3 gap-6">

        {{-- LEFT: AI Report ──────────────────────────────────────────── --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- AI Summary --}}
            <div class="card p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-bold text-slate-800 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-violet-100 flex items-center justify-center text-violet-600 text-xs">AI</span>
                        AI Summary
                    </h2>
                    @if(! $session->aiReport || $session->aiReport->error)
                    <form action="{{ route('admin.session-reports.reprocess', $session) }}" method="POST">
                        @csrf @method('POST')
                        <button class="text-xs px-3 py-1.5 rounded-lg bg-violet-600 hover:bg-violet-700 text-white font-semibold transition-colors">
                            {{ $session->aiReport?->error ? 'Retry AI' : 'Run AI' }}
                        </button>
                    </form>
                    @endif
                </div>

                @if($session->aiReport?->processed_at)
                    <p class="text-slate-700 text-sm leading-relaxed mb-4">{{ $session->aiReport->summary ?? 'No summary generated.' }}</p>

                    @if($session->aiReport->action_items)
                    <h3 class="font-semibold text-slate-700 text-sm mb-2">Action Items</h3>
                    <ul class="space-y-1.5">
                        @foreach($session->aiReport->action_items as $item)
                        <li class="flex items-start gap-2 text-sm text-slate-600">
                            <span class="mt-1 w-4 h-4 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center flex-shrink-0 text-xs font-bold">{{ $loop->index + 1 }}</span>
                            {{ $item }}
                        </li>
                        @endforeach
                    </ul>
                    @endif

                    <p class="text-xs text-slate-400 mt-4">Processed {{ $session->aiReport->processed_at->diffForHumans() }}</p>
                @elseif($session->aiReport?->error)
                    <p class="text-sm text-red-600 bg-red-50 rounded-lg p-3">{{ $session->aiReport->error }}</p>
                @else
                    <p class="text-sm text-slate-400 italic">AI report not yet processed.</p>
                @endif
            </div>

            {{-- Auto-created Quiz --}}
            @if($session->aiReport?->quiz)
            <div class="card p-5">
                <h2 class="font-bold text-slate-800 mb-3 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600 text-xs">Q</span>
                    AI-Generated Quiz
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $session->aiReport->quiz->is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }} font-medium">
                        {{ $session->aiReport->quiz->is_published ? 'Published' : 'Draft' }}
                    </span>
                </h2>
                <p class="text-sm text-slate-600 mb-3">{{ $session->aiReport->quiz->title }}</p>
                <div class="space-y-3">
                    @foreach($session->aiReport->quiz->questions as $q)
                    <div class="p-3 bg-slate-50 rounded-lg">
                        <p class="text-sm font-semibold text-slate-800 mb-2">{{ $loop->index + 1 }}. {{ $q->question }}</p>
                        <ul class="space-y-1">
                            @foreach($q->options ?? [] as $opt)
                            <li class="text-xs text-slate-600 flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full {{ $opt === $q->correct_answer ? 'bg-emerald-500' : 'bg-slate-200' }}"></span>
                                {{ $opt }}
                            </li>
                            @endforeach
                        </ul>
                        @if($q->explanation)
                        <p class="text-xs text-blue-600 mt-1.5 italic">{{ $q->explanation }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Attendance list --}}
            <div class="card p-5">
                <h2 class="font-bold text-slate-800 mb-3 flex items-center gap-2">
                    <span class="w-6 h-6 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600 text-xs">👥</span>
                    Attendance ({{ $session->attendances->count() }} via platform)
                </h2>
                @if($session->attendances->isEmpty())
                <p class="text-sm text-slate-400 italic">No attendance tracked yet.</p>
                @else
                <div class="flex flex-wrap gap-2">
                    @foreach($session->attendances as $att)
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 rounded-lg text-xs text-slate-700">
                        <span class="w-5 h-5 rounded-full bg-slate-300 flex items-center justify-center text-xs font-bold uppercase">{{ substr($att->user->name ?? '?', 0, 1) }}</span>
                        {{ $att->user->name ?? 'Unknown' }}
                    </span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- RIGHT: Teacher Log + Payment ───────────────────────────── --}}
        <div class="space-y-5">

            {{-- Teacher Log --}}
            <div class="card p-5">
                <h2 class="font-bold text-slate-800 mb-3">Teacher Log</h2>
                @if($session->sessionLog)
                @php $log = $session->sessionLog; @endphp
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Duration logged</dt>
                        <dd class="font-semibold text-slate-800">{{ $log->actual_duration_minutes }} min</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Students reported</dt>
                        <dd class="font-semibold text-slate-800">{{ $log->actual_student_count }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">AI tracked</dt>
                        <dd class="font-semibold text-slate-800">{{ $session->aiReport?->attendees_count ?? '—' }}</dd>
                    </div>
                    @if($log->notes)
                    <div class="pt-2 border-t border-slate-100">
                        <dt class="text-slate-500 mb-1">Notes</dt>
                        <dd class="text-slate-700 text-xs leading-relaxed">{{ $log->notes }}</dd>
                    </div>
                    @endif
                </dl>
                @else
                <p class="text-sm text-amber-600 italic">Teacher has not submitted a log yet.</p>
                @endif
            </div>

            {{-- Payment Item --}}
            <div class="card p-5">
                <h2 class="font-bold text-slate-800 mb-3">Payment Claim</h2>
                @if($session->paymentItem)
                @php $pay = $session->paymentItem; @endphp
                <dl class="space-y-2 text-sm mb-4">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Hours billed</dt>
                        <dd class="font-semibold">{{ $pay->hours_logged }}h</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Rate</dt>
                        <dd class="font-semibold">${{ number_format($pay->hourly_rate_usd, 2) }}/hr</dd>
                    </div>
                    <div class="flex justify-between border-t border-slate-100 pt-2">
                        <dt class="text-slate-700 font-semibold">Total</dt>
                        <dd class="font-bold text-slate-900 text-base">${{ number_format($pay->total_usd, 2) }}</dd>
                    </div>
                </dl>

                <div class="mb-3">
                    <span class="px-3 py-1 rounded-full text-xs font-bold
                        {{ $pay->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : ($pay->status === 'approved' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700') }}">
                        {{ strtoupper($pay->status) }}
                    </span>
                    @if($pay->approved_at)
                    <p class="text-xs text-slate-400 mt-1">Approved {{ $pay->approved_at->format('d M Y') }} by {{ $pay->approver?->name }}</p>
                    @endif
                    @if($pay->admin_notes)
                    <p class="text-xs text-slate-500 italic mt-1">{{ $pay->admin_notes }}</p>
                    @endif
                </div>

                @if($pay->status === 'pending')
                <form action="{{ route('admin.teacher-payments.approve', $pay) }}" method="POST" class="space-y-2">
                    @csrf
                    <input type="number" name="hourly_rate_usd" step="0.01" value="{{ $pay->hourly_rate_usd }}"
                           placeholder="Override rate (USD)" class="form-input text-sm w-full">
                    <textarea name="admin_notes" rows="2" placeholder="Admin notes…" class="form-input text-sm w-full resize-none"></textarea>
                    <button class="w-full px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors">
                        Approve Payment
                    </button>
                </form>
                @elseif($pay->status === 'approved')
                <form action="{{ route('admin.teacher-payments.mark-paid', $pay) }}" method="POST">
                    @csrf
                    <button class="w-full px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold transition-colors">
                        Mark as Paid
                    </button>
                </form>
                @endif

                @else
                <p class="text-sm text-slate-400 italic">No payment claim yet — awaiting teacher log.</p>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
