<x-app-layout>
    <x-slot name="title">Enrol — {{ $course->title }}</x-slot>

    <div class="max-w-lg mx-auto">
        <div class="page-header">
            <h1 class="page-title">Complete Enrolment</h1>
            <p class="page-subtitle">You're one step away from accessing this course.</p>
        </div>

        {{-- Course summary --}}
        @php
            $color = match(strtolower($course->subject ?? '')) {
                'mathematics','maths' => 'from-blue-500 to-indigo-600',
                'english language' => 'from-purple-500 to-violet-600',
                'biology','combined science' => 'from-emerald-500 to-teal-600',
                'chemistry' => 'from-teal-400 to-emerald-600',
                'physics' => 'from-cyan-500 to-blue-600',
                'history' => 'from-amber-400 to-orange-500',
                'geography' => 'from-teal-500 to-cyan-600',
                'business studies','commerce' => 'from-orange-400 to-rose-500',
                default => 'from-slate-500 to-slate-700',
            };
        @endphp
        <div class="card overflow-hidden mb-6">
            <div class="h-24 bg-gradient-to-br {{ $color }} p-4 flex items-end">
                <span class="text-white font-bold">{{ $course->subject }}</span>
            </div>
            <div class="p-5 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-slate-900">{{ $course->title }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $course->grade_level }} · By {{ $course->teacher->name }}</p>
                </div>
                <div class="text-right">
                    @if($course->price_usd > 0)
                    <p class="text-xl font-extrabold text-emerald-700">${{ number_format($course->price_usd, 2) }}</p>
                    <p class="text-xs text-slate-400">ZWG {{ number_format($course->price_zwg ?? 0, 0) }}</p>
                    @else
                    <p class="badge-green text-sm">Free</p>
                    @endif
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('payments.initiate', $course) }}" class="space-y-5">
            @csrf

            @if($course->price_usd > 0)
            {{-- Payment method --}}
            <div class="card p-5">
                <h2 class="section-title mb-4">Payment method</h2>
                <div class="grid grid-cols-2 gap-3">
                    @foreach([
                        ['stripe',  '💳', 'Stripe',   'USD card payment',        'Most currencies'],
                        ['paynow',  '🏦', 'Paynow',   'ZWG bank transfer',       'Zimbabwe banks'],
                        ['ecocash', '📱', 'EcoCash',  'ZWG mobile money',        'Econet wallet'],
                        ['innbucks','💚', 'InnBucks', 'USD mobile money',        'Steward Bank'],
                    ] as [$val, $icon, $label, $desc, $sub])
                    <label class="cursor-pointer">
                        <input type="radio" name="payment_method" value="{{ $val }}" class="sr-only peer" {{ $val === 'stripe' ? 'checked' : '' }}>
                        <div class="border-2 rounded-xl p-4 border-slate-200 peer-checked:border-emerald-600 peer-checked:bg-emerald-50 transition-all">
                            <div class="text-2xl mb-2">{{ $icon }}</div>
                            <p class="font-semibold text-sm text-slate-800">{{ $label }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $desc }}</p>
                            <p class="text-[10px] text-slate-300 mt-0.5">{{ $sub }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            @else
            <input type="hidden" name="payment_method" value="free">
            @endif

            <button type="submit" class="btn-primary w-full justify-center text-base py-3">
                @if($course->price_usd > 0)
                    Proceed to payment →
                @else
                    Enrol for free →
                @endif
            </button>
            <a href="{{ route('courses.index') }}" class="btn-secondary w-full justify-center">Cancel</a>
        </form>
    </div>
</x-app-layout>
