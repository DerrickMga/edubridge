<x-app-layout>
    <x-slot name="title">Users</x-slot>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Users</h1>
        </div>

        {{-- Filters --}}
        <form method="GET" class="flex gap-3 mb-6">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email..." class="border border-gray-300 rounded-xl px-4 py-2 text-sm w-64 focus:ring-2 focus:ring-green-500 outline-none">
            <select name="role" class="border border-gray-300 rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                <option value="">All roles</option>
                <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>Student</option>
                <option value="teacher" {{ request('role') === 'teacher' ? 'selected' : '' }}>Teacher</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
            <button class="bg-green-700 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-green-800 transition">Filter</button>
        </form>

        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-3 text-gray-500 font-medium">Name</th>
                        <th class="text-left px-6 py-3 text-gray-500 font-medium">Email</th>
                        <th class="text-left px-6 py-3 text-gray-500 font-medium">Role</th>
                        <th class="text-left px-6 py-3 text-gray-500 font-medium">Joined</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $user->email }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $user->role === 'admin' ? 'bg-red-100 text-red-700' : ($user->role === 'teacher' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700') }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-gray-400">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-3 text-right">
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-green-700 hover:text-green-900 font-medium text-xs">Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $users->links() }}</div>
    </div>
</x-app-layout>
