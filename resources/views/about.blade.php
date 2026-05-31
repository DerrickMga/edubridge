<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About EduBridge</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white antialiased font-sans">

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
            <a href="{{ route('courses.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Courses</a>
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary btn-sm">Dashboard</a>
            @else
                <a href="{{ route('register') }}" class="btn-primary btn-sm">Get started</a>
            @endauth
        </div>
    </div>
</nav>

{{-- Hero --}}
<section class="bg-gradient-to-br from-slate-900 to-emerald-950 py-20 text-center">
    <div class="max-w-3xl mx-auto px-4">
        <h1 class="text-4xl font-extrabold text-white mb-4">About EduBridge</h1>
        <p class="text-slate-300 text-xl leading-relaxed">Making quality O-Level and A-Level education accessible to every Zimbabwean student, wherever they are.</p>
    </div>
</section>

{{-- Mission --}}
<section class="max-w-4xl mx-auto px-4 sm:px-6 py-16 space-y-12">
    <div class="grid md:grid-cols-2 gap-10 items-center">
        <div>
            <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Our Mission</span>
            <h2 class="text-2xl font-bold text-slate-900 mt-2 mb-4">Bridging the education gap in Zimbabwe</h2>
            <p class="text-slate-600 leading-relaxed">EduBridge was built with one goal: to ensure that every Zimbabwean learner, regardless of where they live, has access to the same quality of O-Level and A-Level instruction as students in the best urban schools.</p>
            <p class="text-slate-600 leading-relaxed mt-4">We do this by partnering with qualified, experienced Zimbabwean teachers who deliver structured, curriculum-aligned video lessons and live sessions, accessible on mobile phones with minimal data.</p>
        </div>
        <div class="space-y-4">
            @foreach([
                ['🇿🇼', 'Made for Zimbabwe', 'All content is ZIMSEC-aligned and taught by Zimbabwean educators.'],
                ['📱', 'Mobile-first', 'Optimised for smartphone learning. Works reliably on 3G across Zimbabwe.'],
                ['💸', 'Affordable', 'Pay in ZWG (EcoCash/Paynow) or USD (Stripe/InnBucks). Prices set by teachers.'],
                ['🤖', 'AI-powered', 'Chiedza AI gives every student a 24/7 personal tutor.'],
            ] as [$icon, $title, $desc])
            <div class="flex items-start gap-4">
                <span class="text-2xl">{{ $icon }}</span>
                <div>
                    <p class="font-semibold text-slate-800">{{ $title }}</p>
                    <p class="text-sm text-slate-500">{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Company --}}
    <div class="card p-8 text-center bg-slate-50">
        <h3 class="text-xl font-bold text-slate-900 mb-3">KMG Vital Links (Pvt) Ltd</h3>
        <p class="text-slate-500 leading-relaxed max-w-xl mx-auto">EduBridge is a product of KMG Vital Links, a Zimbabwean technology company focused on digital solutions that improve lives. We are headquartered in Harare, Zimbabwe.</p>
    </div>

    {{-- Team --}}
    <div>
        <h2 class="text-2xl font-bold text-slate-900 mb-8 text-center">Built for learners, by Zimbabweans</h2>
        <div class="grid sm:grid-cols-3 gap-6">
            @foreach([
                ['📚', 'Students', 'We exist for the millions of Zimbabwean students who deserve better resources.'],
                ['🎓', 'Teachers', 'Our tutors are qualified educators who earn fairly for their knowledge.'],
                ['🌍', 'Community', 'Every subscription helps fund better education across Zimbabwe.'],
            ] as [$icon, $role, $desc])
            <div class="text-center card p-6">
                <div class="text-4xl mb-3">{{ $icon }}</div>
                <h4 class="font-semibold text-slate-900 mb-2">{{ $role }}</h4>
                <p class="text-xs text-slate-500 leading-relaxed">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- CTA --}}
    <div class="text-center">
        <h3 class="text-xl font-bold text-slate-900 mb-4">Join EduBridge today</h3>
        <div class="flex gap-3 justify-center flex-wrap">
            <a href="{{ route('register') }}" class="btn-primary">Get started free</a>
            <a href="{{ route('courses.index') }}" class="btn-secondary">Browse courses</a>
        </div>
    </div>
</section>

{{-- Footer --}}
<footer class="border-t border-slate-200 bg-white py-8">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-slate-400 mb-4">
            <div class="font-bold text-slate-800">EduBridge</div>
            <div class="flex flex-wrap gap-5 justify-center">
                <a href="{{ route('subjects.index') }}" class="hover:text-slate-600">Subjects</a>
                <a href="{{ route('curriculum.guide') }}" class="hover:text-slate-600">Curriculum Guide</a>
                <a href="{{ route('courses.index') }}" class="hover:text-slate-600">Courses</a>
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
