<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-extrabold text-slate-900">Create your account</h2>
        <p class="text-slate-500 text-sm mt-1">Join EduBridge. It's free to get started.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        {{-- Role picker --}}
        <div class="form-group">
            <label class="form-label mb-2">I am a…</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="relative cursor-pointer">
                    <input type="radio" name="role" value="student" class="peer sr-only" {{ old('role','student') === 'student' ? 'checked' : '' }}>
                    <div class="border-2 border-slate-200 peer-checked:border-emerald-600 peer-checked:bg-emerald-50 rounded-xl p-4 transition-all">
                        <span class="text-2xl block mb-1">📚</span>
                        <p class="font-semibold text-slate-800 text-sm">Student</p>
                        <p class="text-xs text-slate-400 mt-0.5">Learn &amp; get AI help</p>
                    </div>
                </label>
                <label class="relative cursor-pointer">
                    <input type="radio" name="role" value="teacher" class="peer sr-only" {{ old('role') === 'teacher' ? 'checked' : '' }}>
                    <div class="border-2 border-slate-200 peer-checked:border-emerald-600 peer-checked:bg-emerald-50 rounded-xl p-4 transition-all">
                        <span class="text-2xl block mb-1">🎓</span>
                        <p class="font-semibold text-slate-800 text-sm">Tutor</p>
                        <p class="text-xs text-slate-400 mt-0.5">Create &amp; teach courses</p>
                    </div>
                </label>
            </div>
            @error('role')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div class="form-group">
            <label for="name" class="form-label">Full name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                class="form-input @error('name') border-red-400 @enderror" placeholder="Takudzwa Moyo" />
            @error('name')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="form-group">
            <label for="email" class="form-label">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                class="form-input @error('email') border-red-400 @enderror" placeholder="you@example.com" />
            @error('email')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                    class="form-input @error('password') border-red-400 @enderror" placeholder="8+ characters" />
                @error('password')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label for="password_confirmation" class="form-label">Confirm</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                    class="form-input" placeholder="Repeat password" />
            </div>
        </div>

        <button type="submit" class="btn-primary w-full justify-center py-3">Create account</button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-500">
        Already have an account?
        <a href="{{ route('login') }}" class="text-emerald-600 hover:text-emerald-700 font-semibold">Sign in</a>
    </p>
</x-guest-layout>
