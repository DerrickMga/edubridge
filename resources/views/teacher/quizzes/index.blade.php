<x-app-layout>
    <x-slot name="title">Quizzes — {{ $course->title }}</x-slot>

    <div class="max-w-3xl">
        <div class="page-header flex items-center justify-between gap-4 mb-6">
            <div>
                <a href="{{ route('teacher.courses.show', $course) }}"
                   class="inline-flex items-center gap-1 text-sm text-slate-400 hover:text-slate-700 mb-1 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                    {{ $course->title }}
                </a>
                <h1 class="page-title">Quizzes</h1>
                <p class="page-subtitle">{{ $quizzes->count() }} quiz{{ $quizzes->count() !== 1 ? 'zes' : '' }}</p>
            </div>
            <a href="{{ route('teacher.quizzes.create', $course) }}" class="btn-primary btn-sm">+ Create Quiz</a>
        </div>

        @if(session('success'))
        <div class="mb-5 flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            {{ session('success') }}
        </div>
        @endif

        @if($quizzes->isEmpty())
        <div class="card p-12 text-center">
            <p class="text-4xl mb-3">📝</p>
            <h3 class="font-semibold text-slate-700 mb-1">No quizzes yet</h3>
            <p class="text-sm text-slate-400 mb-5">Create your first quiz to start testing your students.</p>
            <a href="{{ route('teacher.quizzes.create', $course) }}" class="btn-primary btn-sm">Create Quiz</a>
        </div>
        @else
        <div class="space-y-3">
            @foreach($quizzes as $quiz)
            <div class="card p-5 flex items-center gap-5">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <h3 class="font-semibold text-slate-900 truncate">{{ $quiz->title }}</h3>
                        <span class="flex-shrink-0 {{ $quiz->is_published ? 'badge-green' : 'badge-slate' }} text-xs">
                            {{ $quiz->is_published ? 'Published' : 'Draft' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400">
                        {{ $quiz->questions->count() }} questions
                        @if($quiz->time_limit_minutes) · {{ $quiz->time_limit_minutes }} min @endif
                        · Pass: {{ $quiz->pass_percentage }}%
                        · {{ $quiz->attempts_count }} attempt{{ $quiz->attempts_count !== 1 ? 's' : '' }}
                        @if($quiz->attempts_count > 0)
                        · <span class="{{ $quiz->passed_count / $quiz->attempts_count >= 0.6 ? 'text-emerald-600' : 'text-amber-600' }} font-semibold">
                            {{ $quiz->attempts_count ? round($quiz->passed_count / $quiz->attempts_count * 100) : 0 }}% pass rate
                        </span>
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <form action="{{ route('teacher.quizzes.toggle', [$course, $quiz]) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="{{ $quiz->is_published ? 'btn-secondary' : 'btn-primary' }} btn-sm">
                            {{ $quiz->is_published ? 'Unpublish' : 'Publish' }}
                        </button>
                    </form>
                    <a href="{{ route('teacher.quizzes.show', [$course, $quiz]) }}" class="btn-secondary btn-sm">
                        Results →
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</x-app-layout>
