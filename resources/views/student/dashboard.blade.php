<x-app-layout>
    <x-slot name="title">Student Dashboard</x-slot>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Welcome back, {{ auth()->user()->name }}!</h1>
        <p class="text-gray-500 mb-8">Continue where you left off.</p>

        {{-- Quick actions --}}
        <div class="grid sm:grid-cols-3 gap-4 mb-10">
            <a href="{{ route('student.companion.index') }}" class="bg-green-700 text-white rounded-2xl p-6 hover:bg-green-800 transition">
                <div class="text-3xl mb-2">🤖</div>
                <div class="font-bold text-lg">Ask Chiedza</div>
                <div class="text-green-200 text-sm mt-1">Your AI study companion</div>
            </a>
            <a href="{{ route('courses.index') }}" class="bg-white border border-gray-200 rounded-2xl p-6 hover:shadow-md transition">
                <div class="text-3xl mb-2">📚</div>
                <div class="font-bold text-lg text-gray-900">Browse Courses</div>
                <div class="text-gray-400 text-sm mt-1">Find new subjects</div>
            </a>
            <div class="bg-white border border-gray-200 rounded-2xl p-6">
                <div class="text-3xl mb-2">📅</div>
                <div class="font-bold text-lg text-gray-900">Upcoming Classes</div>
                <div class="text-gray-400 text-sm mt-1">
                    @if($upcomingSessions->count())
                        {{ $upcomingSessions->count() }} scheduled
                    @else
                        None scheduled
                    @endif
                </div>
            </div>
        </div>

        {{-- Enrolled courses --}}
        <h2 class="text-xl font-bold text-gray-900 mb-4">My Courses</h2>
        @if($enrollments->isEmpty())
            <div class="bg-white border border-dashed border-gray-300 rounded-2xl p-12 text-center text-gray-400">
                <p class="text-lg mb-4">You haven't enrolled in any courses yet.</p>
                <a href="{{ route('courses.index') }}" class="bg-green-700 text-white px-6 py-3 rounded-xl font-semibold hover:bg-green-800 transition">Browse courses</a>
            </div>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                @foreach($enrollments as $course)
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden hover:shadow-md transition">
                    <div class="bg-green-100 h-32 flex items-center justify-center text-5xl">📖</div>
                    <div class="p-5">
                        <div class="text-xs text-green-700 font-semibold uppercase tracking-wide mb-1">{{ $course->subject }}</div>
                        <h3 class="font-bold text-gray-900 mb-1">{{ $course->title }}</h3>
                        <p class="text-xs text-gray-400">{{ $course->teacher->name }} &middot; {{ $course->lessons->count() }} lessons</p>
                    </div>
                </div>
                @endforeach
            </div>
        @endif

        {{-- Upcoming live sessions --}}
        @if($upcomingSessions->isNotEmpty())
        <h2 class="text-xl font-bold text-gray-900 mb-4">Upcoming Live Classes</h2>
        <div class="space-y-3">
            @foreach($upcomingSessions as $session)
            <div class="bg-white border border-gray-200 rounded-xl p-4 flex items-center justify-between">
                <div>
                    <div class="font-semibold text-gray-900">{{ $session->title }}</div>
                    <div class="text-sm text-gray-400">{{ $session->course->title }} &middot; {{ $session->scheduled_at->format('D d M, g:ia') }}</div>
                </div>
                <a href="{{ $session->meeting_url }}" target="_blank" class="bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-800 transition">
                    Join
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</x-app-layout>
