<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-extrabold text-slate-900">Welcome back</h2>
        <p class="text-slate-500 text-sm mt-1">Sign in to your EduBridge account</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf
        <div class="form-group">
            <label for="email" class="form-label">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                class="form-input @error('email') border-red-400 @enderror" placeholder="you@example.com" />
            @error('email')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="form-group">
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="form-label mb-0">Password</label>
                @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium">Forgot password?</a>
                @endif
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                class="form-input @error('password') border-red-400 @enderror" placeholder="••••••••" />
            @error('password')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="flex items-center gap-2">
            <input id="remember_me" type="checkbox" name="remember" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
            <label for="remember_me" class="text-sm text-slate-600">Remember me</label>
        </div>
        <button type="submit" class="btn-primary w-full justify-center py-3">Sign in</button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Don't have an account?
        <a href="{{ route('register') }}" class="text-emerald-600 hover:text-emerald-700 font-semibold">Create one free</a>
    </p>
</x-guest-layout>
