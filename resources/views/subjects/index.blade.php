<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subject Library — EduBridge</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 antialiased font-sans" x-data="{ tab: 'all' }">

{{-- Nav --}}
<nav class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-slate-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 flex h-16 items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-emerald-600 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <span class="font-extrabold text-slate-900">EduBridge</span>
        </a>
        <div class="flex items-center gap-3">
            <a href="{{ route('courses.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">All courses</a>
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary btn-sm">Dashboard</a>
            @else
                <a href="{{ route('register') }}" class="btn-primary btn-sm">Get started</a>
            @endauth
        </div>
    </div>
</nav>

{{-- Hero --}}
<section class="bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-900 py-14 text-center">
    <div class="max-w-3xl mx-auto px-4">
        <span class="badge-green text-xs uppercase tracking-wide mb-4 inline-flex">Zimbabwe Curriculum</span>
        <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-3">Subject Library</h1>
        <p class="text-slate-300 text-lg">Explore every O-Level and A-Level subject taught on EduBridge, aligned to the ZIMSEC curriculum.</p>
    </div>
</section>

{{-- Content --}}
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-10">

    {{-- Tabs --}}
    <div class="flex gap-2 mb-8 border-b border-slate-200">
        @foreach(['all' => 'All Subjects', 'o_level' => 'O-Level (Form 1–4)', 'a_level' => 'A-Level (Form 5–6)'] as $key => $label)
        <button @click="tab = '{{ $key }}'"
            :class="tab === '{{ $key }}' ? 'border-emerald-600 text-emerald-700 font-semibold' : 'border-transparent text-slate-500 hover:text-slate-700'"
            class="border-b-2 pb-3 px-1 mr-6 text-sm transition-all">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- O-Level grid --}}
    <div x-show="tab === 'all' || tab === 'o_level'" class="mb-12">
        <div class="flex items-center gap-3 mb-5">
            <h2 class="section-title text-lg">O-Level Subjects</h2>
            <span class="badge-slate">{{ count($subjects['o_level']) }}</span>
            <span class="text-xs text-slate-400">ZIMSEC / Cambridge · Form 1–4</span>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($subjects['o_level'] as $sub)
            <div class="card card-hover p-5">
                <div class="flex items-start gap-3 mb-3">
                    <span class="text-3xl">{{ $sub['icon'] }}</span>
                    <div class="flex-1">
                        <h3 class="font-semibold text-slate-900">{{ $sub['name'] }}</h3>
                        <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded">{{ $sub['exam'] }}</span>
                    </div>
                </div>
                <p class="text-xs text-slate-500 mb-3 leading-relaxed">{{ $sub['desc'] }}</p>
                <div class="flex flex-wrap gap-1 mb-4">
                    @foreach(array_slice($sub['topics'], 0, 4) as $topic)
                    <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">{{ $topic }}</span>
                    @endforeach
                    @if(count($sub['topics']) > 4)
                    <span class="text-[10px] bg-slate-100 text-slate-400 px-2 py-0.5 rounded-full">+{{ count($sub['topics']) - 4 }} more</span>
                    @endif
                </div>
                <a href="{{ route('courses.index', ['subject' => $sub['name']]) }}" class="btn-secondary btn-sm w-full justify-center">
                    Browse courses →
                </a>
            </div>
            @endforeach
        </div>
    </div>

    {{-- A-Level grid --}}
    <div x-show="tab === 'all' || tab === 'a_level'">
        <div class="flex items-center gap-3 mb-5">
            <h2 class="section-title text-lg">A-Level Subjects</h2>
            <span class="badge-slate">{{ count($subjects['a_level']) }}</span>
            <span class="text-xs text-slate-400">ZIMSEC / Cambridge · Form 5–6</span>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($subjects['a_level'] as $sub)
            <div class="card card-hover p-5">
                <div class="flex items-start gap-3 mb-3">
                    <span class="text-3xl">{{ $sub['icon'] }}</span>
                    <div class="flex-1">
                        <h3 class="font-semibold text-slate-900">{{ $sub['name'] }}</h3>
                        <span class="text-[10px] font-semibold text-purple-600 bg-purple-50 px-1.5 py-0.5 rounded">A-Level · {{ $sub['exam'] }}</span>
                    </div>
                </div>
                <p class="text-xs text-slate-500 mb-3 leading-relaxed">{{ $sub['desc'] }}</p>
                <div class="flex flex-wrap gap-1 mb-4">
                    @foreach(array_slice($sub['topics'], 0, 4) as $topic)
                    <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full">{{ $topic }}</span>
                    @endforeach
                    @if(count($sub['topics']) > 4)
                    <span class="text-[10px] bg-slate-100 text-slate-400 px-2 py-0.5 rounded-full">+{{ count($sub['topics']) - 4 }} more</span>
                    @endif
                </div>
                <a href="{{ route('courses.index', ['subject' => $sub['name']]) }}" class="btn-secondary btn-sm w-full justify-center">
                    Browse courses →
                </a>
            </div>
            @endforeach
        </div>
    </div>

    {{-- CTA --}}
    <div class="mt-16 text-center card p-10 bg-gradient-to-br from-emerald-50 to-teal-50 border-emerald-200">
        <h3 class="text-xl font-bold text-slate-900 mb-2">Can't find your subject?</h3>
        <p class="text-slate-500 mb-6">We're adding new courses every week. Let us know what you need.</p>
        <a href="{{ route('register') }}" class="btn-primary">Join EduBridge free</a>
    </div>
</div>

{{-- Footer --}}
<footer class="border-t border-slate-200 bg-white mt-16 py-8">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-slate-400 mb-4">
            <div class="font-bold text-slate-800">EduBridge</div>
            <div class="flex flex-wrap gap-5 justify-center">
                <a href="{{ route('curriculum.guide') }}" class="hover:text-slate-600">Curriculum Guide</a>
                <a href="{{ route('courses.index') }}" class="hover:text-slate-600">All Courses</a>
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
