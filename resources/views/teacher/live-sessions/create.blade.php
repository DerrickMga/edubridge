<x-app-layout>
    <x-slot name="title">Schedule Live Class</x-slot>

    <div class="max-w-2xl">
        {{-- Header --}}
        <div class="page-header flex items-center gap-3">
            <a href="{{ route('teacher.dashboard') }}"
               class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition-colors flex-shrink-0">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </a>
            <div>
                <h1 class="page-title">Schedule Live Class</h1>
                <p class="page-subtitle">Set up a Zoom, Google Meet, or Calendly session for your students.</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-5 flex gap-3 rounded-xl bg-red-50 border border-red-200 p-4">
            <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
            <ul class="text-sm text-red-700 space-y-0.5 list-disc list-inside">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('teacher.live-sessions.store') }}" method="POST" class="space-y-5"
              x-data="{ provider: '{{ old('provider', 'Meet') }}' }">
            @csrf

            {{-- Platform --}}
            <div class="card p-6">
                <h3 class="font-semibold text-slate-900 mb-4">Meeting Platform</h3>
                <div>
                    <div class="grid grid-cols-4 gap-2 mb-4">
                        @foreach([['Meet','Google Meet','🎥'],['Zoom','Zoom','📹'],['Calendly','Calendly','📅'],['Other','Other','🔗']] as [$val,$label,$ico])
                        <label class="relative cursor-pointer">
                            <input type="radio" name="provider" value="{{ $val }}" x-model="provider"
                                   class="peer sr-only" {{ old('provider','Meet') === $val ? 'checked' : '' }}>
                            <div class="border-2 rounded-xl p-2.5 text-center transition-all duration-150 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 border-slate-200 hover:border-slate-300 select-none">
                                <span class="text-xl block mb-0.5">{{ $ico }}</span>
                                <p class="text-xs font-medium text-slate-600">{{ $label }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    <p class="text-xs font-medium text-slate-500 mb-2">Quick launch — open in new tab, then paste the link below:</p>
                    <div class="flex flex-wrap gap-2">
                        <a href="https://meet.google.com/new" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 text-xs bg-white border border-slate-200 hover:border-green-400 hover:text-green-700 text-slate-500 rounded-lg px-2.5 py-1.5 font-medium transition-colors">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>New Google Meet
                        </a>
                        <a href="https://zoom.us/start/videomeeting" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 text-xs bg-white border border-slate-200 hover:border-blue-400 hover:text-blue-700 text-slate-500 rounded-lg px-2.5 py-1.5 font-medium transition-colors">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>New Zoom
                        </a>
                        <a href="https://calendly.com" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-1.5 text-xs bg-white border border-slate-200 hover:border-teal-400 hover:text-teal-700 text-slate-500 rounded-lg px-2.5 py-1.5 font-medium transition-colors">
                            <span class="w-2 h-2 rounded-full bg-teal-500"></span>Calendly
                        </a>
                    </div>
                </div>
            </div>

            {{-- Session Details --}}
            <div class="card p-6 space-y-5">
                <h3 class="font-semibold text-slate-900">Session Details</h3>

                <div class="form-group">
                    <label class="form-label" for="course_id">Course</label>
                    <select name="course_id" id="course_id" class="form-select" required>
                        <option value="">Select a course...</option>
                        @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                            {{ $course->title }} &mdash; {{ $course->subject }}
                        </option>
                        @endforeach
                    </select>
                    @if($courses->isEmpty())
                    <p class="form-hint text-amber-600">
                        <a href="{{ route('teacher.courses.browse') }}" class="underline">Choose a course first</a> before scheduling a session.
                    </p>
                    @endif
                </div>

                <div class="form-group">
                    <label class="form-label" for="title">Session Title</label>
                    <input type="text" name="title" id="title" class="form-input"
                           placeholder="e.g. O-Level Maths — Quadratic Equations revision"
                           value="{{ old('title') }}" required>
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div class="form-group">
                        <label class="form-label" for="scheduled_at">Date &amp; Time</label>
                        <input type="datetime-local" name="scheduled_at" id="scheduled_at"
                               class="form-input"
                               value="{{ old('scheduled_at') }}"
                               min="{{ now()->addMinutes(10)->format('Y-m-d\TH:i') }}"
                               required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="duration_minutes">Duration</label>
                        <select name="duration_minutes" id="duration_minutes" class="form-select" required>
                            @foreach([30 => '30 min', 45 => '45 min', 60 => '1 hour', 90 => '1 hr 30 min', 120 => '2 hours', 180 => '3 hours'] as $mins => $label)
                            <option value="{{ $mins }}" {{ old('duration_minutes', 60) == $mins ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Meeting Link (hidden for Zoom — auto-created) --}}
            <div class="card p-6 space-y-4">

                <h3 class="font-semibold text-slate-900">Meeting Link</h3>

                {{-- Zoom auto-create notice --}}
                <div x-show="provider === 'Zoom'"
                     class="flex items-start gap-3 rounded-xl bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-800">
                    <span class="text-lg flex-shrink-0">📹</span>
                    <div>
                        <p class="font-semibold">Zoom meeting will be created automatically</p>
                        <p class="text-xs text-blue-600 mt-0.5">A Zoom meeting link with waiting room and cloud recording enabled will be generated when you click "Schedule Session". Leave the URL field blank.</p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="meeting_url">
                        Meeting URL
                        <span x-show="provider === 'Zoom'" class="text-slate-400 font-normal">(optional — leave blank to auto-create)</span>
                    </label>
                    <input type="url" name="meeting_url" id="meeting_url" class="form-input"
                           placeholder="https://meet.google.com/abc-defg-hij"
                           value="{{ old('meeting_url') }}">
                    <p class="form-hint" x-show="provider !== 'Zoom'">Paste the Google Meet or Calendly join link. Students will see a "Join" button.</p>
                </div>
                <div class="form-group" x-show="provider !== 'Zoom'">
                    <label class="form-label" for="meeting_id">Meeting ID <span class="text-slate-400 font-normal">(optional)</span></label>
                    <input type="text" name="meeting_id" id="meeting_id" class="form-input"
                           placeholder="e.g. 123 456 7890"
                           value="{{ old('meeting_id') }}">
                </div>

                @error('zoom')
                <p class="text-sm text-red-600 font-medium">⚠️ {{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 justify-end pt-2">
                <a href="{{ route('teacher.dashboard') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                    Schedule Session
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
