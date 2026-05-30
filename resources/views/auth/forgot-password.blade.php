<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-extrabold text-slate-900">Reset your password</h2>
        <p class="text-slate-500 text-sm mt-1">Enter your email and we'll send a reset link.</p>
    </div>
    <x-auth-session-status class="mb-4" :status="session('status')" />
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf
        <div class="form-group">
            <label for="email" class="form-label">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                class="form-input @error('email') border-red-400 @enderror" placeholder="you@example.com" />
            @error('email')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <button type="submit" class="btn-primary w-full justify-center py-3">Send reset link</button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-500">
        Remember it? <a href="{{ route('login') }}" class="text-emerald-600 font-semibold hover:text-emerald-700">Sign in</a>
    </p>
</x-guest-layout>
