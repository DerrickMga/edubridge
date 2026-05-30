<x-app-layout>
    <x-slot name="title">{{ $quiz->title }} — Results</x-slot>

    <div class="max-w-4xl">
        <div class="page-header flex items-center justify-between gap-4 mb-6">
            <div>
                <a href="{{ route('teacher.quizzes.index', $course) }}"
                   class="inline-flex items-center gap-1 text-sm text-slate-400 hover:text-slate-700 mb-1 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                    All Quizzes
                </a>
                <h1 class="page-title">{{ $quiz->title }}</h1>
                <p class="page-subtitle">
                    {{ $quiz->questions->count() }} questions
                    · Pass: {{ $quiz->pass_percentage }}%
                    @if($quiz->time_limit_minutes) · {{ $quiz->time_limit_minutes }} min @endif
                    <span class="ml-1 {{ $quiz->is_published ? 'badge-green' : 'badge-slate' }}">{{ $quiz->is_published ? 'Published' : 'Draft' }}</span>
                </p>
            </div>
            <form action="{{ route('teacher.quizzes.toggle', [$course, $quiz]) }}" method="POST">
                @csrf @method('PATCH')
                <button type="submit" class="{{ $quiz->is_published ? 'btn-secondary' : 'btn-primary' }} btn-sm">
                    {{ $quiz->is_published ? 'Unpublish' : 'Publish' }}
                </button>
            </form>
        </div>

        {{-- Stats row --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="stat-card text-center">
                <p class="stat-value">{{ $stats['total'] }}</p>
                <p class="stat-label">Total Attempts</p>
            </div>
            <div class="stat-card text-center">
                <p class="stat-value text-emerald-600">{{ $stats['passed'] }}</p>
                <p class="stat-label">Passed
                    @if($stats['total'] > 0)
                    <span class="text-slate-400">({{ round($stats['passed'] / $stats['total'] * 100) }}%)</span>
                    @endif
                </p>
            </div>
            <div class="stat-card text-center">
                <p class="stat-value text-blue-600">{{ $stats['avg_pct'] }}%</p>
                <p class="stat-label">Avg Score</p>
            </div>
        </div>

        {{-- Attempts table --}}
        <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-semibold text-slate-900">Student Attempts</h2>
                <span class="text-sm text-slate-400">{{ $attempts->count() }} record{{ $attempts->count() !== 1 ? 's' : '' }}</span>
            </div>

            @if($attempts->isEmpty())
            <div class="p-10 text-center text-slate-400 text-sm">
                No attempts yet — share this quiz with your students.
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">
                            <th class="px-5 py-3">Student</th>
                            <th class="px-5 py-3 text-center">Attempt</th>
                            <th class="px-5 py-3 text-center">Score</th>
                            <th class="px-5 py-3 text-center">Result</th>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" x-data="{ open: null }">
                        @foreach($attempts as $attempt)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-3.5 font-medium text-slate-900">{{ $attempt->student->name }}</td>
                            <td class="px-5 py-3.5 text-center text-slate-500">{{ $attempt->attempt_number }}</td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="font-semibold {{ $attempt->passed ? 'text-emerald-600' : 'text-red-500' }}">
                                    {{ $attempt->score_percentage }}%
                                </span>
                                <span class="text-slate-400 text-xs"> ({{ $attempt->score }}/{{ $attempt->max_score }})</span>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <span class="{{ $attempt->passed ? 'badge-green' : 'badge-red' }}">
                                    {{ $attempt->passed ? 'Pass' : 'Fail' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-400 text-xs">
                                {{ $attempt->completed_at?->format('d M Y, g:ia') ?? '—' }}
                            </td>
                            <td class="px-5 py-3.5">
                                <button @click="open = (open === {{ $attempt->id }} ? null : {{ $attempt->id }})"
                                        class="text-xs text-emerald-600 hover:text-emerald-800 font-medium">
                                    <span x-text="open === {{ $attempt->id }} ? 'Hide ▲' : 'Review ▼'">Review ▼</span>
                                </button>
                            </td>
                        </tr>
                        {{-- Expanded answer review --}}
                        <tr x-show="open === {{ $attempt->id }}" class="bg-slate-50">
                            <td colspan="6" class="px-5 py-4">
                                <div class="space-y-2">
                                    @foreach($quiz->questions as $q)
                                    @php
                                        $studentAns = $attempt->answers[$q->id] ?? null;
                                        $correct    = $studentAns && strtolower(trim($studentAns)) === strtolower(trim($q->correct_answer));
                                    @endphp
                                    <div class="flex items-start gap-3 p-3 rounded-lg {{ $correct ? 'bg-emerald-50 border border-emerald-200' : 'bg-red-50 border border-red-200' }}">
                                        <span class="mt-0.5 flex-shrink-0 text-base">{{ $correct ? '✅' : '❌' }}</span>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-slate-800">{{ $q->question }}</p>
                                            <p class="text-xs mt-0.5 {{ $correct ? 'text-emerald-700' : 'text-red-700' }}">
                                                Student: <strong>{{ $studentAns ?? '(no answer)' }}</strong>
                                                @if(!$correct)
                                                · Correct: <strong>{{ $q->correct_answer }}</strong>
                                                @endif
                                            </p>
                                            @if($q->explanation)
                                            <p class="text-xs text-slate-500 mt-0.5">💡 {{ $q->explanation }}</p>
                                            @endif
                                        </div>
                                        <span class="text-xs text-slate-400 flex-shrink-0">{{ $q->points }} pt{{ $q->points !== 1 ? 's' : '' }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
