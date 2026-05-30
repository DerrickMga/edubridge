<x-app-layout>
    <x-slot name="title">Certificate Verification</x-slot>

    <div class="max-w-xl mx-auto">
        <div class="card p-8 text-center">
            <span class="text-5xl block mb-4">🏆</span>
            <h1 class="text-2xl font-bold text-slate-900 mb-2">Certificate Verified</h1>
            <p class="text-slate-500 mb-6">This EduBridge certificate is authentic.</p>

            <div class="bg-slate-50 rounded-2xl p-6 text-left space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-slate-400">Certificate No.</span><span class="font-mono font-semibold text-slate-700">{{ $cert->certificate_number }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Student</span><span class="font-semibold text-slate-700">{{ $cert->student->name }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Course</span><span class="font-semibold text-slate-700">{{ $cert->course->title }}</span></div>
                <div class="flex justify-between"><span class="text-slate-400">Issued</span><span class="font-semibold text-slate-700">{{ $cert->issued_at->format('d F Y') }}</span></div>
                @if($cert->final_score)
                <div class="flex justify-between"><span class="text-slate-400">Final Score</span><span class="font-semibold text-emerald-600">{{ $cert->final_score }}%</span></div>
                @endif
            </div>

            <div class="mt-6 flex items-center justify-center gap-2 text-emerald-600 text-sm font-medium">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                Verified by EduBridge Zimbabwe
            </div>
        </div>
    </div>
</x-app-layout>
