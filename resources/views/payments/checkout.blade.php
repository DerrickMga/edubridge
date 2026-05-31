<x-app-layout>
    <x-slot name="title">Enrol &mdash; {{ $course->title }}</x-slot>

    <div class="max-w-2xl mx-auto" x-data="checkout()">

        <div class="page-header">
            <h1 class="page-title">Complete Enrolment</h1>
            <p class="page-subtitle">Choose your access period and preferred payment method.</p>
        </div>

        @if(session(''error''))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
            {{ session(''error'') }}
        </div>
        @endif

        {{-- Course summary card --}}
        @php
            $color = match(strtolower($course->subject ?? '''')) {
                ''mathematics'',''maths'' => ''from-blue-500 to-indigo-600'',
                ''english language'' => ''from-purple-500 to-violet-600'',
                ''biology'',''combined science'' => ''from-emerald-500 to-teal-600'',
                ''chemistry'' => ''from-teal-400 to-emerald-600'',
                ''physics'' => ''from-cyan-500 to-blue-600'',
                ''history'' => ''from-amber-400 to-orange-500'',
                ''geography'' => ''from-teal-500 to-cyan-600'',
                ''business studies'',''commerce'' => ''from-orange-400 to-rose-500'',
                default => ''from-slate-500 to-slate-700'',
            };
        @endphp
        <div class="card overflow-hidden mb-6">
            <div class="h-20 bg-gradient-to-br {{ $color }} px-5 flex items-center gap-3">
                <span class="text-white text-2xl font-bold">{{ $course->subject }}</span>
            </div>
            <div class="p-5 flex items-start justify-between gap-4">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">{{ $course->title }}</h3>
                    <p class="text-xs text-slate-400 mt-1">{{ $course->grade_level }} &middot; By {{ $course->teacher->name }}</p>
                </div>
                <div class="text-right shrink-0">
                    <p class="text-xs text-slate-400">Base price</p>
                    <p class="text-xl font-extrabold text-emerald-700">${{ number_format($course->price_usd, 2) }}</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route(''payments.initiate'', $course) }}" id="checkoutForm">
            @csrf
            <input type="hidden" name="provider" x-model="selectedProvider">
            <input type="hidden" name="access_period" x-model="selectedPeriod">

            {{-- Step 1: Access Period --}}
            <div class="card p-5 mb-5">
                <h2 class="section-title mb-1">Step 1 &mdash; How long do you need access?</h2>
                <p class="text-xs text-slate-400 mb-4">Longer periods offer better value. You can renew at any time.</p>
                <div class="grid grid-cols-2 gap-3">
                    @foreach($periodPricing as $p)
                    <label class="cursor-pointer">
                        <input type="radio" value="{{ $p[''key''] }}" x-model="selectedPeriod" class="sr-only">
                        <div class="border-2 rounded-xl p-4 transition-all"
                             :class="selectedPeriod === ''{{ $p[''key''] }}'' ? ''border-emerald-600 bg-emerald-50'' : ''border-slate-200 hover:border-slate-300''">
                            <div class="flex justify-between items-start mb-2">
                                <span class="text-sm font-semibold text-slate-700">{{ $p[''label''] }}</span>
                                @if($p[''key''] === ''annual'')
                                <span class="text-[10px] font-bold bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full">BEST VALUE</span>
                                @elseif($p[''key''] === ''lifetime'')
                                <span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full">FULL ACCESS</span>
                                @endif
                            </div>
                            <p class="text-xl font-extrabold text-emerald-700">${{ number_format($p[''usd''], 2) }}</p>
                            @if(($p[''zwg''] ?? 0) > 0)
                            <p class="text-xs text-slate-400 mt-0.5">ZWG {{ number_format($p[''zwg''], 0) }}</p>
                            @endif
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Step 2: Payment method --}}
            <div class="card p-5 mb-5">
                <h2 class="section-title mb-1">Step 2 &mdash; Choose payment method</h2>
                <p class="text-xs text-slate-400 mb-4">All payments are processed securely.</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @foreach([
                        [''stripe'',   ''💳'', ''Stripe'',   ''Card (USD)'',       ''Visa / Mastercard / Amex''],
                        [''payfast'',  ''🏦'', ''PayFast'',  ''Card or EFT (ZAR)'',''South African banks''],
                        [''paynow_zw'',''🏛'', ''Paynow'',   ''Bank (ZWG)'',       ''Zimbabwe banks''],
                        [''ecocash'',  ''📱'', ''EcoCash'',  ''Mobile (ZWG)'',     ''Econet wallet''],
                        [''innbucks'', ''💚'', ''InnBucks'', ''Mobile (USD)'',     ''Steward Bank''],
                    ] as [$val, $icon, $label, $desc, $sub])
                    <label class="cursor-pointer">
                        <input type="radio" value="{{ $val }}" x-model="selectedProvider" class="sr-only">
                        <div class="border-2 rounded-xl p-3 transition-all"
                             :class="selectedProvider === ''{{ $val }}'' ? ''border-emerald-600 bg-emerald-50'' : ''border-slate-200 hover:border-slate-300''">
                            <div class="text-2xl mb-1.5">{{ $icon }}</div>
                            <p class="font-semibold text-sm text-slate-800">{{ $label }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $desc }}</p>
                            <p class="text-[10px] text-slate-300 mt-0.5">{{ $sub }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Order summary --}}
            <div class="card p-5 mb-5 bg-slate-50">
                <h2 class="section-title mb-3">Order Summary</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Course</span>
                        <span class="font-medium text-slate-800 text-right max-w-[200px] truncate">{{ $course->title }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Access period</span>
                        <span class="font-medium text-slate-800 capitalize" x-text="periodLabel"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Payment via</span>
                        <span class="font-medium text-slate-800 capitalize" x-text="providerLabel"></span>
                    </div>
                    <div class="border-t border-slate-200 pt-2 mt-2 flex justify-between">
                        <span class="font-bold text-slate-700">Total</span>
                        <span class="font-extrabold text-emerald-700 text-base" x-text="selectedPrice"></span>
                    </div>
                </div>
            </div>

            <button type="submit" :disabled="!selectedProvider || !selectedPeriod"
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-4 rounded-xl text-base transition disabled:opacity-40 disabled:cursor-not-allowed">
                Proceed to payment →
            </button>
            <a href="{{ route(''courses.index'') }}" class="block text-center text-sm text-slate-400 hover:text-slate-600 mt-3">Cancel</a>
        </form>
    </div>

    @push(''scripts'')
    <script>
    function checkout() {
        const periods = @json($periodPricing);
        return {
            selectedProvider: ''stripe'',
            selectedPeriod: ''lifetime'',
            get periodLabel() {
                const p = periods.find(x => x.key === this.selectedPeriod);
                return p ? p.label : ''—'';
            },
            get providerLabel() {
                const map = {stripe:''Stripe (Card)'', payfast:''PayFast'', paynow_zw:''Paynow ZW'', ecocash:''EcoCash'', innbucks:''InnBucks''};
                return map[this.selectedProvider] || this.selectedProvider;
            },
            get selectedPrice() {
                const p = periods.find(x => x.key === this.selectedPeriod);
                if (!p) return ''—'';
                if ([''paynow_zw'',''ecocash''].includes(this.selectedProvider)) {
                    return ''ZWG '' + p.zwg.toLocaleString();
                }
                return ''$'' + p.usd.toFixed(2);
            },
        };
    }
    </script>
    @endpush

</x-app-layout>