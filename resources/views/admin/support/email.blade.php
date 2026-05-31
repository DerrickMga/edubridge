<x-app-layout>
    <x-slot name="title">Compose Email — Admin</x-slot>

    <div class="max-w-3xl space-y-6" x-data="{
        recipientType: 'all_teachers',
        recipientId: '',
        users: {{ Js::from($users) }},
        get filteredUsers() {
            return this.users;
        }
    }">
        <div class="page-header">
            <div>
                <h1 class="page-title">Compose Email</h1>
                <p class="page-subtitle">Send a direct email to users from the EduBridge platform address.</p>
            </div>
            <a href="{{ route('admin.support.index') }}" class="btn-secondary">← Inbox</a>
        </div>

        @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
        <div class="alert-error">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('admin.support.send-email') }}" class="card p-6 space-y-5">
            @csrf

            {{-- Recipient --}}
            <div>
                <label class="form-label">Recipients <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-3">
                    @foreach([
                        ['all_teachers', 'All Teachers'],
                        ['all_students', 'All Students'],
                        ['all_users',    'All Users'],
                        ['single',       'Specific User'],
                    ] as [$val, $label])
                    <label class="relative flex items-center gap-2 cursor-pointer border rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
                           :class="recipientType === '{{ $val }}' ? 'border-violet-500 bg-violet-50 text-violet-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50'">
                        <input type="radio" name="recipient_type" value="{{ $val }}" x-model="recipientType" class="sr-only">
                        <span>{{ $label }}</span>
                    </label>
                    @endforeach
                </div>

                {{-- Specific user picker --}}
                <div x-show="recipientType === 'single'" x-cloak>
                    <label class="form-label">Select User</label>
                    <select name="recipient_id" x-model="recipientId" class="form-input">
                        <option value="">— Choose a user —</option>
                        @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }}) — {{ ucfirst($u->role) }}</option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" name="recipient_id" x-show="recipientType !== 'single'" x-bind:value="''">
            </div>

            {{-- Subject --}}
            <div>
                <label class="form-label">Subject <span class="text-red-500">*</span></label>
                <input type="text" name="subject" value="{{ old('subject') }}" required class="form-input" placeholder="Email subject line">
                @error('subject')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Reply-to --}}
            <div>
                <label class="form-label">Reply-To Address <span class="text-slate-400 font-normal">(optional)</span></label>
                <input type="email" name="reply_to" value="{{ old('reply_to') }}" class="form-input" placeholder="e.g. support@kmgvitallinks.co.uk">
                <p class="text-xs text-slate-400 mt-1">Leave blank to use the default no-reply address.</p>
                @error('reply_to')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Body --}}
            <div>
                <label class="form-label">Message Body <span class="text-red-500">*</span></label>
                <p class="text-xs text-slate-400 mb-1.5">Plain text or basic HTML. Your message is automatically wrapped in the EduBridge branded email template.</p>
                <textarea name="body" rows="14" required class="form-input font-mono text-sm" placeholder="Write your email here...

You can use plain text (line breaks will be preserved) or paste HTML.

Example:
&lt;p&gt;Dear team,&lt;/p&gt;
&lt;p&gt;Please note that...&lt;/p&gt;">{{ old('body') }}</textarea>
                @error('body')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            {{-- Preview note --}}
            <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-xs text-amber-800">
                <strong>Sending from:</strong> EduBridge by KMG &lt;noreply@kmgvitallinks.co.uk&gt;<br>
                Emails are sent immediately via the platform SMTP relay. Large lists may take a moment.
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('admin.support.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/></svg>
                    Send Email
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
