<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-extrabold text-slate-900">Set new password</h2>
    </div>
    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}" />
        <div class="form-group">
            <label for="email" class="form-label">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus
                class="form-input @error('email') border-red-400 @enderror" />
            @error('email')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="form-group">
            <label for="password" class="form-label">New password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password"
                class="form-input @error('password') border-red-400 @enderror" />
            @error('password')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="form-group">
            <label for="password_confirmation" class="form-label">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                class="form-input" />
        </div>
        <button type="submit" class="btn-primary w-full justify-center py-3">Reset password</button>
    </form>
</x-guest-layout>
