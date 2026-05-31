<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $course->title }} — EduBridge</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 antialiased font-sans">

{{-- Nav --}}
<nav class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-slate-200 shadow-sm">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 flex h-16 items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-emerald-600 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <span class="font-extrabold text-slate-900">EduBridge</span>
        </a>
        <div class="flex items-center gap-3">
            <a href="{{ route('courses.index') }}" class="text-sm text-slate-500 hover:text-slate-800">&larr; All courses</a>
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary btn-sm">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Log in</a>
                <a href="{{ route('register') }}" class="btn-primary btn-sm">Get started</a>
            @endauth
        </div>
    </div>
</nav>

@php
    $color = match(strtolower($course->subject ?? '')) {
        'mathematics','maths','a-level mathematics' => 'from-blue-600 to-indigo-700',
        'english language','a-level english literature' => 'from-purple-600 to-violet-700',
        'biology','combined science','a-level biology' => 'from-emerald-600 to-teal-700',
        'chemistry','a-level chemistry' => 'from-teal-500 to-emerald-700',
        'physics','a-level physics' => 'from-cyan-600 to-blue-700',
        'history','a-level history' => 'from-amber-500 to-orange-600',
        'geography','a-level geography' => 'from-teal-600 to-cyan-700',
        'business studies','commerce' => 'from-orange-500 to-rose-600',
        'accounting' => 'from-rose-500 to-pink-700',
        'computer science' => 'from-indigo-600 to-violet-700',
        default => 'from-slate-600 to-slate-800',
    };
@endphp

{{-- Hero --}}
<section class="bg-gradient-to-br {{ $color }} py-14">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">
        <div class="flex flex-col md:flex-row md:items-end gap-8">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-3">
                    <span class="badge bg-white/20 text-white text-xs">{{ $course->subject }}</span>
                    @if($course->grade_level)
                        <span class="badge bg-white/15 text-white/80 text-xs">{{ $course->grade_level }}</span>
                    @endif
                </div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-white leading-tight mb-3">{{ $course->title }}</h1>
                @if($course->description)
                    <p class="text-white/80 text-lg mb-4 max-w-2xl">{{ $course->description }}</p>
                @endif
                <div class="flex items-center gap-4 text-white/70 text-sm">
                    <span>👩‍🏫 {{ $course->teacher->name }}</span>
                    <span>📚 {{ $course->lessons_count }} lessons</span>
                </div>
            </div>

            {{-- Enrollment card --}}
            <div class="bg-white rounded-2xl shadow-xl p-6 w-full md:w-72 shrink-0">
                @if($isEnrolled)
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-emerald-600 font-semibold text-sm">✅ You're enrolled</span>
                    </div>
                    @if($continueLesson)
                        <a href="{{ route('student.lessons.show', $continueLesson) }}"
                           class="w-full block text-center btn-primary py-3 font-semibold text-sm">
                            Continue Learning →
                        </a>
                    @else
                        <a href="{{ route('student.dashboard') }}"
                           class="w-full block text-center btn-secondary py-3 font-semibold text-sm">
                            Go to Dashboard
                        </a>
                    @endif
                    <p class="text-xs text-center text-slate-400 mt-3">Keep up the great work! 🎓</p>
                @else
                    {{-- Price --}}
                    <div class="mb-4">
                        @if(($course->price_usd ?? 0) > 0)
                            <div class="text-3xl font-extrabold text-slate-900">${{ number_format($course->price_usd, 0) }}</div>
                            @if($course->price_zwg)
                                <div class="text-sm text-slate-500">or ZWG {{ number_format($course->price_zwg, 0) }}</div>
                            @endif
                        @else
                            <div class="text-2xl font-extrabold text-emerald-600">Free</div>
                            <div class="text-xs text-slate-400">No payment required</div>
                        @endif
                    </div>

                    @auth
                        <form action="{{ route('student.courses.enroll', $course) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full btn-primary py-3 font-semibold text-sm">
                                @if(($course->price_usd ?? 0) > 0)
                                    Enrol Now — ${{ number_format($course->price_usd, 0) }}
                                @else
                                    Enrol for Free
                                @endif
                            </button>
                        </form>
                    @else
                        <a href="{{ route('register') }}" class="w-full block text-center btn-primary py-3 font-semibold text-sm">
                            Sign up to Enrol
                        </a>
                        <p class="text-xs text-center text-slate-400 mt-2">
                            Already have an account? <a href="{{ route('login') }}" class="text-emerald-600 hover:underline">Log in</a>
                        </p>
                    @endauth

                    <ul class="mt-4 space-y-1.5 text-xs text-slate-500">
                        <li>✓ {{ $course->lessons_count }} structured lessons</li>
                        <li>✓ Access on any device</li>
                        <li>✓ AI study companion included</li>
                        @if($course->liveSessions()->exists())
                            <li>✓ Live sessions with teacher</li>
                        @endif
                    </ul>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- Flash messages --}}
