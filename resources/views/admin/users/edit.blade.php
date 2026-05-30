<x-app-layout>
    <x-slot name="title">Edit User — {{ $user->name }}</x-slot>

    <div class="page-header flex items-center gap-3">
        <a href="{{ route('admin.users.index') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        </a>
        <div>
            <h1 class="page-title">Edit User</h1>
            <p class="page-subtitle">{{ $user->email }}</p>
        </div>
    </div>

    <div class="max-w-lg">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">
            @csrf @method('PUT')
            <div class="card p-6 space-y-4">
                <div class="form-group">
                    <label for="name" class="form-label">Full name</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-input @error('name') border-red-400 @enderror" />
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label for="email" class="form-label">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required class="form-input @error('email') border-red-400 @enderror" />
                    @error('email')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label for="role" class="form-label">Role</label>
                    <select id="role" name="role" class="form-select">
                        @foreach(['student','teacher','admin'] as $r)
                        <option value="{{ $r }}" {{ old('role', $user->role) === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="password" class="form-label">New password <span class="text-slate-400 font-normal">(leave blank to keep current)</span></label>
                    <input id="password" type="password" name="password" class="form-input @error('password') border-red-400 @enderror" autocomplete="new-password" />
                    @error('password')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn-primary">Save changes</button>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
