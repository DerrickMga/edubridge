<x-app-layout>
<x-slot name="title">Welcome to EduBridge — Step {{ $step }} of {{ $maxStep }}</x-slot>

{{-- ────────────────────────── ONBOARDING WIZARD ────────────────────────── --}}
<div class="min-h-screen flex flex-col items-center justify-center px-4 py-12 bg-gradient-to-br from-slate-50 to-emerald-50">

    {{-- Logo --}}
    <a href="{{ route('home') }}" class="mb-8 flex items-center gap-2">
        <img src="/logo.svg" alt="EduBridge" class="h-10 w-auto">
    </a>

    {{-- Progress bar --}}
    <div class="w-full max-w-2xl mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wide">Step {{ $step }} of {{ $maxStep }}</span>
            <span class="text-xs text-slate-400">{{ round(($step / $maxStep) * 100) }}% complete</span>
        </div>
        <div class="w-full bg-slate-200 rounded-full h-2">
            <div class="bg-emerald-500 h-2 rounded-full transition-all duration-500"
                 style="width: {{ round(($step / $maxStep) * 100) }}%"></div>
        </div>

        {{-- Step dots --}}
        <div class="flex justify-between mt-3">
            @for($i = 1; $i <= $maxStep; $i++)
                <div class="flex flex-col items-center">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all
                        {{ $i < $step  ? 'bg-emerald-500 text-white' : ($i === $step ? 'bg-emerald-600 text-white ring-4 ring-emerald-100' : 'bg-slate-200 text-slate-400') }}">
                        @if($i < $step)
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                            </svg>
                        @else
                            {{ $i }}
                        @endif
                    </div>
                    <span class="text-xs mt-1 {{ $i === $step ? 'text-emerald-700 font-semibold' : 'text-slate-400' }} hidden sm:block">
                        @if($user->role === 'student')
                            {{ ['Welcome', 'Your Profile', 'Explore Features', 'Get Started'][$i - 1] ?? '' }}
                        @else
                            {{ ['Welcome', 'Your Profile', 'Explore Tools', 'Your First Course'][$i - 1] ?? '' }}
                        @endif
                    </span>
                </div>
            @endfor
        </div>
    </div>

    {{-- Card --}}
    <div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">

        {{-- ══════════════════ STEP 1 — WELCOME ══════════════════ --}}
        @if($step === 1)
            <div class="p-8 sm:p-10">
                @if($user->role === 'student')
                    {{-- STUDENT WELCOME --}}
                    <div class="text-center mb-8">
                        <div class="text-5xl mb-4">👋</div>
                        <h1 class="text-3xl font-extrabold text-slate-900 mb-2">
                            Welcome, {{ $user->name }}!
                        </h1>
                        <p class="text-slate-500 text-base max-w-md mx-auto">
                            EduBridge is your personal learning hub. In just 4 quick steps we'll get you set up and ready to learn.
                        </p>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4 mb-8">
                        @foreach([
                            ['📚', 'Structured Courses', 'Expert-taught courses with video lessons, quizzes and assignments.'],
                            ['🤖', 'AI Learning Companion', 'Chiedza — your personal AI tutor — answers questions and explains concepts 24/7.'],
                            ['🏆', 'Badges & Certificates', 'Earn XP, badges and certificates as you progress through your learning journey.'],
                            ['📅', 'Live Sessions', 'Join interactive live sessions with real teachers and ask questions in real time.'],
                        ] as [$icon, $title, $desc])
                            <div class="flex gap-3 p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <span class="text-2xl flex-shrink-0">{{ $icon }}</span>
                                <div>
                                    <p class="font-semibold text-slate-800 text-sm">{{ $title }}</p>
                                    <p class="text-slate-500 text-xs mt-0.5 leading-relaxed">{{ $desc }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                @else
                    {{-- TEACHER WELCOME --}}
                    <div class="text-center mb-8">
                        <div class="text-5xl mb-4">🎓</div>
                        <h1 class="text-3xl font-extrabold text-slate-900 mb-2">
                            Welcome aboard, {{ $user->name }}!
                        </h1>
                        <p class="text-slate-500 text-base max-w-md mx-auto">
                            EduBridge gives you powerful tools to create courses, host live sessions, and inspire students. Let's get you set up.
                        </p>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4 mb-8">
                        @foreach([
                            ['📖', 'Course Builder', 'Create structured courses with lessons, quizzes, assignments and resources.'],
                            ['🎥', 'Live Sessions', 'Schedule and host live teaching sessions with recording support.'],
                            ['👥', 'Multi-Teacher Courses', 'Collaborate — multiple teachers can co-teach the same course.'],
                            ['📊', 'Analytics & Reports', 'Track student progress, session attendance and AI-generated insights.'],
                        ] as [$icon, $title, $desc])
                            <div class="flex gap-3 p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <span class="text-2xl flex-shrink-0">{{ $icon }}</span>
                                <div>
                                    <p class="font-semibold text-slate-800 text-sm">{{ $title }}</p>
                                    <p class="text-slate-500 text-xs mt-0.5 leading-relaxed">{{ $desc }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('onboarding.save', ['step' => 1]) }}">
                    @csrf
                    <button type="submit" class="btn-primary w-full justify-center py-3 text-base">
                        Let's get started
                        <svg class="w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </button>
                </form>
            </div>

        {{-- ══════════════════ STEP 2 — PROFILE SETUP ══════════════════ --}}
        @elseif($step === 2)
            <div class="p-8 sm:p-10">
                <div class="mb-7">
                    <h2 class="text-2xl font-bold text-slate-900">
                        {{ $user->role === 'student' ? '📝 Tell us about yourself' : '📝 Your teaching profile' }}
                    </h2>
                    <p class="text-slate-500 text-sm mt-1">
                        {{ $user->role === 'student'
                            ? 'This helps us personalise your learning experience and connect you with the right courses.'
                            : 'Students will see this when browsing your courses. A great profile builds trust.' }}
                    </p>
                </div>

                <form method="POST" action="{{ route('onboarding.save', ['step' => 2]) }}" class="space-y-5">
                    @csrf

                    @if($user->role === 'student')
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">Grade / Year Level</label>
                                <select name="grade_level" class="form-input">
                                    <option value="">Select your level…</option>
                                    @foreach(['Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6',
                                              'Grade 7','Grade 8','Grade 9','Grade 10','Grade 11','Grade 12',
                                              'A-Level','Foundation','Undergraduate','Postgraduate','Adult Learner'] as $g)
                                        <option value="{{ $g }}" {{ old('grade_level', $user->grade_level) === $g ? 'selected' : '' }}>{{ $g }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Timezone</label>
                                <select name="timezone" class="form-input">
                                    <option value="">Select timezone…</option>
                                    @foreach(timezone_identifiers_list() as $tz)
                                        <option value="{{ $tz }}" {{ old('timezone', $user->timezone) === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" value="{{ old('country', $user->country) }}" class="form-input" placeholder="Zimbabwe">
                            </div>
                            <div class="form-group">
                                <label class="form-label">City</label>
                                <input type="text" name="city" value="{{ old('city', $user->city) }}" class="form-input" placeholder="Harare">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Short bio <span class="text-slate-400 font-normal">(optional)</span></label>
                            <textarea name="bio" rows="3" class="form-input" placeholder="Tell us what you'd like to learn or what you're passionate about…">{{ old('bio', $user->bio) }}</textarea>
                        </div>

                    @else
                        {{-- TEACHER profile --}}
                        <div class="form-group">
                            <label class="form-label">Qualifications & Subjects</label>
                            <input type="text" name="qualification" value="{{ old('qualification', $user->qualification) }}" class="form-input"
                                placeholder="e.g. BSc Mathematics, PGCE, 8 years teaching experience">
                        </div>
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" value="{{ old('country', $user->country) }}" class="form-input" placeholder="Zimbabwe">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Timezone</label>
                                <select name="timezone" class="form-input">
                                    <option value="">Select timezone…</option>
                                    @foreach(timezone_identifiers_list() as $tz)
                                        <option value="{{ $tz }}" {{ old('timezone', $user->timezone) === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Hourly rate (USD) <span class="text-slate-400 font-normal">— helps set pay expectations</span></label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 text-sm">$</span>
                                <input type="number" name="hourly_rate_usd" min="0" step="0.50"
                                    value="{{ old('hourly_rate_usd', $user->hourly_rate_usd) }}"
                                    class="form-input pl-7" placeholder="25.00">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Teaching bio</label>
                            <textarea name="bio" rows="4" class="form-input"
                                placeholder="Describe your teaching style, experience and what makes your lessons great…">{{ old('bio', $user->bio) }}</textarea>
                            <p class="text-xs text-slate-400 mt-1">Aim for 3–5 sentences. Students read this before enrolling.</p>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
                            <ul class="list-disc list-inside space-y-0.5">
                                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="flex gap-3 pt-2">
                        <a href="{{ route('onboarding', ['step' => 1]) }}" class="btn-secondary flex-shrink-0 px-5 py-2.5">Back</a>
                        <button type="submit" class="btn-primary flex-1 justify-center py-2.5">
                            Save & continue
                            <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

        {{-- ══════════════════ STEP 3 — PLATFORM WALKTHROUGH ══════════════════ --}}
        @elseif($step === 3)
            <div class="p-8 sm:p-10">
                @if($user->role === 'student')
                    <div class="mb-7">
                        <h2 class="text-2xl font-bold text-slate-900">🗺️ How to navigate EduBridge</h2>
                        <p class="text-slate-500 text-sm mt-1">Here's a quick tour of everything available to you as a student.</p>
                    </div>

                    <div class="space-y-4">
                        @foreach([
                            [
                                'icon' => '🏠',
                                'title' => 'Your Dashboard',
                                'route' => 'student.dashboard',
                                'label' => 'Go to Dashboard',
                                'desc' => 'Your home base. See enrolled courses, upcoming live sessions, recent achievements, and your XP progress bar all in one place.',
                            ],
                            [
                                'icon' => '📚',
                                'title' => 'Browse Courses',
                                'route' => 'courses.index',
                                'label' => 'Browse Catalogue',
                                'desc' => 'Search hundreds of courses by subject, grade level or topic. Enrol in free or paid courses with one click. Filter by tutor, price, or difficulty.',
                            ],
                            [
                                'icon' => '🤖',
                                'title' => 'Chiedza — Your AI Companion',
                                'route' => 'student.companion.index',
                                'label' => 'Open Chiedza',
                                'desc' => 'Ask Chiedza any question about your lessons. Get instant explanations, practice questions, exam tips and personalised study plans.',
                            ],
                            [
                                'icon' => '📓',
                                'title' => 'My Notebook',
                                'route' => 'student.notebook.index',
                                'label' => 'Open Notebook',
                                'desc' => 'Take and organise notes while watching lessons. Notes are tied to the lesson so you can always find them again.',
                            ],
                            [
                                'icon' => '📅',
                                'title' => 'Live Sessions',
                                'route' => 'student.dashboard',
                                'label' => 'View upcoming sessions',
                                'desc' => 'Your upcoming live sessions appear on your dashboard. You\'ll get email and WhatsApp reminders before each one.',
                            ],
                            [
                                'icon' => '🏆',
                                'title' => 'Achievements & Leaderboard',
                                'route' => 'student.achievements',
                                'label' => 'My Achievements',
                                'desc' => 'Earn XP for completing lessons, quizzes and assignments. Unlock badges and certificates. Compete on the global leaderboard.',
                            ],
                        ] as $item)
                            <div class="flex gap-4 p-4 bg-slate-50 rounded-xl border border-slate-100 hover:border-emerald-200 transition-colors">
                                <span class="text-3xl flex-shrink-0 mt-0.5">{{ $item['icon'] }}</span>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-slate-800">{{ $item['title'] }}</p>
                                    <p class="text-slate-500 text-sm mt-0.5 leading-relaxed">{{ $item['desc'] }}</p>
                                </div>
                                <a href="{{ route($item['route']) }}" target="_blank"
                                   class="flex-shrink-0 self-center text-xs text-emerald-600 font-medium hover:underline whitespace-nowrap hidden sm:block">
                                    {{ $item['label'] }} →
                                </a>
                            </div>
                        @endforeach
                    </div>

                @else
                    {{-- TEACHER walkthrough --}}
                    <div class="mb-7">
                        <h2 class="text-2xl font-bold text-slate-900">🗺️ Your teaching toolkit</h2>
                        <p class="text-slate-500 text-sm mt-1">Everything you need to run a world-class course — here's where to find it.</p>
                    </div>

                    <div class="space-y-4">
                        @foreach([
                            [
                                'icon' => '🏠',
                                'title' => 'Teacher Dashboard',
                                'route' => 'teacher.dashboard',
                                'label' => 'Go to Dashboard',
                                'desc' => 'Your overview: enrolled students, upcoming live sessions, recent lesson completions, your earnings summary and AI report notifications.',
                            ],
                            [
                                'icon' => '📖',
                                'title' => 'My Courses',
                                'route' => 'teacher.courses.index',
                                'label' => 'View My Courses',
                                'desc' => 'Create and manage your courses here. Each course has lessons, quizzes, assignments and resources. Use "Browse Catalogue" to join an existing course as a co-teacher.',
                            ],
                            [
                                'icon' => '🎥',
                                'title' => 'Live Sessions',
                                'route' => 'teacher.live-sessions.create',
                                'label' => 'Schedule a session',
                                'desc' => 'Schedule live teaching sessions with a Zoom/Meet link. Students are notified automatically. Sessions are recorded and linked to your course.',
                            ],
                            [
                                'icon' => '📝',
                                'title' => 'Assignments & Quizzes',
                                'route' => 'teacher.courses.index',
                                'label' => 'Manage Courses',
                                'desc' => 'Inside each course you can add auto-graded quizzes and open-ended assignments. The AI can generate quiz questions from your lesson content.',
                            ],
                            [
                                'icon' => '📊',
                                'title' => 'Session AI Reports',
                                'route' => 'teacher.dashboard',
                                'label' => 'See Reports',
                                'desc' => 'After each live session, the AI generates a detailed report: attendance, participation, topics covered, and recommended follow-up actions.',
                            ],
                            [
                                'icon' => '💬',
                                'title' => 'WhatsApp Integration',
                                'route' => 'teacher.dashboard',
                                'label' => 'Dashboard',
                                'desc' => 'Students can message you through WhatsApp. All messages are routed to your conversation inbox so nothing gets lost.',
                            ],
                        ] as $item)
                            <div class="flex gap-4 p-4 bg-slate-50 rounded-xl border border-slate-100 hover:border-emerald-200 transition-colors">
                                <span class="text-3xl flex-shrink-0 mt-0.5">{{ $item['icon'] }}</span>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-slate-800">{{ $item['title'] }}</p>
                                    <p class="text-slate-500 text-sm mt-0.5 leading-relaxed">{{ $item['desc'] }}</p>
                                </div>
                                <a href="{{ route($item['route']) }}" target="_blank"
                                   class="flex-shrink-0 self-center text-xs text-emerald-600 font-medium hover:underline whitespace-nowrap hidden sm:block">
                                    {{ $item['label'] }} →
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('onboarding.save', ['step' => 3]) }}" class="mt-8">
                    @csrf
                    <div class="flex gap-3">
                        <a href="{{ route('onboarding', ['step' => 2]) }}" class="btn-secondary flex-shrink-0 px-5 py-2.5">Back</a>
                        <button type="submit" class="btn-primary flex-1 justify-center py-2.5">
                            Continue
                            <svg class="w-4 h-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

        {{-- ══════════════════ STEP 4 — LAUNCH PAD ══════════════════ --}}
        @elseif($step === 4)
            <div class="p-8 sm:p-10 text-center">
                <div class="text-6xl mb-5">🚀</div>
                <h2 class="text-3xl font-extrabold text-slate-900 mb-3">
                    {{ $user->role === 'student' ? "You're all set!" : "Ready to teach!" }}
                </h2>
                <p class="text-slate-500 max-w-md mx-auto mb-10">
                    {{ $user->role === 'student'
                        ? "Your EduBridge account is ready. Head to your dashboard to explore courses, meet your AI companion Chiedza, and start earning XP!"
                        : "Your teaching profile is live. Head to your dashboard to create your first course or join an existing one as a co-teacher." }}
                </p>

                {{-- Quick action tiles --}}
                <div class="grid sm:grid-cols-{{ $user->role === 'student' ? '3' : '2' }} gap-3 mb-10 text-left">
                    @if($user->role === 'student')
                        <a href="{{ route('courses.index') }}"
                           class="flex flex-col gap-2 p-5 bg-emerald-50 border-2 border-emerald-200 rounded-xl hover:bg-emerald-100 transition-colors">
                            <span class="text-2xl">📚</span>
                            <p class="font-semibold text-emerald-900 text-sm">Browse Courses</p>
                            <p class="text-emerald-700 text-xs">Find your first course to enrol in</p>
                        </a>
                        <a href="{{ route('student.companion.index') }}"
                           class="flex flex-col gap-2 p-5 bg-violet-50 border-2 border-violet-200 rounded-xl hover:bg-violet-100 transition-colors">
                            <span class="text-2xl">🤖</span>
                            <p class="font-semibold text-violet-900 text-sm">Meet Chiedza</p>
                            <p class="text-violet-700 text-xs">Your AI study companion</p>
                        </a>
                        <a href="{{ route('student.dashboard') }}"
                           class="flex flex-col gap-2 p-5 bg-sky-50 border-2 border-sky-200 rounded-xl hover:bg-sky-100 transition-colors">
                            <span class="text-2xl">🏆</span>
                            <p class="font-semibold text-sky-900 text-sm">My Dashboard</p>
                            <p class="text-sky-700 text-xs">Track progress & upcoming sessions</p>
                        </a>
                    @else
                        <a href="{{ route('teacher.courses.create') }}"
                           class="flex flex-col gap-2 p-5 bg-emerald-50 border-2 border-emerald-200 rounded-xl hover:bg-emerald-100 transition-colors">
                            <span class="text-2xl">✏️</span>
                            <p class="font-semibold text-emerald-900 text-sm">Create a Course</p>
                            <p class="text-emerald-700 text-xs">Build your first course from scratch</p>
                        </a>
                        <a href="{{ route('teacher.courses.browse') }}"
                           class="flex flex-col gap-2 p-5 bg-sky-50 border-2 border-sky-200 rounded-xl hover:bg-sky-100 transition-colors">
                            <span class="text-2xl">👥</span>
                            <p class="font-semibold text-sky-900 text-sm">Join a Course</p>
                            <p class="text-sky-700 text-xs">Co-teach an existing course</p>
                        </a>
                    @endif
                </div>

                <form method="POST" action="{{ route('onboarding.complete') }}">
                    @csrf
                    <button type="submit" class="btn-primary px-10 py-3 text-base justify-center w-full sm:w-auto">
                        Go to my dashboard
                        <svg class="w-5 h-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </button>
                </form>
            </div>
        @endif

    </div>

    {{-- Skip link --}}
    @if($step < 4)
        <form method="POST" action="{{ route('onboarding.complete') }}" class="mt-5">
            @csrf
            <button type="submit" class="text-sm text-slate-400 hover:text-slate-600 underline underline-offset-2 transition-colors">
                Skip setup — go straight to my dashboard
            </button>
        </form>
    @endif

</div>
</x-app-layout>
