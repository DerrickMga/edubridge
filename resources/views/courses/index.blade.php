<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>All Courses — EduBridge</title>
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
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary btn-sm">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Log in</a>
                <a href="{{ route('register') }}" class="btn-primary btn-sm">Get started</a>
            @endauth
        </div>
    </div>
</nav>

{{-- Hero --}}
<section class="bg-gradient-to-br from-slate-900 to-emerald-900 py-14 text-center">
    <div class="max-w-3xl mx-auto px-4">
        <span class="badge-green text-xs uppercase tracking-wide mb-4 inline-flex">Zimbabwe Curriculum</span>
        <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-3">Browse Courses</h1>
        <p class="text-emerald-200 text-lg mb-6">All subjects taught by vetted Zimbabwean educators, covering O-Level and A-Level.</p>
        <form method="GET" class="max-w-md mx-auto flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search subject or course…" class="form-input flex-1 bg-white/10 border-white/20 text-white placeholder-white/50 focus:bg-white focus:text-slate-900 focus:placeholder-slate-400 transition-all" />
            <button type="submit" class="btn-primary">Search</button>
        </form>
    </div>
</section>

{{-- Content --}}
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">

    {{-- Filter tabs --}}
    <div class="flex items-center gap-2 mb-8 flex-wrap">
        @foreach(['' => 'All', 'Mathematics' => 'Maths', 'English Language' => 'English', 'Biology' => 'Biology', 'Chemistry' => 'Chemistry', 'Physics' => 'Physics', 'Geography' => 'Geography', 'History' => 'History', 'Business Studies' => 'Business', 'Accounting' => 'Accounting', 'Computer Science' => 'Computer Science'] as $val => $label)
        <a href="{{ route('courses.index', array_merge(request()->except('subject','page'), $val ? ['subject' => $val] : [])) }}"
            class="{{ request('subject') === $val || (request('subject') === null && $val === '') ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:border-emerald-400 hover:text-emerald-700' }} px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Results count --}}
    <p class="text-sm text-slate-400 mb-6">Showing <span class="text-slate-700 font-semibold">{{ $courses->total() }}</span> courses</p>

    @if($courses->isEmpty())
    <div class="empty-state">
        <span class="empty-state-icon">🔍</span>
        <p class="empty-state-title">No courses found</p>
        <p class="empty-state-text">Try a different search or subject filter.</p>
        <a href="{{ route('courses.index') }}" class="btn-secondary">Clear filters</a>
    </div>
    @else
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($courses as $course)
        @php
            $color = match(strtolower($course->subject ?? '')) {
                'mathematics','maths','a-level mathematics' => 'from-blue-500 to-indigo-600',
                'english language','a-level english literature' => 'from-purple-500 to-violet-600',
                'biology','combined science','a-level biology' => 'from-emerald-500 to-teal-600',
                'chemistry','a-level chemistry' => 'from-teal-400 to-emerald-600',
                'physics','a-level physics' => 'from-cyan-500 to-blue-600',
                'history','a-level history' => 'from-amber-400 to-orange-500',
                'geography','a-level geography' => 'from-teal-500 to-cyan-600',
                'business studies','commerce','a-level business studies' => 'from-orange-400 to-rose-500',
                'accounting','a-level accounting' => 'from-rose-400 to-pink-600',
                'computer science','a-level computer science' => 'from-indigo-500 to-violet-600',
                default => 'from-slate-500 to-slate-700',
            };
            $enrolled = auth()->check() && auth()->user()->enrollments->contains('id', $course->id);
        @endphp
        <a href="{{ route('courses.show', $course) }}" class="card hover:shadow-card-hover transition-all duration-200 overflow-hidden flex flex-col group">
            <div class="h-32 bg-gradient-to-br {{ $color }} flex flex-col justify-between p-4 relative">
                <div class="flex items-center justify-between">
                    <span class="badge bg-white/20 text-white text-xs">{{ $course->subject }}</span>
                    @if($enrolled)
                        <span class="badge bg-white/30 text-white text-xs font-semibold">✓ Enrolled</span>
                    @endif
                </div>
                <span class="text-xs text-white/70">{{ $course->grade_level }}</span>
            </div>
            <div class="p-5 flex flex-col flex-1">
                <h3 class="font-semibold text-slate-900 leading-snug mb-1 group-hover:text-emerald-700 transition">{{ $course->title }}</h3>
                @if($course->description)
                <p class="text-xs text-slate-400 mb-3 line-clamp-2 flex-1">{{ $course->description }}</p>
                @else
                <div class="flex-1"></div>
                @endif
                <div class="flex items-center justify-between mb-4 text-xs text-slate-500">
                    <span>{{ $course->teacher->name }}</span>
                    <span>{{ $course->lessons_count ?? $course->lessons->count() }} lessons</span>
                </div>
                <div class="flex items-center justify-between">
                    <div>
                        @if($course->price_usd > 0)
                        <span class="font-bold text-emerald-700">${{ number_format($course->price_usd, 0) }}</span>
                        <span class="text-xs text-slate-400"> / ZWG {{ number_format($course->price_zwg ?? 0, 0) }}</span>
                        @else
                        <span class="badge-green">Free</span>
                        @endif
                    </div>
                    @if($enrolled)
                        <span class="btn-secondary btn-sm pointer-events-none text-xs">Continue →</span>
                    @else
                        <span class="btn-primary btn-sm text-xs">View course</span>
                    @endif
                </div>
            </div>
        </a>
        @endforeach
    </div>
    <div class="mt-8">{{ $courses->withQueryString()->links() }}</div>
    @endif
</div>

{{-- Footer --}}
<footer class="border-t border-slate-200 bg-white mt-16 py-8">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-slate-400 mb-4">
            <div class="font-bold text-slate-800">EduBridge</div>
            <div class="flex flex-wrap gap-5 justify-center">
                <a href="{{ route('subjects.index') }}" class="hover:text-slate-600">Subjects</a>
                <a href="{{ route('curriculum.guide') }}" class="hover:text-slate-600">Curriculum Guide</a>
                <a href="{{ route('about') }}" class="hover:text-slate-600">About</a>
            </div>
            <div>&copy; {{ date('Y') }} EduBridge &middot; <a href="https://www.kmgvitallinks.co.uk" target="_blank" rel="noopener" class="hover:text-slate-600 underline">KMG Vital Links</a></div>
        </div>
        <div class="flex flex-wrap gap-4 justify-center text-xs text-slate-400 border-t border-slate-100 pt-4">
            <a href="{{ route('terms') }}" class="hover:text-slate-600">Terms of Service</a>
            <a href="{{ route('privacy') }}" class="hover:text-slate-600">Privacy Policy</a>
            <a href="{{ route('refund') }}" class="hover:text-slate-600">Refund Policy</a>
            <a href="{{ route('cookies') }}" class="hover:text-slate-600">Cookie Policy</a>
            <a href="{{ route('acceptable-use') }}" class="hover:text-slate-600">Acceptable Use</a>
        </div>
    </div>
</footer>
</body>
</html>
