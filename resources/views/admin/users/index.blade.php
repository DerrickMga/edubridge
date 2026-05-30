<x-app-layout>
    <x-slot name="title">Users</x-slot>

    <div class="page-header flex items-center justify-between gap-4">
        <div>
            <h1 class="page-title">Users</h1>
            <p class="page-subtitle">Manage all accounts on the platform.</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="card p-4 mb-6 flex flex-col sm:flex-row gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email…" class="form-input flex-1" />
        <select name="role" class="form-select w-full sm:w-40">
            <option value="">All roles</option>
            @foreach(['student','teacher','admin'] as $r)
            <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
            Filter
        </button>
    </form>

    <div class="card overflow-hidden">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full text-sm font-bold flex items-center justify-center uppercase flex-shrink-0
                                {{ match($user->role) { 'admin' => 'bg-red-100 text-red-700', 'teacher' => 'bg-purple-100 text-purple-700', default => 'bg-blue-100 text-blue-700' } }}">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="font-semibold text-slate-800">{{ $user->name }}</p>
                                <p class="text-xs text-slate-400">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="{{ match($user->role) { 'admin' => 'badge-red', 'teacher' => 'badge-purple', default => 'badge-blue' } }}">
                            {{ $user->role }}
                        </span>
                    </td>
                    <td class="text-sm text-slate-500">{{ $user->created_at->format('d M Y') }}</td>
                    <td class="text-right">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn-secondary btn-sm">Edit</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-12 text-slate-400">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-4 border-t border-slate-100">
            {{ $users->withQueryString()->links() }}
        </div>
    </div>
</x-app-layout>
