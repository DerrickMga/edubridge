<x-app-layout>
    <x-slot name="title">Admin Dashboard</x-slot>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Admin Dashboard</h1>

        {{-- Stats --}}
        <div class="grid sm:grid-cols-4 gap-6 mb-10">
            @foreach([
                ['Students', $stats['students'], 'bg-blue-50 text-blue-700'],
                ['Teachers', $stats['teachers'], 'bg-purple-50 text-purple-700'],
                ['Courses', $stats['courses'], 'bg-green-50 text-green-700'],
                ['Revenue (USD)', '$'.number_format($stats['revenue'],2), 'bg-yellow-50 text-yellow-700'],
            ] as [$label, $value, $cls])
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <div class="text-3xl font-extrabold {{ $cls }} rounded-lg px-3 py-1 inline-block mb-2">{{ $value }}</div>
                <div class="text-gray-500 text-sm">{{ $label }}</div>
            </div>
            @endforeach
        </div>

        <div class="grid lg:grid-cols-2 gap-8">
            {{-- Recent users --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-bold text-gray-900">Recent Users</h2>
                    <a href="{{ route('admin.users.index') }}" class="text-green-700 text-sm font-medium">View all</a>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recentUsers as $user)
                        <tr>
                            <td class="py-2 font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="py-2 text-gray-400">{{ $user->email }}</td>
                            <td class="py-2">
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $user->role === 'admin' ? 'bg-red-100 text-red-700' : ($user->role === 'teacher' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700') }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Recent payments --}}
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <h2 class="font-bold text-gray-900 mb-4">Recent Payments</h2>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recentPayments as $payment)
                        <tr>
                            <td class="py-2 font-medium text-gray-900">{{ $payment->user->name }}</td>
                            <td class="py-2 text-gray-400">{{ $payment->course?->title ?? '—' }}</td>
                            <td class="py-2 font-semibold text-gray-900">${{ number_format($payment->amount,2) }}</td>
                            <td class="py-2">
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $payment->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $payment->status }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
