<x-app-layout>
    <x-slot name="title">Platform Pricing &mdash; Admin</x-slot>

    <div class="max-w-xl mx-auto">

        <div class="page-header">
            <div>
                <h1 class="page-title">Platform Pricing</h1>
                <p class="page-subtitle">These rates apply to all courses. Changes take effect immediately.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary text-sm">
                &larr; Dashboard
            </a>
        </div>

        @if(session('success'))
        <div class="mb-5 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-medium">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.pricing.update') }}">
            @csrf
            @method('PATCH')

            <div class="card p-6 space-y-5">

                <div class="pb-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wide">Access Period Prices (USD)</h2>
                </div>

                @foreach($fields as $key => $meta)
                @php $isRate = $key === 'price_zwg_rate'; @endphp
                <div class="form-group">
                    <label class="form-label" for="{{ $key }}">{{ $meta['label'] }}</label>
                    <div class="relative">
                        @if(!$isRate)
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">$</span>
                        @endif
                        <input
                            type="number"
                            id="{{ $key }}"
                            name="{{ $key }}"
                            value="{{ old($key, $settings[$key] ?? '') }}"
                            min="{{ $meta['min'] }}"
                            step="{{ $isRate ? '1' : '0.01' }}"
                            class="form-input {{ $isRate ? '' : 'pl-7' }}"
                            required
                        >
                        @if($isRate)
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">ZWG</span>
                        @endif
                    </div>
                    @if(!$isRate)
                    <p class="form-hint">Students pay this amount in USD for {{ strtolower($meta['label']) }} access.</p>
                    @else
                    <p class="form-hint">Used to display ZWG equivalent prices to Zimbabwean students.</p>
                    @endif
                </div>
                @endforeach

                {{-- Preview --}}
                <div class="rounded-xl bg-slate-50 border border-slate-100 p-4 text-sm">
                    <p class="font-semibold text-slate-600 mb-2">Current pricing (live)</p>
                    <table class="w-full text-slate-700">
                        <thead>
                            <tr class="text-xs text-slate-400 uppercase">
                                <th class="text-left pb-1">Period</th>
                                <th class="text-right pb-1">USD</th>
                                <th class="text-right pb-1">ZWG</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @php
                                $rate = (float)($settings['price_zwg_rate'] ?? 30);
                                $rows = [
                                    'price_hourly'  => '1 Hour',
                                    'price_monthly' => '1 Month',
                                    'price_termly'  => '1 Term',
                                ];
                            @endphp
                            @foreach($rows as $k => $rowLabel)
                            @php $usd = (float)($settings[$k] ?? 0); @endphp
                            <tr>
                                <td class="py-1.5">{{ $rowLabel }}</td>
                                <td class="py-1.5 text-right font-medium text-emerald-700">${{ number_format($usd, 2) }}</td>
                                <td class="py-1.5 text-right text-slate-400">{{ number_format($usd * $rate, 0) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>

            <div class="mt-5 flex justify-end">
                <button type="submit" class="btn-primary">
                    Save Changes
                </button>
            </div>
        </form>

    </div>
</x-app-layout>
