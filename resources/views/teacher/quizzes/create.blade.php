<x-app-layout>
    <x-slot name="title">Create Quiz</x-slot>

    <div class="max-w-3xl" x-data="{
        questions: [{ type: 'mcq', question: '', options: ['','','',''], correct_answer: '', explanation: '', points: 1 }],
        addQuestion() {
            this.questions.push({ type: 'mcq', question: '', options: ['','','',''], correct_answer: '', explanation: '', points: 1 });
        },
        removeQuestion(i) {
            if (this.questions.length > 1) this.questions.splice(i, 1);
        },
        addOption(i) {
            this.questions[i].options.push('');
        },
        removeOption(i, j) {
            if (this.questions[i].options.length > 2) this.questions[i].options.splice(j, 1);
        },
    }">
        <div class="page-header flex items-center gap-3 mb-6">
            <a href="{{ route('teacher.courses.show', $course) }}"
               class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </a>
            <div>
                <h1 class="page-title">Create Quiz</h1>
                <p class="page-subtitle">{{ $course->title }}</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-5 flex gap-3 rounded-xl bg-red-50 border border-red-200 p-4">
            <ul class="text-sm text-red-700 list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('teacher.quizzes.store', $course) }}" method="POST" class="space-y-6">
            @csrf

            {{-- Quiz Metadata --}}
            <div class="card p-6 space-y-5">
                <h3 class="font-semibold text-slate-900">Quiz Details</h3>
                <div class="grid sm:grid-cols-2 gap-5">
                    <div class="form-group sm:col-span-2">
                        <label class="form-label">Quiz Title</label>
                        <input type="text" name="title" class="form-input" value="{{ old('title') }}" required placeholder="e.g. Chapter 3 Check-in">
                    </div>
                    <div class="form-group sm:col-span-2">
                        <label class="form-label">Description <span class="text-slate-400 font-normal">(optional)</span></label>
                        <textarea name="description" class="form-textarea" rows="2" placeholder="What should students know before taking this quiz?">{{ old('description') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Time Limit (minutes) <span class="text-slate-400 font-normal">(0 = unlimited)</span></label>
                        <input type="number" name="time_limit_minutes" class="form-input" value="{{ old('time_limit_minutes', 0) }}" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Pass Score (%)</label>
                        <input type="number" name="pass_percentage" class="form-input" value="{{ old('pass_percentage', 60) }}" min="1" max="100" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Max Attempts</label>
                        <input type="number" name="max_attempts" class="form-input" value="{{ old('max_attempts', 3) }}" min="1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Linked Lesson <span class="text-slate-400 font-normal">(optional)</span></label>
                        <select name="lesson_id" class="form-select">
                            <option value="">— No specific lesson —</option>
                            @foreach($lessons as $lesson)
                            <option value="{{ $lesson->id }}" {{ old('lesson_id') == $lesson->id ? 'selected' : '' }}>{{ $lesson->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group flex items-center gap-6 sm:col-span-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="show_answers_after" value="1" class="accent-emerald-500" {{ old('show_answers_after') ? 'checked' : '' }}>
                            <span class="text-sm text-slate-600">Show correct answers after submission</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_published" value="1" class="accent-emerald-500" {{ old('is_published') ? 'checked' : '' }}>
                            <span class="text-sm text-slate-600">Publish immediately</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Questions --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="section-title">Questions (<span x-text="questions.length"></span>)</h3>
                    <button type="button" @click="addQuestion()" class="btn-secondary btn-sm">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Add Question
                    </button>
                </div>

                <template x-for="(q, i) in questions" :key="i">
                    <div class="card p-5 space-y-4">
                        <div class="flex items-start gap-3">
                            <span class="mt-2 w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-500 flex-shrink-0" x-text="i+1"></span>
                            <div class="flex-1 space-y-3">
                                <div class="flex gap-3">
                                    <div class="flex-1 form-group mb-0">
                                        <label class="form-label text-xs">Question</label>
                                        <input type="text" :name="`questions[${i}][question]`" x-model="q.question" class="form-input" required placeholder="What is...?">
                                    </div>
                                    <div class="w-32 form-group mb-0">
                                        <label class="form-label text-xs">Points</label>
                                        <input type="number" :name="`questions[${i}][points]`" x-model="q.points" class="form-input" min="1" value="1">
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="form-label text-xs">Type</label>
                                    <select :name="`questions[${i}][type]`" x-model="q.type" class="form-select">
                                        <option value="mcq">Multiple Choice</option>
                                        <option value="true_false">True / False</option>
                                        <option value="short_answer">Short Answer</option>
                                    </select>
                                </div>

                                {{-- MCQ Options --}}
                                <div x-show="q.type === 'mcq'" class="space-y-2">
                                    <label class="form-label text-xs">Options</label>
                                    <template x-for="(opt, j) in q.options" :key="j">
                                        <div class="flex gap-2">
                                            <input type="text" :name="`questions[${i}][options][]`" x-model="q.options[j]"
                                                   class="form-input flex-1 text-sm" :placeholder="`Option ${j+1}`">
                                            <button type="button" @click="removeOption(i,j)"
                                                    x-show="q.options.length > 2"
                                                    class="w-8 h-9 rounded-lg border border-slate-200 flex items-center justify-center text-slate-400 hover:text-red-500 transition-colors">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </template>
                                    <button type="button" @click="addOption(i)" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">
                                        + Add option
                                    </button>
                                    <div class="form-group mt-2 mb-0">
                                        <label class="form-label text-xs">Correct Answer (paste the exact option text)</label>
                                        <input type="text" :name="`questions[${i}][correct_answer]`" x-model="q.correct_answer" class="form-input text-sm" required>
                                    </div>
                                </div>

                                {{-- True/False --}}
                                <div x-show="q.type === 'true_false'" class="form-group mb-0">
                                    <label class="form-label text-xs">Correct Answer</label>
                                    <select :name="`questions[${i}][correct_answer]`" x-model="q.correct_answer" class="form-select">
                                        <option value="True">True</option>
                                        <option value="False">False</option>
                                    </select>
                                </div>

                                {{-- Short answer --}}
                                <div x-show="q.type === 'short_answer'" class="form-group mb-0">
                                    <label class="form-label text-xs">Model Answer</label>
                                    <input type="text" :name="`questions[${i}][correct_answer]`" x-model="q.correct_answer" class="form-input text-sm" placeholder="Exact expected answer...">
                                </div>

                                <div class="form-group mb-0">
                                    <label class="form-label text-xs">Explanation <span class="text-slate-400 font-normal">(shown to students after)</span></label>
                                    <input type="text" :name="`questions[${i}][explanation]`" x-model="q.explanation" class="form-input text-sm" placeholder="Optional explanation...">
                                </div>
                            </div>
                            <button type="button" @click="removeQuestion(i)" x-show="questions.length > 1"
                                    class="mt-1 w-7 h-7 rounded-lg border border-slate-200 flex items-center justify-center text-slate-400 hover:text-red-500 hover:border-red-300 transition-colors flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex gap-3 justify-end pt-2">
                <a href="{{ route('teacher.courses.show', $course) }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Create Quiz</button>
            </div>
        </form>
    </div>
</x-app-layout>
