<x-app-layout>
    <x-slot name="title">{{ $user->name }}</x-slot>
    <div class="max-w-2xl mx-auto px-4 py-8">
        <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-400 hover:text-gray-600 mb-4 inline-block">&larr; Users</a>
        <div class="bg-white border border-gray-200 rounded-2xl p-8">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 rounded-full bg-green-100 text-green-700 font-extrabold text-2xl flex items-center justify-center">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ $user->name }}</h1>
                    <p class="text-gray-400 text-sm">{{ $user->email }}</p>
                </div>
                <span class="ml-auto inline-block px-3 py-1 rounded-full text-xs font-semibold
                    {{ $user->role === 'admin' ? 'bg-red-100 text-red-700' : ($user->role === 'teacher' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700') }}">
                    {{ $user->role }}
                </span>
            </div>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-gray-400">Phone</dt><dd class="font-medium text-gray-900">{{ $user->phone ?? '—' }}</dd></div>
                <div><dt class="text-gray-400">Country</dt><dd class="font-medium text-gray-900">{{ $user->country }}</dd></div>
                <div><dt class="text-gray-400">Grade</dt><dd class="font-medium text-gray-900">{{ $user->grade_level ?? '—' }}</dd></div>
                <div><dt class="text-gray-400">Status</dt><dd class="font-medium {{ $user->is_active ? 'text-green-600' : 'text-red-500' }}">{{ $user->is_active ? 'Active' : 'Suspended' }}</dd></div>
                <div><dt class="text-gray-400">Joined</dt><dd class="font-medium text-gray-900">{{ $user->created_at->format('d M Y') }}</dd></div>
            </dl>
            <div class="mt-6">
                <a href="{{ route('admin.users.edit', $user) }}" class="bg-green-700 text-white px-5 py-2 rounded-xl text-sm font-semibold hover:bg-green-800 transition">Edit user</a>
            </div>
        </div>
    </div>
</x-app-layout>
