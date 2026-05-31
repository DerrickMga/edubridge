<x-app-layout>
    <x-slot name="title">Edit Live Session</x-slot>

    <div class="max-w-2xl">
        {{-- Header --}}
        <div class="page-header flex items-center gap-3">
            <a href="{{ route('teacher.dashboard') }}"
               class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center hover:bg-slate-50 transition-colors flex-shrink-0">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
            </a>
            <div>
                <h1 class="page-title">Edit Live Session</h1>
                <p class="page-subtitle">{{ $liveSession->title }}</p>
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

        <form action="{{ route('teacher.live-sessions.update', $liveSession) }}" method="POST" class="space-y-5"
              x-data="{ provider: '{{ old('provider', $liveSession->provider) }}' }">
            @csrf
            @method('PUT')

            {{-- Platform --}}
            <div class="card p-6">
                <h3 class="font-semibold text-slate-900 mb-4">Meeting Platform</h3>
                <div>
                    <div class="grid grid-cols-4 gap-2 mb-4">
                        @foreach([['Meet','Google Meet','🎥'],['Zoom','Zoom','📹'],['Calendly','Calendly','📅'],['Other','Other','🔗']] as [$val,$label,$ico])
                        <label class="relative cursor-pointer">
                            <input type="radio" name="provider" value="{{ $val }}" x-model="provider"
                                   class="peer sr-only" {{ old('provider', $liveSession->provider) === $val ? 'checked' : '' }}>
                            <div class="border-2 rounded-xl p-2.5 text-center transition-all duration-150 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 border-slate-200 hover:border-slate-300 select-none">
                                <span class="text-xl block mb-0.5">{{ $ico }}</span>
                                <p class="text-xs font-medium text-slate-600">{{ $label }}</p>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Session Details --}}
            <div class="card p-6 space-y-5">
                <h3 class="font-semibold text-slate-900">Session Details</h3>

                <div class="form-group">
                    <label class="form-label" for="course_id">Course</label>
                    <select name="course_id" id="course_id" class="form-select" required>
                        @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ old('course_id', $liveSession->course_id) == $course->id ? 'selected' : '' }}>
                            {{ $course->title }} &mdash; {{ $course->subject }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="title">Session Title</label>
                    <input type="text" name="title" id="title" class="form-input"
                           value="{{ old('title', $liveSession->title) }}" required>
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div class="form-group">
                        <label class="form-label" for="scheduled_at">Date &amp; Time</label>
                        <input type="datetime-local" name="scheduled_at" id="scheduled_at"
                               class="form-input"
                               value="{{ old('scheduled_at', $liveSession->scheduled_at->format('Y-m-d\TH:i')) }}"
                               required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="duration_minutes">Duration</label>
                        <select name="duration_minutes" id="duration_minutes" class="form-select" required>
                            @foreach([30 => '30 min', 45 => '45 min', 60 => '1 hour', 90 => '1 hr 30 min', 120 => '2 hours', 180 => '3 hours'] as $mins => $label)
                            <option value="{{ $mins }}" {{ old('duration_minutes', $liveSession->duration_minutes) == $mins ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select name="status" id="status" class="form-select" required>
                        @foreach(['scheduled' => 'Scheduled', 'ongoing' => 'Ongoing', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $label)
                        <option value="{{ $val }}" {{ old('status', $liveSession->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Meeting Link --}}
            <div class="card p-6 space-y-4">
                <h3 class="font-semibold text-slate-900">Meeting Link</h3>

                {{-- Zoom auto-create notice --}}
                @if($liveSession->provider === 'Zoom' && $liveSession->meeting_id)
                <div class="flex items-start gap-3 rounded-xl bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-800">
                    <span class="text-lg flex-shrink-0">📹</span>
                    <div>
                        <p class="font-semibold">Zoom meeting is active (ID: {{ $liveSession->meeting_id }})</p>
                        <p class="text-xs text-blue-600 mt-0.5">Saving changes will update the scheduled time in Zoom automatically. Clear the URL below only if you want to use a different link.</p>
                    </div>
                </div>
                @else
                <div x-show="provider === 'Zoom'"
                     class="flex items-start gap-3 rounded-xl bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-800">
                    <span class="text-lg flex-shrink-0">📹</span>
                    <div>
                        <p class="font-semibold">Zoom meeting will be created automatically</p>
                        <p class="text-xs text-blue-600 mt-0.5">Leave the URL blank to auto-create a Zoom meeting with cloud recording.</p>
                    </div>
                </div>
                @endif

                {{-- Google Meet notice --}}
                @if($liveSession->provider === 'Meet' && $liveSession->meeting_id)
                <div class="flex items-start gap-3 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                    <span class="text-lg flex-shrink-0">🎥</span>
                    <div>
                        <p class="font-semibold">Google Meet is active</p>
                        <p class="text-xs text-green-600 mt-0.5">Saving changes will update the event time in Google Calendar automatically.</p>
                    </div>
                </div>
                @else
                <div x-show="provider === 'Meet'"
                     class="flex items-start gap-3 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                    <span class="text-lg flex-shrink-0">🎥</span>
                    <div>
                        @if($meetConnected)
                        <p class="font-semibold">Google Meet will be created automatically</p>
                        <p class="text-xs text-green-600 mt-0.5">Leave the URL blank to auto-create a Meet link via your connected Google Calendar.</p>
                        @else
                        <p class="font-semibold">Connect Google to auto-create Meet sessions</p>
                        <p class="text-xs text-green-700 mt-0.5">
                            <a href="{{ route('teacher.youtube.connect') }}" class="underline font-semibold">Connect your Google account</a> to auto-generate Meet links, or paste a Meet URL below.
                        </p>
                        @endif
                    </div>
                </div>
                @endif

                <div class="form-group">
                    <label class="form-label" for="meeting_url">Meeting URL</label>
                    <input type="url" name="meeting_url" id="meeting_url" class="form-input"
                           placeholder="https://meet.google.com/abc-defg-hij"
                           value="{{ old('meeting_url', $liveSession->meeting_url) }}">
                </div>
                <div class="form-group" x-show="provider !== 'Zoom'">
                    <label class="form-label" for="meeting_id">Meeting ID <span class="text-slate-400 font-normal">(optional)</span></label>
                    <input type="text" name="meeting_id" id="meeting_id" class="form-input"
                           value="{{ old('meeting_id', $liveSession->meeting_id) }}">
                </div>

                @error('zoom')
                <p class="text-sm text-red-600 font-medium">⚠️ {{ $message }}</p>
                @enderror
                @error('meet')
                <p class="text-sm text-red-600 font-medium">⚠️ {{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3 justify-end pt-2">
                <a href="{{ route('teacher.dashboard') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">Save Changes</button>
            </div>
        </form>

        {{-- Danger zone --}}
        <div class="mt-8 card p-5 border-red-100 bg-red-50/50">
            <h4 class="font-semibold text-red-700 mb-1">Cancel Session</h4>
            <p class="text-sm text-slate-500 mb-3">This will remove the session from your dashboard and students will no longer see it.</p>
            <form action="{{ route('teacher.live-sessions.destroy', $liveSession) }}" method="POST"
                  onsubmit="return confirm('Are you sure you want to cancel this session?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger btn-sm">Cancel &amp; delete session</button>
            </form>
        </div>
    </div>
</x-app-layout>
