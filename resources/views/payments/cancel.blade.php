<x-app-layout>
    <x-slot name="title">Payment Cancelled</x-slot>
    <div class="max-w-md mx-auto px-4 py-20 text-center">
        <div class="text-6xl mb-4">😕</div>
        <h1 class="text-2xl font-extrabold text-gray-900 mb-2">Payment cancelled</h1>
        <p class="text-gray-500 mb-8">No worries — nothing was charged. You can try again whenever you're ready.</p>
        <a href="{{ route('courses.index') }}" class="bg-green-700 text-white px-8 py-3 rounded-xl font-bold hover:bg-green-800 transition">Back to courses</a>
    </div>
</x-app-layout>
