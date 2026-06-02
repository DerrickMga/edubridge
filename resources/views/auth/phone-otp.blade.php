<x-guest-layout>
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-[#25D366] flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 leading-tight">Verify your WhatsApp</h2>
                <p class="text-slate-500 text-sm">We'll send a 6-digit code to confirm your number</p>
            </div>
        </div>
    </div>

    @if (!session('otp_sent'))
        {{-- ── Step 1: Enter phone number ── --}}
        <form method="POST" action="{{ route('phone.verify.send') }}" class="space-y-5">
            @csrf

            <div class="form-group">
                <label for="phone" class="form-label">WhatsApp number</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 text-sm font-medium select-none">+</span>
                    <input id="phone" type="tel" name="phone"
                        value="{{ old('phone', auth()->user()->phone ? ltrim(auth()->user()->phone, '+') : '') }}"
                        required autofocus
                        placeholder="263 8612 166754"
                        class="form-input pl-7 @error('phone') border-red-400 @enderror"
                        maxlength="16">
                </div>
                <p class="text-xs text-slate-400 mt-1">Include country code without spaces, e.g. 2637712345678</p>
                @error('phone')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-[#25D366] hover:bg-[#1ebe5d] text-white font-semibold transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
                Send OTP via WhatsApp
            </button>
        </form>

    @else
        {{-- ── Step 2: Enter the OTP ── --}}
        <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm font-semibold text-emerald-800">OTP sent!</p>
                <p class="text-xs text-emerald-700 mt-0.5">
                    We sent a 6-digit code to <span class="font-mono font-bold">{{ session('otp_phone') }}</span>.
                    It expires in <strong>10 minutes</strong>.
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('phone.verify.confirm') }}" class="space-y-5">
            @csrf

            <div class="form-group">
                <label for="code" class="form-label">6-digit code</label>
                <input id="code" type="text" name="code" inputmode="numeric" pattern="\d{6}"
                    required autofocus maxlength="6"
                    placeholder="······"
                    class="form-input tracking-[0.4em] text-center text-xl font-bold @error('code') border-red-400 @enderror">
                @error('code')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold transition-colors">
                Verify &amp; Continue
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('phone.verify') }}" class="text-sm text-slate-500 hover:text-slate-700 underline underline-offset-2">
                Wrong number? Go back
            </a>
        </div>
    @endif

    <div class="mt-6 text-center">
        <form method="POST" action="{{ route('phone.verify.skip') }}">
            @csrf
            <button type="submit" class="text-sm text-slate-400 hover:text-slate-600 transition-colors">
                Skip for now
            </button>
        </form>
    </div>
</x-guest-layout>
