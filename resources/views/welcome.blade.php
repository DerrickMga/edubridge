<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EduBridge — Quality Education for Zimbabwe</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white antialiased font-sans">

{{-- Nav --}}
<nav class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-slate-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 flex h-16 items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-emerald-600 flex items-center justify-center shadow-sm">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <span class="font-extrabold text-xl text-slate-900 tracking-tight">EduBridge</span>
        </a>
        <div class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-600">
            <a href="{{ route('courses.index') }}" class="hover:text-slate-900">Courses</a>
            <a href="{{ route('subjects.index') }}" class="hover:text-slate-900">Subjects</a>
            <a href="{{ route('curriculum.guide') }}" class="hover:text-slate-900">Curriculum Guide</a>
            <a href="{{ route('about') }}" class="hover:text-slate-900">About</a>
        </div>
        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary">Go to dashboard</a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Log in</a>
                <a href="{{ route('register') }}" class="btn-primary">Get started free</a>
            @endauth
        </div>
    </div>
</nav>

{{-- Hero --}}
<section class="relative bg-gradient-to-br from-slate-900 via-emerald-950 to-slate-900 overflow-hidden">
    <div class="absolute inset-0 opacity-20" style="background-image:url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2260%22 height=%2260%22><circle cx=%2230%22 cy=%2230%22 r=%221%22 fill=%22%23fff%22/></svg>');"></div>
    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 py-24 md:py-32 text-center">
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold bg-emerald-700/50 text-emerald-300 border border-emerald-600/30 px-3 py-1 rounded-full mb-6">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
            Zimbabwe's Digital Learning Platform
        </span>
        <h1 class="text-4xl md:text-6xl font-extrabold text-white leading-tight mb-5 tracking-tight">
            Learn from Zimbabwe's<br class="hidden md:block">
            <span class="text-emerald-400">best educators</span>
        </h1>
        <p class="text-lg md:text-xl text-slate-300 max-w-2xl mx-auto mb-10 leading-relaxed">
            O-Level and A-Level courses taught by qualified, ZIMSEC-aligned Zimbabwean teachers. Study on your phone, anytime, anywhere, in USD or ZWG.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center mb-14">
            <a href="{{ route('register') }}" class="bg-emerald-600 hover:bg-emerald-500 text-white font-semibold px-8 py-3.5 rounded-xl shadow-lg hover:shadow-emerald-500/25 transition-all text-base">
                Start learning free →
            </a>
            <a href="{{ route('courses.index') }}" class="bg-white/10 hover:bg-white/20 text-white border border-white/20 font-semibold px-8 py-3.5 rounded-xl transition-all text-base">
                Browse courses
            </a>
        </div>
        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 max-w-2xl mx-auto">
            @foreach([
                ['O & A Level', 'All subjects'],
                ['ZWG & USD', 'Payment options'],
                ['24/7', 'Study anytime'],
                ['AI Companion', 'Chiedza AI']
            ] as [$val, $label])
            <div class="bg-white/5 border border-white/10 rounded-xl p-4">
                <p class="text-lg font-extrabold text-white">{{ $val }}</p>
                <p class="text-xs text-slate-400 mt-0.5">{{ $label }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Features --}}
<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-14">
            <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Why EduBridge?</span>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Built for Zimbabwean students</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            @foreach([
                ['🇿🇼', 'ZIMSEC Aligned', 'All content mapped to the Zimbabwe O-Level and A-Level syllabi. Every lesson is tagged by topic and exam board.'],
                ['📱', 'Mobile-First', 'Built for smartphones. Study from anywhere with minimal data, including on 3G networks across Zimbabwe.'],
                ['💸', 'ZWG & USD Payments', 'Pay in ZWG via EcoCash and Paynow, or in USD via Stripe and InnBucks. Truly accessible for every household.'],
                ['🤖', 'Chiedza AI Tutor', 'Get instant answers to any question, from solving equations to explaining history. Available 24/7.'],
                ['🎓', 'Vetted Teachers', 'All tutors are qualified, experienced Zimbabwean educators. No anonymous content creators.'],
                ['📊', 'Progress Tracking', 'Track your learning progress, see which topics you have covered, and get personalised revision suggestions.'],
            ] as [$icon, $title, $desc])
            <div class="text-center">
                <div class="w-14 h-14 rounded-2xl bg-emerald-50 flex items-center justify-center text-3xl mx-auto mb-4">{{ $icon }}</div>
                <h3 class="font-semibold text-slate-900 text-lg mb-2">{{ $title }}</h3>
                <p class="text-slate-500 text-sm leading-relaxed">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Subject tiles --}}
