<x-app-layout>
    <x-slot name="title">My Profile</x-slot>

    <div class="max-w-3xl mx-auto space-y-6">

        <div class="page-header">
            <div>
                <h1 class="page-title">My Profile</h1>
                <p class="page-subtitle">Manage your account details, avatar, and public information.</p>
            </div>
        </div>

        {{-- Avatar --}}
        <div class="card p-6">
            <h2 class="section-title mb-5">Profile Picture</h2>
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf @method('PATCH')
                <div class="flex items-start gap-6" x-data="{ preview: null }">
                    <div class="flex-shrink-0">
                        <div class="w-24 h-24 rounded-2xl overflow-hidden border-2 border-slate-200">
                            <template x-if="preview">
                                <img :src="preview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!preview">
                                @if($user->avatar)
                                    <img src="{{ $user->avatar_url }}" class="w-full h-full object-cover" alt="{{ $user->name }}">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white text-3xl font-black uppercase">{{ substr($user->name, 0, 1) }}</div>
                                @endif
                            </template>
                        </div>
                    </div>
                    <div class="flex-1 space-y-3">
                        <label for="avatar" class="btn btn-secondary btn-sm cursor-pointer inline-flex">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                            Upload new photo
                        </label>
                        <input type="file" name="avatar" id="avatar" accept="image/jpg,image/jpeg,image/png,image/webp" class="sr-only"
                               @change="const f=$event.target.files[0]; if(f){const r=new FileReader();r.onload=e=>preview=e.target.result;r.readAsDataURL(f);}">
                        <p class="text-xs text-slate-400">JPG, PNG or WebP · Max 2 MB · Square photos work best</p>
                        @error('avatar') <p class="form-error">{{ $message }}</p> @enderror
                        <div class="flex items-center gap-3 pt-1">
                            <button type="submit" class="btn btn-primary btn-sm">Save photo</button>
                            @if($user->avatar)
                                <form method="POST" action="{{ route('profile.remove-avatar') }}" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-500 hover:text-red-700 transition-colors">Remove</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Personal Info --}}
        <form method="POST" action="{{ route('profile.update') }}" class="card p-6 space-y-5">
            @csrf @method('PATCH')
            <h2 class="section-title">Personal Information</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label" for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-input" required autocomplete="name">
                    @error('name') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-input" required autocomplete="email">
                    @if($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                        <p class="form-hint text-amber-600">⚠ Email not verified. <a href="{{ route('verification.send') }}" class="underline">Resend</a></p>
                    @endif
                    @error('email') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+263 77 ..." class="form-input" autocomplete="tel">
                </div>
                <div class="form-group">
                    <label class="form-label" for="country">Country</label>
                    <input type="text" id="country" name="country" value="{{ old('country', $user->country) }}" placeholder="Zimbabwe" class="form-input" autocomplete="country-name">
                </div>
                <div class="form-group">
                    <label class="form-label" for="city">City / Town</label>
                    <input type="text" id="city" name="city" value="{{ old('city', $user->city) }}" placeholder="Harare" class="form-input" autocomplete="address-level2">
                </div>
                @if($user->isTeacher())
                <div class="form-group">
                    <label class="form-label" for="qualification">Highest Qualification</label>
                    <input type="text" id="qualification" name="qualification" value="{{ old('qualification', $user->qualification) }}" placeholder="e.g. B.Ed Mathematics" class="form-input">
                </div>
                @endif
                @if($user->isStudent())
                <div class="form-group">
                    <label class="form-label" for="grade_level">Grade Level</label>
                    <select id="grade_level" name="grade_level" class="form-select">
                        <option value="">Select grade</option>
                        @foreach(['Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6','Grade 7','Form 1','Form 2','Form 3','Form 4','Form 5','Form 6 Lower','Form 6 Upper','A Level'] as $g)
                            <option value="{{ $g }}" @selected(old('grade_level', $user->grade_level) === $g)>{{ $g }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>
            <div class="form-group">
                <label class="form-label" for="bio">Bio</label>
                <textarea id="bio" name="bio" rows="3" class="form-textarea" placeholder="Tell learners a little about yourself...">{{ old('bio', $user->bio) }}</textarea>
                <p class="form-hint">Max 1,000 characters · Visible on your public profile</p>
                @error('bio') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="flex justify-end"><button type="submit" class="btn btn-primary">Save changes</button></div>
        </form>

        {{-- Social / Web links --}}
        <form method="POST" action="{{ route('profile.update') }}" class="card p-6 space-y-5">
            @csrf @method('PATCH')
            <h2 class="section-title">Links &amp; Social</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label" for="website">Website</label>
                    <input type="url" id="website" name="website" value="{{ old('website', $user->website) }}" placeholder="https://yoursite.com" class="form-input">
                    @error('website') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label" for="linkedin_url">LinkedIn URL</label>
                    <input type="url" id="linkedin_url" name="linkedin_url" value="{{ old('linkedin_url', $user->linkedin_url) }}" placeholder="https://linkedin.com/in/..." class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label" for="twitter_handle">Twitter / X Handle</label>
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">@</span>
                        <input type="text" id="twitter_handle" name="twitter_handle" value="{{ old('twitter_handle', $user->twitter_handle) }}" placeholder="yourhandle" class="form-input pl-8">
                    </div>
                </div>
            </div>
            <div class="flex justify-end"><button type="submit" class="btn btn-primary">Save links</button></div>
        </form>

        {{-- Change Password --}}
        <div class="card p-6">
            <h2 class="section-title mb-5">Change Password</h2>
            @include('profile.partials.update-password-form')
        </div>

        {{-- Danger Zone --}}
        <div class="card border-red-200 p-6">
            <h2 class="section-title text-red-700 mb-2">Danger Zone</h2>
            <p class="text-sm text-slate-500 mb-4">Permanently delete your account and all associated data. This cannot be undone.</p>
            @include('profile.partials.delete-user-form')
        </div>

    </div>
</x-app-layout>