@if(session('success'))
    <div class="max-w-5xl mx-auto px-4 sm:px-6 pt-4">
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
    </div>
@endif

{{-- Body --}}
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-10 grid md:grid-cols-3 gap-8">

    {{-- Lessons list --}}
    <div class="md:col-span-2">
        <h2 class="text-xl font-bold text-slate-900 mb-4">Course Content</h2>
        @forelse($course->lessons as $i => $lesson)
        <div class="flex items-start gap-4 bg-white border border-slate-200 rounded-xl px-4 py-3 mb-3 hover:border-emerald-300 transition">
            <div class="w-8 h-8 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold text-sm flex items-center justify-center shrink-0 mt-0.5">{{ $i + 1 }}</div>
            <div class="flex-1">
                <div class="font-medium text-slate-900 text-sm">{{ $lesson->title }}</div>
                @if($lesson->description)
                    <div class="text-xs text-slate-400 mt-0.5 line-clamp-1">{{ $lesson->description }}</div>
                @endif
            </div>
            @if($isEnrolled)
                <a href="{{ route('student.lessons.show', $lesson) }}" class="text-xs text-emerald-600 hover:underline font-medium shrink-0">Study →</a>
            @else
                <span class="text-xs text-slate-300">🔒</span>
            @endif
        </div>
        @empty
            <div class="text-sm text-slate-400">No lessons published yet.</div>
        @endforelse
    </div>

    {{-- Sidebar --}}
    <div class="space-y-5">
        {{-- About the teacher --}}
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h3 class="font-semibold text-slate-900 text-sm mb-3">About the Teacher</h3>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                    {{ strtoupper(substr($course->teacher->name, 0, 1)) }}
                </div>
                <div>
                    <div class="font-medium text-slate-900 text-sm">{{ $course->teacher->name }}</div>
                    <div class="text-xs text-slate-400">EduBridge Educator</div>
                </div>
            </div>
        </div>

        {{-- Course details --}}
        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <h3 class="font-semibold text-slate-900 text-sm mb-3">Course Details</h3>
            <div class="space-y-2 text-xs text-slate-600">
                <div class="flex justify-between"><span>Subject</span><span class="font-medium text-slate-800">{{ $course->subject }}</span></div>
                @if($course->grade_level)
                    <div class="flex justify-between"><span>Level</span><span class="font-medium text-slate-800">{{ $course->grade_level }}</span></div>
                @endif
                <div class="flex justify-between"><span>Lessons</span><span class="font-medium text-slate-800">{{ $course->lessons_count }}</span></div>
            </div>
        </div>

        {{-- Browse other subjects --}}
        <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-5">
            <h3 class="font-semibold text-emerald-900 text-sm mb-2">Browse by Subject</h3>
            <a href="{{ route('subjects.index') }}" class="text-xs text-emerald-700 hover:underline font-medium">View all subjects →</a>
        </div>
    </div>
</div>

</body>
</html>
