<x-app-layout>
    <x-slot name="title">{{ $quiz->title }}</x-slot>

    <div class="max-w-2xl" x-data="{
        current: 0,
        total: {{ $quiz->questions->count() }},
        answers: {},
        started: false,
        timeLeft: {{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes * 60 : 0 }},
        timerInterval: null,
        startQuiz() {
            this.started = true;
            if (this.timeLeft > 0) {
                this.timerInterval = setInterval(() => {
                    this.timeLeft--;
                    if (this.timeLeft <= 0) {
                        clearInterval(this.timerInterval);
                        this.$refs.quizForm.submit();
                    }
                }, 1000);
            }
        },
        get minutes() { return String(Math.floor(this.timeLeft / 60)).padStart(2, '0'); },
        get seconds() { return String(this.timeLeft % 60).padStart(2, '0'); },
    }">
        {{-- Header --}}
        <div class="page-header flex items-start justify-between gap-3 mb-6">
            <div>
                <h1 class="page-title">{{ $quiz->title }}</h1>
                <p class="page-subtitle">{{ $quiz->questions->count() }} questions
                    @if($quiz->time_limit_minutes) &middot; {{ $quiz->time_limit_minutes }} min limit @endif
                    &middot; Pass: {{ $quiz->pass_percentage }}%
                </p>
            </div>
            @if($quiz->time_limit_minutes)
            <div x-show="started" class="flex-shrink-0 bg-slate-900 text-white rounded-xl px-4 py-2 font-mono text-lg font-bold">
                <span x-text="minutes + ':' + seconds"></span>
            </div>
            @endif
        </div>

        {{-- Start screen --}}
        <div x-show="!started" class="card p-8 text-center">
            <span class="text-5xl block mb-4">📝</span>
            <h2 class="text-xl font-bold text-slate-900 mb-2">Ready to start?</h2>
            @if($quiz->description)
            <p class="text-slate-500 mb-5">{{ $quiz->description }}</p>
            @endif
            <ul class="text-sm text-slate-600 space-y-1 mb-6 text-left max-w-xs mx-auto">
                <li>📊 {{ $quiz->questions->count() }} questions</li>
                @if($quiz->time_limit_minutes)<li>⏱ {{ $quiz->time_limit_minutes }} minute time limit</li>@endif
                <li>✅ Pass score: {{ $quiz->pass_percentage }}%</li>
                <li>🔁 Max {{ $quiz->max_attempts }} attempt{{ $quiz->max_attempts !== 1 ? 's' : '' }}</li>
            </ul>
            <button @click="startQuiz()" class="btn-primary">Start Quiz</button>
        </div>

        {{-- Quiz form --}}
        <div x-show="started">
            <form x-ref="quizForm" action="{{ route('student.quizzes.submit', $quiz) }}" method="POST" class="space-y-5">
                @csrf
                @foreach($quiz->questions as $i => $question)
                <div class="card p-5">
                    <p class="font-semibold text-slate-900 mb-1">
                        <span class="text-slate-400 text-sm font-normal mr-1">{{ $i + 1 }}.</span>
                        {{ $question->question }}
                        <span class="ml-2 text-xs text-slate-400">({{ $question->points }} pt{{ $question->points !== 1 ? 's' : '' }})</span>
                    </p>

                    @if($question->type === 'mcq' && $question->options)
                    <div class="mt-3 space-y-2">
                        @foreach($question->options as $opt)
                        <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 cursor-pointer hover:border-emerald-400 hover:bg-emerald-50 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $opt }}"
                                   x-model="answers[{{ $question->id }}]" class="accent-emerald-500">
                            <span class="text-sm text-slate-700">{{ $opt }}</span>
                        </label>
                        @endforeach
                    </div>
                    @elseif($question->type === 'true_false')
                    <div class="mt-3 flex gap-3">
                        @foreach(['True','False'] as $opt)
                        <label class="flex-1 flex items-center justify-center gap-2 p-3 rounded-xl border border-slate-200 cursor-pointer hover:border-emerald-400 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $opt }}"
                                   x-model="answers[{{ $question->id }}]" class="accent-emerald-500">
                            <span class="text-sm font-medium text-slate-700">{{ $opt }}</span>
                        </label>
                        @endforeach
                    </div>
                    @else
                    <div class="mt-3">
                        <input type="text" name="answers[{{ $question->id }}]"
                               x-model="answers[{{ $question->id }}]"
                               class="form-input" placeholder="Your answer...">
                    </div>
                    @endif
                </div>
                @endforeach

                <div class="flex gap-3 justify-end pt-2">
                    <button type="submit" class="btn-primary"
                            onclick="return confirm('Submit quiz? You cannot change answers after submission.')">
                        Submit Quiz
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
