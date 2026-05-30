<x-app-layout>
    <x-slot name="title">Admin Dashboard</x-slot>

    <div class="page-header flex items-start justify-between gap-4">
        <div>
            <h1 class="page-title">Platform Overview</h1>
            <p class="page-subtitle">EduBridge administration — {{ \Carbon\Carbon::now()->format('d M Y') }}</p>
        </div>
    </div>

    {{-- KPI Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="stat-card">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Students</p>
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z"/></svg>
                </div>
            </div>
            <p class="stat-value">{{ $studentCount }}</p>
            <p class="stat-label">Total students</p>
        </div>
        <div class="stat-card">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Teachers</p>
                <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342"/></svg>
                </div>
            </div>
            <p class="stat-value">{{ $teacherCount }}</p>
            <p class="stat-label">Active tutors</p>
        </div>
        <div class="stat-card">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Courses</p>
                <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
            </div>
            <p class="stat-value">{{ $courseCount }}</p>
            <p class="stat-label">Published courses</p>
        </div>
        <div class="stat-card">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Revenue</p>
                <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
            </div>
            <p class="stat-value">${{ number_format($totalRevenue, 0) }}</p>
            <p class="stat-label">Total revenue (USD)</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Recent Users --}}
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                <h2 class="section-title">Recent Users</h2>
                <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">View all →</a>
            </div>
            <table class="data-table">
                <thead><tr><th>Name</th><th>Role</th><th>Joined</th></tr></thead>
                <tbody>
                    @foreach($recentUsers as $user)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold flex items-center justify-center uppercase flex-shrink-0">{{ substr($user->name, 0, 1) }}</div>
                                <div>
                                    <p class="font-medium text-slate-800">{{ $user->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="{{ match($user->role) { 'admin' => 'badge-red', 'teacher' => 'badge-purple', default => 'badge-blue' } }}">{{ $user->role }}</span>
                        </td>
                        <td class="text-xs text-slate-400">{{ $user->created_at->format('d M Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Recent Payments --}}
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
                        <td class="font-semibold text-emerald-700">${{ number_format($payment->amount_usd, 2) }}</td>
                        <td><span class="{{ $payment->status === 'paid' ? 'badge-green' : 'badge-amber' }}">{{ $payment->status }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-8 text-slate-400 text-sm">No payments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
