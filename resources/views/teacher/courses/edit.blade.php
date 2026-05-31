<x-app-layout>
    <x-slot name="title">Edit {{ $course->title }}</x-slot>

    <div class="max-w-2xl">
        <div class="page-header flex items-center gap-3 mb-6">
            <a href="{{ route('teacher.courses.show', $course) }}"
               class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </a>
            <div>
                <h1 class="page-title">Edit Course</h1>
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

        <form method="POST" action="{{ route('teacher.courses.update', $course) }}" class="space-y-5">
            @csrf @method('PUT')
            <div class="card p-6 space-y-5">
                {{-- Read-only course identity (set by curriculum admin) --}}
                <div class="rounded-xl bg-slate-50 border border-slate-200 px-4 py-3">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-1">Course</p>
                    <p class="text-base font-semibold text-slate-800">{{ $course->title }}</p>
                    <p class="text-sm text-slate-500 mt-0.5">{{ $course->subject }} &mdash; {{ $course->grade_level }}</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        @foreach(['draft','published','archived'] as $s)
                        <option value="{{ $s }}" {{ old('status', $course->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-textarea" rows="4">{{ old('description', $course->description) }}</textarea>
                </div>

                <div class="form-group">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        <p class="font-semibold text-slate-700 mb-1">Platform pricing applies to all courses</p>
                        <ul class="space-y-0.5 text-slate-500">
                            <li>1 Hour &mdash; <span class="font-medium text-slate-700">$1.00</span> / ZWG 30</li>
                            <li>1 Month &mdash; <span class="font-medium text-slate-700">$5.00</span> / ZWG 150</li>
                            <li>1 Term (3 months) &mdash; <span class="font-medium text-slate-700">$10.00</span> / ZWG 300</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 justify-end">
                <a href="{{ route('teacher.courses.show', $course) }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>

        {{-- Release course --}}
        <div class="mt-8 card border-amber-200 p-5">
            <h3 class="font-semibold text-amber-700 mb-1">Release Course</h3>
            <p class="text-sm text-slate-500 mb-4">Return this course to the available pool so another teacher can claim it. Students will retain their enrolments.</p>
            <form method="POST" action="{{ route('teacher.courses.release', $course) }}"
                  onsubmit="return confirm('Release this course back to the catalogue?')">
                @csrf
                <button type="submit" class="btn-secondary border-amber-300 text-amber-700 hover:bg-amber-50">Release Course</button>
            </form>
        </div>
    </div>
</x-app-layout>
