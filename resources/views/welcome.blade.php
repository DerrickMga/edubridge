<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduBridge — AI-Powered Learning for Zimbabwe</title>
    <meta name="description" content="EduBridge delivers affordable, high-quality supplementary education directly to students' phones via WhatsApp, powered by AI.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-900 antialiased">

{{-- Navigation --}}
<nav class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-gray-100 shadow-sm">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <span class="text-2xl font-extrabold text-green-700">EduBridge</span>
        </a>
        <div class="hidden md:flex items-center gap-8 text-sm font-medium text-gray-600">
            <a href="#features" class="hover:text-green-700 transition">Features</a>
            <a href="#how-it-works" class="hover:text-green-700 transition">How it works</a>
            <a href="{{ route('courses.index') }}" class="hover:text-green-700 transition">Courses</a>
            <a href="#pricing" class="hover:text-green-700 transition">Pricing</a>
        </div>
        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-800 transition">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-green-700 transition">Log in</a>
                <a href="{{ route('register') }}" class="bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-green-800 transition">Get started</a>
            @endauth
        </div>
    </div>
</nav>

{{-- Hero --}}
<section class="relative overflow-hidden bg-gradient-to-br from-green-50 via-white to-emerald-50 py-20 md:py-32">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="inline-block bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full mb-6 uppercase tracking-wide">
            Zimbabwe's First AI-Powered Learning Companion
        </span>
        <h1 class="text-4xl md:text-6xl font-extrabold text-gray-900 leading-tight mb-6">
            Quality Tuition.<br>
            <span class="text-green-700">Accessible via WhatsApp.</span>
        </h1>
        <p class="max-w-2xl mx-auto text-lg md:text-xl text-gray-600 mb-10">
            EduBridge gives every Zimbabwean student the same quality education as premium private tuition —
            at a fraction of the cost, directly on their phone, with an AI companion available 24/7.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}" class="bg-green-700 text-white px-8 py-4 rounded-xl text-lg font-bold hover:bg-green-800 transition shadow-lg">
                Start learning free
            </a>
            <a href="#how-it-works" class="border-2 border-green-700 text-green-700 px-8 py-4 rounded-xl text-lg font-bold hover:bg-green-50 transition">
                See how it works
            </a>
        </div>
    </div>
</section>

{{-- Stats bar --}}
<section class="bg-green-700 text-white py-12">
    <div class="max-w-5xl mx-auto px-4 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
        <div><div class="text-4xl font-extrabold">24/7</div><div class="text-green-200 mt-1 text-sm">AI companion access</div></div>
        <div><div class="text-4xl font-extrabold">O &amp; A</div><div class="text-green-200 mt-1 text-sm">Level curriculum coverage</div></div>
        <div><div class="text-4xl font-extrabold">$0</div><div class="text-green-200 mt-1 text-sm">To get started</div></div>
        <div><div class="text-4xl font-extrabold">ZW+</div><div class="text-green-200 mt-1 text-sm">Diaspora-friendly payments</div></div>
    </div>
</section>

{{-- Features --}}
<section id="features" class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900">Everything a student needs</h2>
            <p class="mt-4 text-gray-500 text-lg max-w-2xl mx-auto">Six integrated tools designed for the Zimbabwean context.</p>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach([
                ['AI Study Companion', 'Meet Chiedza — your personal AI tutor, always on, always patient. Ask anything about your O-level or A-level syllabus.'],
                ['Lessons via WhatsApp', 'Short-form video lessons delivered directly inside WhatsApp conversations. No app download needed.'],
                ['Live & Recorded Classes', 'Join live lessons via Zoom or Google Meet. Miss one? Every class is recorded and archived on YouTube.'],
                ['Flexible Payments', 'Pay with EcoCash, InnBucks, or Paynow locally — or Stripe for the diaspora. Pricing designed for Zimbabwe.'],
                ['Vetted Zimbabwean Teachers', 'Every teacher is screened, trained, and coordinated by EduBridge to ensure curriculum accuracy.'],
                ['YouTube Revision Library', 'All lessons auto-upload to a YouTube playlist so students can revise anytime, anywhere.'],
            ] as [$title, $desc])
            <div class="bg-gray-50 rounded-2xl p-6 hover:shadow-md transition">
                <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $title }}</h3>
                <p class="text-gray-500 text-sm leading-relaxed">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- How it works --}}
