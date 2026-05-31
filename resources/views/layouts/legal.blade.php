<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — EduBridge</title>
    <meta name="description" content="@yield('meta-description', 'EduBridge legal information — KMG Vital Links (Pvt) Ltd')">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 antialiased font-sans">

{{-- Nav --}}
<nav class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-slate-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 flex h-16 items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-lg bg-emerald-600 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <span class="font-extrabold text-slate-900">EduBridge</span>
        </a>
        <div class="hidden sm:flex items-center gap-4 text-xs text-slate-500">
            <span class="font-medium text-slate-400 uppercase tracking-wider">Legal &amp; Policies</span>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('courses.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Courses</a>
            @auth
                <a href="{{ route('dashboard') }}" class="text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg transition-colors">Dashboard</a>
            @else
                <a href="{{ route('register') }}" class="text-sm font-semibold bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg transition-colors">Get started</a>
            @endauth
        </div>
    </div>
</nav>

{{-- Hero --}}
<div class="bg-gradient-to-br from-slate-900 to-slate-800 py-12 text-center">
    <div class="max-w-3xl mx-auto px-4">
        <p class="text-xs font-semibold text-emerald-400 uppercase tracking-widest mb-2">Legal &amp; Policies</p>
        <h1 class="text-3xl md:text-4xl font-extrabold text-white">@yield('hero-title')</h1>
        <p class="text-slate-400 text-sm mt-3">Last updated: @yield('last-updated', 'May 2026') &nbsp;·&nbsp; EduBridge by KMG Vital Links (Pvt) Ltd</p>
    </div>
</div>

{{-- Content layout --}}
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
    <div class="flex flex-col lg:flex-row gap-10">

        {{-- Sidebar --}}
        <aside class="lg:w-56 shrink-0">
            <div class="lg:sticky lg:top-24">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">All Policies</p>
                <nav class="space-y-1 text-sm">
                    <a href="{{ route('terms') }}"
                       class="flex items-center gap-2 px-3 py-2 rounded-lg {{ request()->routeIs('terms') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                        📄 Terms of Service
                    </a>
                    <a href="{{ route('privacy') }}"
                       class="flex items-center gap-2 px-3 py-2 rounded-lg {{ request()->routeIs('privacy') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                        🔒 Privacy Policy
                    </a>
                    <a href="{{ route('refund') }}"
                       class="flex items-center gap-2 px-3 py-2 rounded-lg {{ request()->routeIs('refund') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                        💳 Refund Policy
                    </a>
                    <a href="{{ route('cookies') }}"
                       class="flex items-center gap-2 px-3 py-2 rounded-lg {{ request()->routeIs('cookies') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                        🍪 Cookie Policy
                    </a>
                    <a href="{{ route('acceptable-use') }}"
                       class="flex items-center gap-2 px-3 py-2 rounded-lg {{ request()->routeIs('acceptable-use') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:bg-slate-100' }}">
                        ✅ Acceptable Use
                    </a>
                </nav>

                <div class="mt-8 p-4 bg-white rounded-xl border border-slate-200 text-xs text-slate-500 space-y-2">
                    <p class="font-semibold text-slate-700">KMG Vital Links (Pvt) Ltd</p>
                    <p>Harare, Zimbabwe</p>
                    <a href="https://www.kmgvitallinks.co.uk" target="_blank" rel="noopener"
                       class="flex items-center gap-1 text-emerald-600 hover:underline font-medium">
                        kmgvitallinks.co.uk ↗
                    </a>
                    <a href="mailto:legal@kmgvitallinks.co.uk" class="flex items-center gap-1 text-emerald-600 hover:underline">
                        legal@kmgvitallinks.co.uk
                    </a>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <article class="flex-1 min-w-0 bg-white rounded-2xl border border-slate-200 shadow-sm p-8 md:p-10 prose prose-slate prose-sm md:prose-base max-w-none
            prose-headings:font-bold prose-headings:text-slate-900
            prose-h2:text-xl prose-h2:mt-10 prose-h2:mb-3 prose-h2:pb-2 prose-h2:border-b prose-h2:border-slate-100
            prose-h3:text-base prose-h3:mt-6 prose-h3:mb-2
            prose-a:text-emerald-600 prose-a:no-underline hover:prose-a:underline
            prose-li:my-0.5 prose-p:text-slate-600 prose-p:leading-relaxed">
            @yield('content')
        </article>

    </div>
</div>

{{-- Footer --}}
<footer class="bg-slate-900 text-slate-400 py-10 mt-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="grid sm:grid-cols-2 md:grid-cols-4 gap-8 mb-8">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-6 h-6 rounded-md bg-emerald-600 flex items-center justify-center">
                        <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <span class="font-bold text-white text-sm">EduBridge</span>
                </div>
                <p class="text-xs leading-relaxed">Zimbabwe's AI-powered O &amp; A Level learning platform.</p>
                <a href="https://www.kmgvitallinks.co.uk" target="_blank" rel="noopener"
                   class="inline-block mt-2 text-xs text-emerald-400 hover:text-emerald-300">KMG Vital Links ↗</a>
            </div>
            <div>
                <p class="text-white font-semibold text-xs uppercase tracking-widest mb-3">Learn</p>
                <ul class="space-y-1.5 text-xs">
                    <li><a href="{{ route('courses.index') }}" class="hover:text-white">All Courses</a></li>
                    <li><a href="{{ route('subjects.index') }}" class="hover:text-white">Subjects</a></li>
                    <li><a href="{{ route('curriculum.guide') }}" class="hover:text-white">Curriculum Guide</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-white">About EduBridge</a></li>
                </ul>
            </div>
            <div>
                <p class="text-white font-semibold text-xs uppercase tracking-widest mb-3">Legal</p>
                <ul class="space-y-1.5 text-xs">
                    <li><a href="{{ route('terms') }}" class="hover:text-white">Terms of Service</a></li>
                    <li><a href="{{ route('privacy') }}" class="hover:text-white">Privacy Policy</a></li>
                    <li><a href="{{ route('refund') }}" class="hover:text-white">Refund Policy</a></li>
                    <li><a href="{{ route('cookies') }}" class="hover:text-white">Cookie Policy</a></li>
                    <li><a href="{{ route('acceptable-use') }}" class="hover:text-white">Acceptable Use</a></li>
                </ul>
            </div>
            <div>
                <p class="text-white font-semibold text-xs uppercase tracking-widest mb-3">KMG Group</p>
                <ul class="space-y-1.5 text-xs">
                    <li><a href="https://www.kmgvitallinks.co.uk" target="_blank" rel="noopener" class="hover:text-white flex items-center gap-1">KMG Vital Links ↗</a></li>
                    <li><a href="https://edu.kmgvitallinks.co.uk" class="hover:text-white">EduBridge</a></li>
                    <li><a href="mailto:info@kmgvitallinks.co.uk" class="hover:text-white">info@kmgvitallinks.co.uk</a></li>
                    <li><a href="mailto:legal@kmgvitallinks.co.uk" class="hover:text-white">legal@kmgvitallinks.co.uk</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-800 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <p>&copy; {{ date('Y') }} EduBridge &middot; KMG Vital Links (Pvt) Ltd &middot; Harare, Zimbabwe</p>
            <div class="flex gap-4">
                <a href="{{ route('terms') }}" class="hover:text-white">Terms</a>
                <a href="{{ route('privacy') }}" class="hover:text-white">Privacy</a>
                <a href="{{ route('refund') }}" class="hover:text-white">Refunds</a>
                <a href="{{ route('cookies') }}" class="hover:text-white">Cookies</a>
            </div>
        </div>
    </div>
</footer>

</body>
</html>
