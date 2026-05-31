{{--
  Support Widget — floating bubble included at the bottom of app.blade.php
  Visible to authenticated students and teachers
--}}
@auth
@if(in_array(auth()->user()->role, ['student', 'teacher']))
<div x-data="{
    open: false,
    loading: false,
    done: false,
    ticketNum: '',
    form: { subject: '', message: '', category: 'general', priority: 'medium' },
    errors: {},
    async submit() {
        this.loading = true;
        this.errors = {};
        try {
            const res = await fetch('{{ route('support.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.form)
            });
            const json = await res.json();
            if (res.ok && json.success) {
                this.done = true;
                this.ticketNum = json.ticket_number;
                this.form = { subject: '', message: '', category: 'general', priority: 'medium' };
            } else if (json.errors) {
                this.errors = json.errors;
            }
        } catch(e) {}
        this.loading = false;
    },
    reset() { this.done = false; this.open = false; }
}" class="fixed bottom-5 right-5 z-50 flex flex-col items-end gap-3">

    {{-- Panel --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="w-[360px] bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-violet-600 to-violet-700 px-5 py-4 flex items-center justify-between">
            <div>
                <p class="text-white font-semibold text-sm">Support</p>
                <p class="text-violet-200 text-xs mt-0.5">We typically reply within a few hours</p>
            </div>
            <button @click="open = false" class="text-white/70 hover:text-white p-1">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="p-5">

            {{-- Success state --}}
            <div x-show="done" class="text-center py-4">
                <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                </div>
                <p class="text-sm font-semibold text-slate-800 mb-1">Ticket submitted!</p>
                <p class="text-xs text-slate-500 mb-1">Reference: <span class="font-mono font-bold text-violet-600" x-text="ticketNum"></span></p>
                <p class="text-xs text-slate-400 mb-4">Check your ticket history for updates.</p>
                <div class="flex gap-2 justify-center">
                    <a href="{{ route('support.index') }}" class="text-xs text-violet-600 hover:underline font-medium">View my tickets →</a>
                    <span class="text-slate-300">|</span>
                    <button @click="reset()" class="text-xs text-slate-500 hover:underline">Close</button>
                </div>
            </div>

            {{-- Form state --}}
            <div x-show="!done">
                <p class="text-xs text-slate-500 mb-3">Describe your issue and we'll get back to you.</p>

                <div class="space-y-3">
                    <div>
                        <input x-model="form.subject" type="text" placeholder="Subject *"
                               class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-violet-500 focus:border-transparent outline-none">
                        <p x-show="errors.subject" x-text="errors.subject?.[0]" class="text-xs text-red-500 mt-1"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <select x-model="form.category" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-violet-500 outline-none bg-white">
                                <option value="general">General</option>
                                <option value="technical">Technical</option>
                                <option value="billing">Billing</option>
                                <option value="course">Course</option>
                                <option value="complaint">Complaint</option>
                            </select>
                        </div>
                        <div>
                            <select x-model="form.priority" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-violet-500 outline-none bg-white">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <textarea x-model="form.message" rows="4" placeholder="Describe your issue... *"
                                  class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-violet-500 focus:border-transparent outline-none resize-none"></textarea>
                        <p x-show="errors.message" x-text="errors.message?.[0]" class="text-xs text-red-500 mt-1"></p>
                    </div>

                    <button @click="submit()" :disabled="loading"
                            class="w-full bg-violet-600 hover:bg-violet-700 disabled:opacity-60 text-white text-sm font-semibold py-2.5 rounded-lg transition-colors">
                        <span x-show="!loading">Submit Ticket</span>
                        <span x-show="loading">Sending…</span>
                    </button>

                    <p class="text-center text-xs text-slate-400">
                        <a href="{{ route('support.index') }}" class="text-violet-500 hover:underline">View my tickets</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Floating button --}}
    <button @click="open = !open"
            class="w-13 h-13 rounded-full bg-violet-600 hover:bg-violet-700 shadow-lg hover:shadow-xl transition-all flex items-center justify-center text-white"
            style="width:52px;height:52px;"
            title="Support">
        <svg x-show="!open" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z"/>
        </svg>
        <svg x-show="open" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>
@endif
@endauth