<section id="how-it-works" class="py-20 bg-green-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 text-center">
        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-14">Up and running in 3 steps</h2>
        <div class="grid md:grid-cols-3 gap-10">
            @foreach([
                ['1', 'Create a free account', 'Sign up with your name and phone number. No credit card required to start.'],
                ['2', 'Enrol in a course', 'Browse subjects taught by vetted Zimbabwean educators. Pay what you can afford.'],
                ['3', 'Learn on WhatsApp', 'Receive lessons, chat with Chiedza your AI companion, and join live classes — all from WhatsApp.'],
            ] as [$step, $title, $desc])
            <div class="flex flex-col items-center">
                <div class="w-12 h-12 rounded-full bg-green-700 text-white flex items-center justify-center text-xl font-extrabold mb-4">{{ $step }}</div>
                <h3 class="font-bold text-gray-900 mb-2">{{ $title }}</h3>
                <p class="text-gray-500 text-sm">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Pricing --}}
<section id="pricing" class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 text-center">
        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">Priced for Zimbabwe</h2>
        <p class="text-gray-500 mb-12">Start free. Upgrade when you're ready.</p>
        <div class="grid md:grid-cols-3 gap-8">
            @foreach([
                ['Free', '$0', 'USD', ['AI companion (5 messages/day)', 'Access to free lessons', 'YouTube revision library'], false],
                ['Student', '$5', 'USD/month', ['Unlimited AI companion', 'Full lesson library', 'Live class access', 'WhatsApp delivery'], true],
                ['Diaspora', '$10', 'USD/month', ['Everything in Student', 'Pay with Stripe/card', 'Support a student in ZW', 'Receipt for sponsorship'], false],
            ] as [$plan, $price, $period, $features, $popular])
            <div class="rounded-2xl border-2 p-8 flex flex-col {{ $popular ? 'border-green-700 shadow-xl relative' : 'border-gray-200' }}">
                @if($popular)
                    <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-green-700 text-white text-xs font-bold px-3 py-1 rounded-full">Most popular</span>
                @endif
                <div class="text-xl font-bold text-gray-900 mb-2">{{ $plan }}</div>
                <div class="text-4xl font-extrabold text-green-700 mb-1">{{ $price }}</div>
                <div class="text-sm text-gray-400 mb-6">{{ $period }}</div>
                <ul class="text-sm text-gray-600 space-y-2 mb-8 flex-1 text-left">
                    @foreach($features as $f)
                        <li class="flex items-center gap-2"><span class="text-green-600">✓</span> {{ $f }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}" class="block {{ $popular ? 'bg-green-700 text-white' : 'border-2 border-green-700 text-green-700' }} rounded-xl py-3 font-bold hover:opacity-90 transition">
                    Get started
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="bg-green-700 text-white py-16 text-center">
    <h2 class="text-3xl md:text-4xl font-extrabold mb-4">Ready to bridge the gap?</h2>
    <p class="text-green-200 mb-8 text-lg">Join thousands of Zimbabwean students learning smarter.</p>
    <a href="{{ route('register') }}" class="inline-block bg-white text-green-700 px-10 py-4 rounded-xl text-lg font-extrabold hover:bg-green-50 transition shadow-xl">
        Start learning today — it's free
    </a>
</section>

{{-- Footer --}}
<footer class="bg-gray-900 text-gray-400 py-10">
    <div class="max-w-6xl mx-auto px-4 flex flex-col md:flex-row items-center justify-between gap-4 text-sm">
        <div class="font-bold text-white text-lg">EduBridge</div>
        <div class="flex gap-6">
            <a href="#" class="hover:text-white transition">Privacy</a>
            <a href="#" class="hover:text-white transition">Terms</a>
            <a href="mailto:hello@edubridge.co.zw" class="hover:text-white transition">Contact</a>
        </div>
        <div>&copy; {{ date('Y') }} EduBridge &middot; A KMG Vital Links Initiative</div>
    </div>
</footer>

</body>
</html>
