<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Curriculum Benchmark Guide — EduBridge</title>
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
        <div class="flex items-center gap-3">
            <a href="{{ route('subjects.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Subjects</a>
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary btn-sm">Dashboard</a>
            @else
                <a href="{{ route('register') }}" class="btn-primary btn-sm">Get started</a>
            @endauth
        </div>
    </div>
</nav>

{{-- Hero --}}
<section class="bg-gradient-to-br from-slate-900 to-indigo-900 py-16 text-center">
    <div class="max-w-3xl mx-auto px-4">
        <span class="text-xs font-semibold bg-indigo-700 text-indigo-200 px-3 py-1 rounded-full uppercase tracking-wider mb-4 inline-block">ZIMSEC Aligned</span>
        <h1 class="text-3xl md:text-4xl font-extrabold text-white mb-3">Zimbabwe Curriculum Benchmark Guide</h1>
        <p class="text-slate-300 text-lg">Your comprehensive reference for O-Level and A-Level standards, grade targets, and study strategies.</p>
    </div>
</section>

{{-- Benchmarks: O-Level --}}
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-12 space-y-12">

    {{-- Grade Scale --}}
    <div class="card p-6">
        <h2 class="section-title mb-5 text-base">ZIMSEC Grade Scale</h2>
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
            @foreach([
                ['A', '75-100%', 'bg-emerald-100 text-emerald-800 border-emerald-200'],
                ['B', '60-74%',  'bg-blue-100 text-blue-800 border-blue-200'],
                ['C', '50-59%',  'bg-teal-100 text-teal-800 border-teal-200'],
                ['D', '40-49%',  'bg-amber-100 text-amber-800 border-amber-200'],
                ['E', '30-39%',  'bg-orange-100 text-orange-800 border-orange-200'],
                ['U', '0-29%',   'bg-red-100 text-red-800 border-red-200'],
            ] as [$grade, $range, $cls])
            <div class="border rounded-xl p-4 text-center {{ $cls }}">
                <p class="text-3xl font-extrabold">{{ $grade }}</p>
                <p class="text-xs font-semibold mt-1">{{ $range }}</p>
            </div>
            @endforeach
        </div>
        <p class="text-xs text-slate-400 mt-4">EduBridge target: <span class="font-semibold text-emerald-700">B or above (60%+)</span> in every subject. A passes (75%+) unlock university eligibility.</p>
    </div>

    {{-- O-Level benchmarks --}}
    <div>
        <h2 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm font-bold">O</span>
            O-Level Benchmarks
        </h2>
        <div class="space-y-4">
            @php
            $benchmarks = [
                ['Mathematics', '📐', 'from-blue-500 to-indigo-600', 'Form 1–4', [
                    'Form 1–2: Numbers, algebra basics, basic geometry',
                    'Form 3: Quadratic equations, circle theorems, trigonometry',
                    'Form 4: Advanced statistics, calculus intro, coordinate geometry',
                    'Exam: Paper 1 (non-calculator) + Paper 2 (structured problems)',
                ], '62%', 'Pass 5+ subjects including Maths with grade C or above for ZIMSEC O-Level Certificate.'],
                ['English Language', '✍️', 'from-purple-500 to-violet-600', 'Form 1–4', [
                    'Form 1–2: Basic grammar, comprehension, paragraph writing',
                    'Form 3: Essay types, formal letter writing, vocabulary expansion',
                    'Form 4: Summary writing, argumentative essays, directed writing',
                    'Exam: Paper 1 (comprehension/writing) + Paper 2 (composition)',
                ], '70%', 'English is compulsory for university admission and most employment.'],
                ['Biology', '🧬', 'from-emerald-500 to-teal-600', 'Form 1–4', [
                    'Form 1–2: Cell biology, nutrition, basic ecosystem',
                    'Form 3: Genetics and variation, human physiology, ecology',
                    'Form 4: Reproduction, homeostasis, natural selection',
                    'Exam: Paper 1 (MCQ) + Paper 2 (structured/essay)',
                ], '55%', 'Required for medicine, nursing, and agricultural sciences.'],
                ['Chemistry', '⚗️', 'from-teal-400 to-emerald-600', 'Form 1–4', [
                    'Form 1–2: Matter, separation techniques, periodic table',
                    'Form 3: Chemical bonding, acids/bases, mole concept',
                    'Form 4: Organic chemistry, electrochemistry, rates of reaction',
                    'Exam: Paper 1 (MCQ) + Paper 2 (structured/practical-based)',
                ], '50%', 'Key requirement for medicine, pharmacy, and engineering.'],
                ['History', '📜', 'from-amber-400 to-orange-500', 'Form 1–4', [
                    'Form 1–2: Pre-colonial Zimbabwe, early civilisations',
                    'Form 3: Colonialism in Africa, rise of nationalism',
                    'Form 4: Zimbabwe liberation war, post-independence Africa',
                    'Exam: Essay-based, source analysis, causation/consequence',
                ], '65%', 'Develops critical thinking; required for law and social sciences.'],
                ['Geography', '🗺️', 'from-teal-500 to-cyan-600', 'Form 1–4', [
                    'Form 1–2: Map reading, weather patterns, population basics',
                    'Form 3: Plate tectonics, agricultural systems, settlement',
                    'Form 4: Environmental issues, economic development, resource management',
                    'Exam: Paper 1 (theory) + Paper 2 (mapwork & fieldwork)',
                ], '60%', 'Excellent for environmental management, urban planning, and social sciences.'],
            ];
            @endphp

            @foreach($benchmarks as [$name, $icon, $color, $level, $topics, $passRate, $note])
            <div class="card overflow-hidden">
                <div class="flex items-center gap-4 p-5 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br {{ $color }} flex items-center justify-center text-xl flex-shrink-0">{{ $icon }}</div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-semibold text-slate-900">{{ $name }}</h3>
                            <span class="badge-slate">{{ $level }}</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-0.5 italic">{{ $note }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-2xl font-extrabold text-emerald-700">{{ $passRate }}</p>
                        <p class="text-[10px] text-slate-400">ZIM pass rate</p>
                    </div>
                </div>
                <div class="p-5">
                    <p class="section-label mb-3">Curriculum progression</p>
                    <ul class="space-y-1.5">
                        @foreach($topics as $topic)
                        <li class="flex items-start gap-2 text-sm text-slate-600">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            {{ $topic }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Study Tips --}}
    <div class="card p-6 bg-gradient-to-br from-emerald-50 to-teal-50 border-emerald-200">
        <h2 class="section-title mb-5 text-base">📚 EduBridge Study Strategy</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach([
                ['🗓️ Spaced Repetition', 'Review material at increasing intervals: 1 day, 3 days, 1 week, 2 weeks. This is the most effective method for long-term retention.'],
                ['📝 Past Papers', 'ZIMSEC past papers are your best preparation tool. Complete at least 5 full past papers per subject before your exams.'],
                ['🎯 Mark Schemes', 'Study mark schemes carefully — they show exactly what examiners are looking for. Use them to self-assess every practice essay.'],
                ['🧩 Active Recall', 'Close your textbook and try to recall key concepts from memory. Testing yourself beats passive re-reading every time.'],
                ['💬 Teach Someone', 'Explain concepts to classmates or family. The Feynman Technique: if you can\'t explain it simply, you don\'t understand it yet.'],
                ['⏰ Time Management', 'In exams, allocate time per mark. For a 2-hour paper with 100 marks: 1 minute per mark = 20 mins to check. Never leave blanks.'],
            ] as [$title, $desc])
            <div class="bg-white rounded-xl border border-emerald-100 p-4">
                <p class="font-semibold text-slate-800 mb-1">{{ $title }}</p>
                <p class="text-xs text-slate-500 leading-relaxed">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- EduBridge Alignment --}}
    <div class="card p-6">
        <h2 class="section-title mb-5 text-base">✅ How EduBridge Aligns with ZIMSEC</h2>
        <div class="space-y-3">
            @foreach([
                ['Syllabus Coverage', 'All EduBridge lessons are mapped to the current ZIMSEC O-Level and A-Level syllabi. Teachers are required to tag lessons with the specific topic area.'],
                ['Exam Preparation Focus', 'Courses include dedicated exam prep lessons with worked ZIMSEC past paper questions and model answers.'],
                ['Multiple Exam Boards', 'We support both ZIMSEC and Cambridge International curricula. Content is clearly labelled by exam board.'],
                ['Teacher Vetting', 'All tutors on EduBridge are verified qualified teachers with at least 2 years of classroom experience in Zimbabwe.'],
                ['AI Study Support', 'Chiedza AI provides instant explanations, concept clarification, and study tips aligned to the Zimbabwe curriculum.'],
            ] as [$label, $desc])
            <div class="flex items-start gap-3">
                <div class="w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                </div>
                <div>
                    <p class="font-medium text-slate-800 text-sm">{{ $label }}</p>
                    <p class="text-xs text-slate-400 mt-0.5 leading-relaxed">{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- CTA --}}
    <div class="text-center py-8">
        <h3 class="text-xl font-bold text-slate-900 mb-2">Ready to start your journey?</h3>
        <p class="text-slate-500 mb-6">Join thousands of Zimbabwean students on EduBridge.</p>
        <div class="flex gap-3 justify-center flex-wrap">
            <a href="{{ route('register') }}" class="btn-primary">Start learning free</a>
            <a href="{{ route('courses.index') }}" class="btn-secondary">Browse courses</a>
        </div>
    </div>
</div>

{{-- Footer --}}
<footer class="border-t border-slate-200 bg-white py-8">
    <div class="max-w-6xl mx-auto px-4 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-slate-400">
        <div class="font-bold text-slate-800">EduBridge</div>
        <div class="flex gap-6">
            <a href="{{ route('subjects.index') }}" class="hover:text-slate-600">Subjects</a>
            <a href="{{ route('courses.index') }}" class="hover:text-slate-600">All Courses</a>
            <a href="{{ route('about') }}" class="hover:text-slate-600">About</a>
        </div>
        <div>&copy; {{ date('Y') }} EduBridge · KMG Vital Links</div>
    </div>
</footer>
</body>
</html>
