<x-app-layout>
    <x-slot name="title">Enrolment Successful!</x-slot>

    <div class="max-w-md mx-auto text-center py-16">
        <div class="w-20 h-20 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
        </div>
        <h1 class="text-2xl font-extrabold text-slate-900 mb-2">You're enrolled!</h1>
        <p class="text-slate-500 mb-8">Welcome to <span class="font-semibold text-slate-800">{{ $course->title }}</span>. Start learning now.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('student.dashboard') }}" class="btn-primary">Go to Dashboard</a>
            <a href="{{ route('courses.index') }}" class="btn-secondary">Browse more courses</a>
        </div>
    </div>
</x-app-layout>
