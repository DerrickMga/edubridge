<x-app-layout>
    <x-slot name="title">Certificate of Completion</x-slot>

    <div class="max-w-3xl mx-auto">
        @if($certificate)
        {{-- Printable certificate --}}
        <div class="card overflow-hidden print:shadow-none" id="certificate">
            <div class="bg-gradient-to-r from-emerald-600 to-teal-700 h-3"></div>
            <div class="p-12 text-center">
                <p class="text-sm font-bold tracking-[0.3em] text-slate-400 uppercase mb-6">EduBridge Zimbabwe</p>
                <p class="text-lg text-slate-500 mb-2">This is to certify that</p>
                <h1 class="text-4xl font-extrabold text-slate-900 mb-2">{{ $certificate->student->name }}</h1>
                <p class="text-lg text-slate-500 mb-2">has successfully completed</p>
                <h2 class="text-2xl font-bold text-emerald-700 mb-6">{{ $certificate->course->title }}</h2>
                <div class="flex justify-center gap-12 mb-8">
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide">Certificate No.</p>
                        <p class="font-mono font-semibold text-slate-700">{{ $certificate->certificate_number }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide">Issued</p>
                        <p class="font-semibold text-slate-700">{{ $certificate->issued_at->format('d F Y') }}</p>
                    </div>
                    @if($certificate->final_score)
                    <div>
                        <p class="text-xs text-slate-400 uppercase tracking-wide">Final Score</p>
                        <p class="font-semibold text-slate-700">{{ $certificate->final_score }}%</p>
                    </div>
                    @endif
                </div>
                <div class="border-t border-slate-100 pt-6">
                    <p class="text-xs text-slate-400">Verified at edubridge.co.zw/verify/{{ $certificate->certificate_number }}</p>
                </div>
            </div>
            <div class="bg-gradient-to-r from-emerald-600 to-teal-700 h-3"></div>
        </div>

        <div class="flex justify-center gap-3 mt-6 print:hidden">
            <button onclick="window.print()" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/></svg>
                Print / Save PDF
            </button>
            <a href="{{ route('student.dashboard') }}" class="btn-secondary">Back to dashboard</a>
        </div>
        @else
        <div class="card empty-state">
            <span class="empty-state-icon">🏆</span>
            <p class="empty-state-title">Certificate not available</p>
            <p class="empty-state-text">Complete all lessons in the course to earn your certificate.</p>
            <a href="{{ route('student.dashboard') }}" class="btn-primary">Back to dashboard</a>
        </div>
        @endif
    </div>
</x-app-layout>
