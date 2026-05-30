<x-app-layout>
    <x-slot name="title">Quiz Result — {{ $quiz->title }}</x-slot>

    <div class="max-w-xl">
        @php
        $pct = $attempt->score_percentage;
        $passed = $attempt->passed;
        // Calculate XP this attempt earned (mirrors GamificationService logic)
        $xpEarned = 0;
        if ($passed) {
            $xpEarned = 20;
            if ($pct === 100) $xpEarned += 15;
            if ($attempt->attempt_number === 1) $xpEarned += 5;
        }
        @endphp

        <div class="card p-8 text-center">
            <div class="w-24 h-24 rounded-full {{ $passed ? 'bg-emerald-100' : 'bg-red-100' }} flex items-center justify-center mx-auto mb-4 text-4xl">
                {{ $passed ? '🎉' : '💪' }}
            </div>
            <h1 class="text-2xl font-bold text-slate-900 mb-1">
                {{ $passed ? 'Well done!' : 'Keep practising' }}
            </h1>
            <p class="text-slate-500 mb-6">{{ $quiz->title }}</p>

            <div class="flex justify-center gap-8 mb-8">
                <div class="text-center">
                    <p class="text-4xl font-extrabold {{ $passed ? 'text-emerald-600' : 'text-red-500' }}">{{ $pct }}%</p>
                    <p class="text-xs text-slate-400 mt-1">Your score</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl font-extrabold text-slate-700">{{ $attempt->score }}/{{ $attempt->max_score }}</p>
                    <p class="text-xs text-slate-400 mt-1">Points</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl font-extrabold {{ $passed ? 'text-emerald-600' : 'text-slate-400' }}">{{ $quiz->pass_percentage }}%</p>
                    <p class="text-xs text-slate-400 mt-1">Pass mark</p>
                </div>
            </div>

            @if($quiz->show_answers_after)
            <div class="text-left space-y-3 mb-6">
                <h3 class="font-semibold text-slate-900 text-sm">Answers</h3>                @foreach($quiz->questions as $q)
                @php
                $studentAns = $attempt->answers[$q->id] ?? null;
                $correct    = strtolower(trim($studentAns ?? '')) === strtolower(trim($q->correct_answer));
                @endphp
                <div class="p-3 rounded-xl {{ $correct ? 'bg-emerald-50 border border-emerald-200' : 'bg-red-50 border border-red-200' }}">
                    <p class="text-sm font-medium text-slate-700 mb-1">{{ $q->question }}</p>
                    <p class="text-xs {{ $correct ? 'text-emerald-700' : 'text-red-700' }}">
                        Your answer: <strong>{{ $studentAns ?? '—' }}</strong>
                        @if(!$correct) &middot; Correct: <strong>{{ $q->correct_answer }}</strong> @endif
                    </p>
                    @if($q->explanation && !$correct)
                    <p class="text-xs text-slate-500 mt-1">💡 {{ $q->explanation }}</p>
                    @endif
                </div>
                @endforeach
            </div>
            @endif

            @if($passed && $xpEarned > 0)
            <div class="mb-5 flex items-center justify-center gap-2 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
                <span class="text-lg">⭐</span>
                <span>You earned <strong>+{{ $xpEarned }} XP</strong> for passing this quiz!</span>
                <a href="{{ route('student.achievements') }}" class="ml-2 text-xs text-emerald-600 hover:underline font-semibold">View achievements →</a>
            </div>
            @endif

            <a href="{{ route('student.dashboard') }}" class="btn-secondary">Back to dashboard</a>
        </div>
    </div>
</x-app-layout>
