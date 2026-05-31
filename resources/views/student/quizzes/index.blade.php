<x-app-layout>
    <x-slot name="title">Quizzes – {{ $course->title }}</x-slot>

    <div class="page-header flex items-center gap-3 mb-6">
        <a href="{{ route('student.dashboard') }}"
           class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition-colors">
            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
        </a>
        <div>
            <p class="text-xs text-slate-400 mb-0.5">{{ $course->subject }} · {{ $course->grade_level }}</p>
            <h1 class="page-title">Quizzes</h1>
        </div>
    </div>

    @if($quizzes->isEmpty())
    <div class="card empty-state">
        <span class="empty-state-icon">📝</span>
        <p class="empty-state-title">No quizzes yet</p>
        <p class="empty-state-text">Your teacher hasn't published any quizzes for this course.</p>
    </div>
    @else
    <div class="space-y-4">
        @foreach($quizzes as $quiz)
        @php
            $bestAttempt  = $quiz->my_attempts->where('passed', true)->sortByDesc('score')->first();
            $lastAttempt  = $quiz->my_attempts->sortByDesc('created_at')->first();
            $attemptsLeft = $quiz->max_attempts - $quiz->my_attempts->count();
        @endphp
        <div class="card p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl flex-shrink-0 flex items-center justify-center
                {{ $bestAttempt ? 'bg-emerald-100' : ($quiz->my_attempts->count() > 0 ? 'bg-amber-100' : 'bg-slate-100') }}">
                @if($bestAttempt)
                <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                @elseif($quiz->my_attempts->count() > 0)
                <svg class="w-6 h-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                @else
                <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"/></svg>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-0.5">
                    <h3 class="font-semibold text-slate-900 truncate">{{ $quiz->title }}</h3>
                    @if($quiz->lesson)
                    <span class="text-xs text-slate-400 hidden sm:inline">· {{ $quiz->lesson->title }}</span>
                    @endif
                </div>
                <div class="flex flex-wrap gap-x-4 gap-y-0.5 text-xs text-slate-400">
                    <span>{{ $quiz->questions->count() }} questions</span>
                    <span>Pass: {{ $quiz->pass_percentage }}%</span>
                    @if($quiz->time_limit_minutes)<span>{{ $quiz->time_limit_minutes }} min</span>@endif
                    @if($bestAttempt)
                    <span class="text-emerald-600 font-semibold">Best: {{ round(($bestAttempt->score / max($bestAttempt->max_score,1))*100) }}%</span>
                    @elseif($lastAttempt)
                    <span class="text-amber-600">Last: {{ round(($lastAttempt->score / max($lastAttempt->max_score,1))*100) }}%</span>
                    @endif
                    <span>{{ $quiz->my_attempts->count() }}/{{ $quiz->max_attempts }} attempts used</span>
                </div>
            </div>
            <div class="flex-shrink-0">
                @if($attemptsLeft > 0)
                <a href="{{ route('student.quizzes.show', $quiz) }}" class="btn-primary btn-sm">
                    {{ $quiz->my_attempts->count() > 0 ? 'Retry' : 'Start' }} Quiz
                </a>
                @else
                <span class="text-xs text-slate-400">No attempts left</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
</x-app-layout>
