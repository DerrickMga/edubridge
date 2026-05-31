<x-app-layout>
    <x-slot name="title">Admin Dashboard</x-slot>

    @push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @endpush

    <div class="page-header flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="page-title">Platform Overview</h1>
            <p class="page-subtitle">EduBridge administration — {{ \Carbon\Carbon::now()->format('d M Y') }}</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="{{ route('admin.courses.index') }}" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Courses
            </a>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 bg-slate-700 hover:bg-slate-800 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                Users
            </a>
            <a href="{{ route('admin.ai-tools') }}" class="inline-flex items-center gap-1.5 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                AI Tools
            </a>
            <a href="{{ route('admin.settings.pricing') }}" class="inline-flex items-center gap-1.5 bg-teal-600 hover:bg-teal-700 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                Pricing
            </a>
            @if($pendingPayments > 0)
            <a href="{{ route('admin.teacher-payments.index') }}" class="inline-flex items-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
                {{ $pendingPayments }} Pending Pay-outs
            </a>
            @endif
        </div>
    </div>

    {{-- KPI Row 1 --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
        <div class="stat-card">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Students</p>
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z"/></svg>
                </div>
            </div>
            <p class="stat-value">{{ number_format($studentCount) }}</p>
            <p class="stat-label">+{{ $newUsersWeek }} new this week</p>
        </div>
        <div class="stat-card">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Enrollments</p>
                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/></svg>
                </div>
            </div>
            <p class="stat-value">{{ number_format($totalEnrollments) }}</p>
            <p class="stat-label">+{{ $weekEnrollments }} this week</p>
        </div>
        <div class="stat-card">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Revenue (30d)</p>
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
            </div>
            <p class="stat-value">${{ number_format($monthRevenue, 0) }}</p>
            <p class="stat-label">All-time: ${{ number_format($totalRevenue, 0) }}</p>
        </div>
        <div class="stat-card">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">AI Sessions</p>
                <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z"/></svg>
                </div>
            </div>
            <p class="stat-value">{{ number_format($totalConversations) }}</p>
            <p class="stat-label">{{ number_format($weekConversations) }} this week · {{ number_format($weekMessages) }} msgs</p>
        </div>
    </div>

    {{-- Revenue Chart + AI model stats --}}
    <div class="grid lg:grid-cols-3 gap-6 mb-6">
        <div class="card lg:col-span-2 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="section-title">Revenue — Last 6 Months</h2>
                <span class="text-xs text-slate-400">USD</span>
            </div>
            <canvas id="revenueChart" height="80"></canvas>
        </div>
        <div class="card p-6">
            <h2 class="section-title mb-4">AI Usage by Model <span class="text-xs font-normal text-slate-400">(30 days)</span></h2>
            @php
                $modelColors = [
                    'chiedza' => ['bg-green-500','text-green-700'],
                    'gpt'     => ['bg-blue-500','text-blue-700'],
                ];
                $totalAiMsgs = array_sum($aiModelStats) ?: 1;
            @endphp
            @forelse($aiModelStats as $model => $count)
            @php $c = $modelColors[$model] ?? ['bg-slate-400','text-slate-700']; @endphp
            <div class="mb-4">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-semibold {{ $c[1] }} flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full {{ $c[0] }}"></span>{{ ucfirst($model) }}
                    </span>
                    <span class="text-xs font-bold text-slate-700">{{ number_format($count) }}</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2">
                    <div class="{{ $c[0] }} h-2 rounded-full transition-all" style="width:{{ round($count/$totalAiMsgs*100) }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-sm text-slate-400 text-center py-6">No AI activity yet.</p>
            @endforelse
            <div class="mt-4 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.ai-tools') }}" class="w-full block text-center bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold py-2.5 rounded-lg transition">
                    Open AI Tools →
                </a>
            </div>
        </div>
    </div>

    {{-- Top Courses + Leaderboard --}}
    <div class="grid lg:grid-cols-2 gap-6 mb-6">
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h2 class="section-title">Top Courses by Enrollment</h2>
                <a href="{{ route('admin.courses.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">Manage all →</a>
            </div>
            <table class="data-table">
                <thead><tr><th>#</th><th>Course</th><th>Teacher</th><th>Enrolled</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($topCourses as $i => $course)
                    <tr>
                        <td class="w-8 text-slate-400 font-bold">{{ $i + 1 }}</td>
                        <td>
                            <p class="font-medium text-slate-800 truncate max-w-[160px]">{{ $course->title }}</p>
                            <p class="text-xs text-slate-400">{{ $course->subject }}</p>
                        </td>
                        <td class="text-xs text-slate-600">{{ $course->teacher->name ?? '—' }}</td>
                        <td class="font-semibold text-indigo-700">{{ $course->enrollments_count }}</td>
                        <td><span class="{{ $course->status === 'published' ? 'badge-green' : 'badge-amber' }}">{{ $course->status }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-8 text-slate-400 text-sm">No courses yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h2 class="section-title">Top Students by XP</h2>
                <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">View all →</a>
            </div>
            <table class="data-table">
                <thead><tr><th>Rank</th><th>Student</th><th>Level</th><th>XP</th><th>Streak</th></tr></thead>
                <tbody>
                    @forelse($leaderboard as $i => $s)
                    <tr>
                        <td class="w-8 text-center">
                            @if($i === 0)🥇@elseif($i === 1)🥈@elseif($i === 2)🥉
                            @else<span class="text-slate-400 font-bold text-sm">{{ $i+1 }}</span>@endif
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center uppercase flex-shrink-0">{{ substr($s->name, 0, 1) }}</div>
                                <span class="font-medium text-slate-800 truncate max-w-[120px]">{{ $s->name }}</span>
                            </div>
                        </td>
                        <td><span class="badge-blue">Lvl {{ $s->level }}</span></td>
                        <td class="font-semibold text-blue-700">{{ number_format($s->xp) }}</td>
                        <td class="text-xs text-orange-600 font-semibold">🔥 {{ $s->streak_days }}d</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-8 text-slate-400 text-sm">No student activity yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Users + Payments --}}
    <div class="grid lg:grid-cols-2 gap-6">
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h2 class="section-title">Recent Users</h2>
                <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">View all →</a>
            </div>
            <table class="data-table">
                <thead><tr><th>Name</th><th>Role</th><th>Joined</th><th></th></tr></thead>
                <tbody>
                    @foreach($recentUsers as $user)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold flex items-center justify-center uppercase flex-shrink-0">{{ substr($user->name, 0, 1) }}</div>
                                <div><p class="font-medium text-slate-800">{{ $user->name }}</p><p class="text-xs text-slate-400">{{ $user->email }}</p></div>
                            </div>
                        </td>
                        <td><span class="{{ match($user->role) { 'admin' => 'badge-red', 'teacher' => 'badge-purple', default => 'badge-blue' } }}">{{ $user->role }}</span></td>
                        <td class="text-xs text-slate-400">{{ $user->created_at->format('d M Y') }}</td>
                        <td><a href="{{ route('admin.users.edit', $user) }}" class="text-xs text-emerald-600 hover:underline">Edit</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h2 class="section-title">Recent Payments</h2>
            </div>
            <table class="data-table">
                <thead><tr><th>Course</th><th>Student</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($recentPayments as $payment)
                    <tr>
                        <td class="max-w-[120px] truncate font-medium text-slate-800">{{ $payment->course->title ?? '—' }}</td>
                        <td class="text-xs">{{ $payment->user->name ?? '—' }}</td>
                        <td class="font-semibold text-emerald-700">${{ number_format($payment->amount, 2) }}</td>
                        <td><span class="{{ $payment->status === 'paid' ? 'badge-green' : 'badge-amber' }}">{{ $payment->status }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-8 text-slate-400 text-sm">No payments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
    new Chart(document.getElementById('revenueChart'), {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Revenue (USD)',
                data: @json($chartData),
                backgroundColor: 'rgba(16,185,129,0.15)',
                borderColor: 'rgba(16,185,129,1)',
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => '$' + ctx.parsed.y.toLocaleString(undefined, {minimumFractionDigits:2}) } }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { callback: v => '$' + v } },
                x: { grid: { display: false } }
            }
        }
    });
    </script>
    @endpush
</x-app-layout>
