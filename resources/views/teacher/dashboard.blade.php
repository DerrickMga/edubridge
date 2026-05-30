<x-app-layout>
    <x-slot name="title">Teacher Dashboard</x-slot>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Teacher Dashboard</h1>

        {{-- Stats --}}
        <div class="grid sm:grid-cols-3 gap-6 mb-10">
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <div class="text-3xl font-extrabold text-green-700">{{ $courses->count() }}</div>
                <div class="text-gray-500 mt-1">Courses</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <div class="text-3xl font-extrabold text-green-700">{{ $courses->sum('enrollments_count') }}</div>
                <div class="text-gray-500 mt-1">Total Students</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <div class="text-3xl font-extrabold text-green-700">${{ number_format($earnings, 2) }}</div>
                <div class="text-gray-500 mt-1">Total Earnings</div>
            </div>
        </div>

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-900">My Courses</h2>
            <a href="{{ route('teacher.courses.create') }}" class="bg-green-700 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-green-800 transition">+ New Course</a>
        </div>

        @if($courses->isEmpty())
            <div class="bg-white border border-dashed border-gray-300 rounded-2xl p-12 text-center text-gray-400">
                <p class="mb-4">You haven't created any courses yet.</p>
                <a href="{{ route('teacher.courses.create') }}" class="bg-green-700 text-white px-6 py-3 rounded-xl font-semibold hover:bg-green-800 transition">Create first course</a>
            </div>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($courses as $course)
                <a href="{{ route('teacher.courses.show', $course) }}" class="bg-white border border-gray-200 rounded-2xl overflow-hidden hover:shadow-md transition block">
                    <div class="bg-green-100 h-28 flex items-center justify-center text-4xl">📖</div>
                    <div class="p-5">
                        <span class="text-xs font-semibold uppercase text-{{ $course->status === 'published' ? 'green' : 'yellow' }}-600">{{ $course->status }}</span>
                        <h3 class="font-bold text-gray-900 mt-1">{{ $course->title }}</h3>
                        <p class="text-xs text-gray-400 mt-1">{{ $course->enrollments_count }} students enrolled</p>
                    </div>
                </a>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