<section class="py-16 bg-slate-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="flex items-end justify-between mb-8">
            <div>
                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Curriculum</span>
                <h2 class="text-2xl font-extrabold text-slate-900 mt-1">Popular subjects</h2>
            </div>
            <a href="{{ route('subjects.index') }}" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700">View all →</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach([
                ['📐', 'Mathematics', 'from-blue-500 to-indigo-600', 'O & A Level'],
                ['✍️', 'English', 'from-purple-500 to-violet-600', 'O & A Level'],
                ['🧬', 'Biology', 'from-emerald-500 to-teal-600', 'O & A Level'],
                ['⚗️', 'Chemistry', 'from-teal-400 to-emerald-600', 'O & A Level'],
                ['⚡', 'Physics', 'from-cyan-500 to-blue-600', 'O & A Level'],
                ['📊', 'Business', 'from-orange-400 to-rose-500', 'O & A Level'],
                ['📜', 'History', 'from-amber-400 to-orange-500', 'O Level'],
                ['💻', 'Computer Sci.', 'from-indigo-500 to-violet-600', 'O & A Level'],
            ] as [$icon, $name, $color, $level])
            <a href="{{ route('courses.index', ['subject' => $name]) }}" class="bg-gradient-to-br {{ $color }} rounded-2xl p-5 hover:shadow-lg transition-all hover:-translate-y-0.5 group">
                <div class="text-3xl mb-3">{{ $icon }}</div>
                <p class="text-white font-bold">{{ $name }}</p>
                <p class="text-white/60 text-xs mt-0.5">{{ $level }}</p>
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="py-20 bg-emerald-700">
    <div class="max-w-2xl mx-auto text-center px-4">
        <h2 class="text-3xl font-extrabold text-white mb-4">Ready to excel in your exams?</h2>
        <p class="text-emerald-200 text-lg mb-8">Join EduBridge today. Free to sign up and built to be affordable for every Zimbabwean household.</p>
        <div class="flex gap-3 justify-center flex-wrap">
            <a href="{{ route('register') }}" class="bg-white text-emerald-700 font-bold px-8 py-3.5 rounded-xl hover:bg-emerald-50 transition-all shadow-lg">Get started free</a>
            <a href="{{ route('about') }}" class="border border-emerald-500 text-white font-semibold px-8 py-3.5 rounded-xl hover:bg-emerald-600 transition-all">Learn more</a>
        </div>
    </div>
</section>

{{-- Footer --}}
<footer class="bg-slate-900 text-slate-400 py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="grid sm:grid-cols-2 md:grid-cols-5 gap-8 mb-10">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 rounded-lg bg-emerald-600 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <span class="font-bold text-white">EduBridge</span>
                </div>
                <p class="text-xs leading-relaxed">Quality O-Level and A-Level education for every Zimbabwean student, wherever they are.</p>
            </div>
            <div>
                <p class="text-white font-semibold text-sm mb-3">Learn</p>
                <ul class="space-y-2 text-xs">
                    <li><a href="{{ route('courses.index') }}" class="hover:text-white">All Courses</a></li>
                    <li><a href="{{ route('subjects.index') }}" class="hover:text-white">Subject Library</a></li>
                    <li><a href="{{ route('curriculum.guide') }}" class="hover:text-white">Curriculum Guide</a></li>
                </ul>
            </div>
            <div>
                <p class="text-white font-semibold text-sm mb-3">Company</p>
                <ul class="space-y-2 text-xs">
                    <li><a href="{{ route('about') }}" class="hover:text-white">About Us</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-white">Become a Tutor</a></li>
                </ul>
            </div>
            <div>
                <p class="text-white font-semibold text-sm mb-3">Account</p>
                <ul class="space-y-2 text-xs">
                    <li><a href="{{ route('login') }}" class="hover:text-white">Sign In</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-white">Register Free</a></li>
                </ul>
            </div>
            <div>
                <p class="text-white font-semibold text-sm mb-3">Legal</p>
                <ul class="space-y-2 text-xs">
                    <li><a href="{{ route('terms') }}" class="hover:text-white">Terms of Service</a></li>
                    <li><a href="{{ route('privacy') }}" class="hover:text-white">Privacy Policy</a></li>
                    <li><a href="{{ route('refund') }}" class="hover:text-white">Refund Policy</a></li>
                    <li><a href="{{ route('cookies') }}" class="hover:text-white">Cookie Policy</a></li>
                    <li><a href="{{ route('acceptable-use') }}" class="hover:text-white">Acceptable Use</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-800 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <p>&copy; {{ date('Y') }} EduBridge by <a href="https://www.kmgvitallinks.co.uk" target="_blank" rel="noopener" class="hover:text-white underline">KMG Vital Links (Pvt) Ltd</a> &middot; Harare, Zimbabwe</p>
            <p>Built with ❤️ for Zimbabwe</p>
        </div>
    </div>
</footer>
</body>
</html>
