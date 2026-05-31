<x-app-layout>
    <x-slot name="title">Teaching Equipment & Loans</x-slot>

    <div class="max-w-4xl mx-auto space-y-8" x-data="{ tab: 'requirements' }">

        {{-- Header --}}
        <div class="page-header">
            <div>
                <h1 class="page-title">Teaching Equipment</h1>
                <p class="page-subtitle">Meet the technical requirements to deliver quality live sessions, and apply for equipment loans if needed.</p>
            </div>
            @if($profile)
                <span class="{{ $profile->statusBadgeClass() }} text-sm px-3 py-1">{{ $profile->statusLabel() }}</span>
            @endif
        </div>

        {{-- Tabs --}}
        <div class="flex gap-1 border-b border-slate-200">
            <button @click="tab='requirements'" :class="tab==='requirements' ? 'border-b-2 border-emerald-500 text-emerald-700 font-semibold' : 'text-slate-500 hover:text-slate-700'" class="px-4 py-2 text-sm transition-colors">Requirements</button>
            <button @click="tab='profile'" :class="tab==='profile' ? 'border-b-2 border-emerald-500 text-emerald-700 font-semibold' : 'text-slate-500 hover:text-slate-700'" class="px-4 py-2 text-sm transition-colors">My Setup</button>
            <button @click="tab='loan'" :class="tab==='loan' ? 'border-b-2 border-emerald-500 text-emerald-700 font-semibold' : 'text-slate-500 hover:text-slate-700'" class="px-4 py-2 text-sm transition-colors">Equipment Loan</button>
            @if($loans->count())
            <button @click="tab='history'" :class="tab==='history' ? 'border-b-2 border-emerald-500 text-emerald-700 font-semibold' : 'text-slate-500 hover:text-slate-700'" class="px-4 py-2 text-sm transition-colors">My Applications ({{ $loans->count() }})</button>
            @endif
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
            <svg class="w-4 h-4 flex-shrink-0 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            {{ session('success') }}
        </div>
        @endif

        {{-- TAB: Requirements --}}
        <div x-show="tab === 'requirements'" x-cloak>
            <div class="card p-6 space-y-6">
                <div>
                    <h2 class="section-title mb-1">Minimum Technical Requirements</h2>
                    <p class="text-sm text-slate-500">All EduBridge teachers must meet these standards to host live sessions and serve students effectively.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Requirement cards --}}
                    @php
                    $reqs = [
                        [
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 0 1 7.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.566 14.587-5.566 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 0 1 1.06 0Z"/>',
                            'title' => 'Stable Internet Connection',
                            'desc'  => 'Minimum 10 Mbps download / 5 Mbps upload. Fibre or cable preferred. Mobile data acceptable only if consistently stable.',
                            'tips'  => ['Use a wired (ethernet) connection when possible', 'Test your speed at fast.com before sessions', 'Avoid public or shared WiFi for live classes'],
                        ],
                        [
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3"/>',
                            'title' => 'Laptop / Desktop with Camera',
                            'desc'  => 'A laptop or desktop computer capable of running Zoom, Google Meet, or similar. Built-in or external webcam at minimum 720p resolution.',
                            'tips'  => ['720p resolution minimum, 1080p recommended', 'Ensure camera is clean and positioned at eye level', 'Test camera 10 minutes before each session'],
                        ],
                        [
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/>',
                            'title' => 'Proper Lighting',
                            'desc'  => 'Your face should be clearly lit and visible at all times. Natural light from a window (facing you, not behind you) or a dedicated ring light.',
                            'tips'  => ['Light source should face you, never behind you', 'Avoid harsh shadows or bright windows in the background', 'A $20–$35 ring light makes a significant difference'],
                        ],
                        [
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75M7.5 21h9M12 2.25c-1.657 0-3 1.343-3 3v9a3 3 0 1 0 6 0v-9c0-1.657-1.343-3-3-3Z"/>',
                            'title' => 'Noise-Canceling Headset',
                            'desc'  => 'A headset or earphones with a built-in microphone. Noise cancellation is essential to eliminate background sounds and deliver clear audio.',
                            'tips'  => ['Position microphone close to your mouth but not directly in front (to avoid plosives)', 'Test audio in Zoom/Meet before class', 'Affordable options: Mpow, Logitech H390, JBL Tune'],
                        ],
                    ];
                    @endphp

                    @foreach($reqs as $i => $req)
                    @php
                        $reqStatuses = $profile ? $profile->requirementsStatus() : [];
                        $isMet = isset($reqStatuses[$i]) ? $reqStatuses[$i]['met'] : null;
                    @endphp
                    <div class="border rounded-2xl p-5 {{ $isMet === true ? 'border-emerald-200 bg-emerald-50' : ($isMet === false ? 'border-red-100 bg-red-50' : 'border-slate-200 bg-white') }}">
                        <div class="flex items-start gap-3 mb-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $isMet === true ? 'bg-emerald-100' : 'bg-slate-100' }}">
                                <svg class="w-5 h-5 {{ $isMet === true ? 'text-emerald-600' : 'text-slate-500' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">{!! $req['icon'] !!}</svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="font-semibold text-slate-800 text-sm">{{ $req['title'] }}</p>
                                    @if($isMet === true)
                                        <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                    @elseif($isMet === false)
                                        <svg class="w-4 h-4 text-red-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">{{ $req['desc'] }}</p>
                            </div>
                        </div>
                        <ul class="space-y-1">
                            @foreach($req['tips'] as $tip)
                            <li class="text-xs text-slate-500 flex items-start gap-1.5">
                                <span class="text-emerald-400 mt-0.5">›</span>{{ $tip }}
                            </li>
                            @endforeach
                        </ul>
                        @if(isset($reqStatuses[$i]) && $reqStatuses[$i]['detail'])
                        <p class="mt-2 text-xs font-medium {{ $isMet ? 'text-emerald-700' : 'text-slate-600' }} bg-white/60 rounded-lg px-2 py-1">
                            Your setup: {{ $reqStatuses[$i]['detail'] }}
                        </p>
                        @endif
                    </div>
                    @endforeach
                </div>

                <div class="flex items-center gap-3 bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 text-sm text-blue-800">
                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                    <span>Don't have all the equipment yet? You may be eligible for an <button @click="tab='loan'" class="underline font-semibold">interest-free equipment loan</button> from EduBridge.</span>
                </div>

                <div class="flex gap-3">
                    <button @click="tab='profile'" class="btn btn-primary btn-sm">Update My Setup →</button>
                </div>
            </div>
        </div>

        {{-- TAB: My Setup (self-declaration form) --}}
        <div x-show="tab === 'profile'" x-cloak>
            <div class="card p-6">
                <h2 class="section-title mb-1">My Equipment Setup</h2>
                <p class="text-sm text-slate-500 mb-6">Declare your current equipment so EduBridge can assess your teaching readiness and match you with the right support.</p>

                <form method="POST" action="{{ route('teacher.equipment.save-profile') }}" class="space-y-6">
                    @csrf

                    {{-- Internet --}}
                    <div class="border border-slate-200 rounded-2xl p-5 space-y-4">
                        <h3 class="font-semibold text-sm text-slate-700 flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 0 1 7.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.566 14.587-5.566 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 0 1 1.06 0Z"/></svg>
                            Internet Connection
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="form-group">
                                <label class="form-label">Do you have stable internet?</label>
                                <select name="has_stable_internet" class="form-select" required>
                                    <option value="1" {{ old('has_stable_internet', $profile?->has_stable_internet) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ old('has_stable_internet', $profile?->has_stable_internet) == '0' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Connection Type</label>
                                <select name="internet_type" class="form-select">
                                    <option value="">Select…</option>
                                    @foreach(['fibre' => 'Fibre', 'cable' => 'Cable', 'dsl' => 'DSL', 'mobile_4g' => 'Mobile 4G', 'mobile_5g' => 'Mobile 5G', 'satellite' => 'Satellite', 'other' => 'Other'] as $val => $lbl)
                                    <option value="{{ $val }}" {{ old('internet_type', $profile?->internet_type) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Download Speed (Mbps)</label>
                                <input type="number" name="internet_speed_mbps" class="form-input" min="1" max="10000" placeholder="e.g. 25" value="{{ old('internet_speed_mbps', $profile?->internet_speed_mbps) }}">
                            </div>
                        </div>
                    </div>

                    {{-- Device --}}
                    <div class="border border-slate-200 rounded-2xl p-5 space-y-4">
                        <h3 class="font-semibold text-sm text-slate-700 flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0H3"/></svg>
                            Computer & Camera
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="form-group">
                                <label class="form-label">Laptop or Desktop?</label>
                                <select name="has_laptop_or_desktop" class="form-select" required>
                                    <option value="1" {{ old('has_laptop_or_desktop', $profile?->has_laptop_or_desktop) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ old('has_laptop_or_desktop', $profile?->has_laptop_or_desktop) == '0' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Device Type</label>
                                <select name="device_type" class="form-select">
                                    <option value="">Select…</option>
                                    <option value="laptop"  {{ old('device_type', $profile?->device_type) === 'laptop'  ? 'selected' : '' }}>Laptop</option>
                                    <option value="desktop" {{ old('device_type', $profile?->device_type) === 'desktop' ? 'selected' : '' }}>Desktop</option>
                                    <option value="tablet"  {{ old('device_type', $profile?->device_type) === 'tablet'  ? 'selected' : '' }}>Tablet</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Operating System</label>
                                <input type="text" name="device_os" class="form-input" placeholder="e.g. Windows 11, macOS Sonoma" value="{{ old('device_os', $profile?->device_os) }}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">Do you have a working camera?</label>
                                <select name="has_camera" class="form-select" required>
                                    <option value="1" {{ old('has_camera', $profile?->has_camera) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ old('has_camera', $profile?->has_camera) == '0' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Camera Type</label>
                                <select name="camera_type" class="form-select">
                                    <option value="">Select…</option>
                                    <option value="built_in"       {{ old('camera_type', $profile?->camera_type) === 'built_in'       ? 'selected' : '' }}>Built-in (laptop)</option>
                                    <option value="external_webcam"{{ old('camera_type', $profile?->camera_type) === 'external_webcam' ? 'selected' : '' }}>External Webcam</option>
                                    <option value="phone"          {{ old('camera_type', $profile?->camera_type) === 'phone'          ? 'selected' : '' }}>Phone Camera</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Lighting --}}
                    <div class="border border-slate-200 rounded-2xl p-5 space-y-4">
                        <h3 class="font-semibold text-sm text-slate-700 flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/></svg>
                            Lighting
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">Do you have proper lighting?</label>
                                <select name="has_proper_lighting" class="form-select" required>
                                    <option value="1" {{ old('has_proper_lighting', $profile?->has_proper_lighting) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ old('has_proper_lighting', $profile?->has_proper_lighting) == '0' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Lighting Type</label>
                                <select name="lighting_type" class="form-select">
                                    <option value="">Select…</option>
                                    @foreach(['natural' => 'Natural Light', 'ring_light' => 'Ring Light', 'softbox' => 'Softbox', 'led_panel' => 'LED Panel', 'desk_lamp' => 'Desk Lamp', 'other' => 'Other'] as $val => $lbl)
                                    <option value="{{ $val }}" {{ old('lighting_type', $profile?->lighting_type) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Headset --}}
                    <div class="border border-slate-200 rounded-2xl p-5 space-y-4">
                        <h3 class="font-semibold text-sm text-slate-700 flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75M7.5 21h9M12 2.25c-1.657 0-3 1.343-3 3v9a3 3 0 1 0 6 0v-9c0-1.657-1.343-3-3-3Z"/></svg>
                            Headset / Microphone
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">Do you have a noise-canceling headset?</label>
                                <select name="has_noise_canceling_headset" class="form-select" required>
                                    <option value="1" {{ old('has_noise_canceling_headset', $profile?->has_noise_canceling_headset) ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ old('has_noise_canceling_headset', $profile?->has_noise_canceling_headset) == '0' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Headset Model (optional)</label>
                                <input type="text" name="headset_model" class="form-input" placeholder="e.g. Logitech H390, AirPods Pro" value="{{ old('headset_model', $profile?->headset_model) }}">
                            </div>
                        </div>
                    </div>

                    @foreach($errors->all() as $error)
                    <p class="form-error">{{ $error }}</p>
                    @endforeach

                    <div class="flex gap-3">
                        <button type="submit" class="btn btn-primary">Save My Setup</button>
                        @if(!$profile || $profile->status !== 'meets_requirements')
                        <button type="button" @click="tab='loan'" class="btn btn-secondary">Apply for Equipment Loan →</button>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- TAB: Loan Application --}}
        <div x-show="tab === 'loan'" x-cloak x-data="loanCalculator()">
            <div class="space-y-6">

                {{-- Active loan warning --}}
                @php $activeLoan = $loans->whereIn('status', ['pending','under_review','approved','disbursed','repaying'])->first(); @endphp
                @if($activeLoan)
                <div class="alert alert-warn flex items-center gap-3">
                    <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                    <span>You have an active loan application (Ref: <strong>{{ $activeLoan->reference_number }}</strong>, Status: <span class="{{ $activeLoan->statusBadgeClass() }}">{{ $activeLoan->statusLabel() }}</span>). You cannot submit a new application while one is active.</span>
                </div>
                @else

                <div class="card p-6">
                    <div class="mb-5">
                        <h2 class="section-title mb-1">Equipment Loan Application</h2>
                        <p class="text-sm text-slate-500">EduBridge offers teachers interest-free equipment loans repaid via monthly deductions from your settlement earnings. Typical processing time: 3–5 business days.</p>
                    </div>

                    <form method="POST" action="{{ route('teacher.equipment.apply-loan') }}" class="space-y-6">
                        @csrf

                        {{-- Items --}}
                        <div class="form-group">
                            <label class="form-label">Equipment Items Requested <span class="text-red-500">*</span></label>
                            <p class="text-xs text-slate-400 mb-3">Select all items you need. Estimated costs shown for reference.</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach(App\Models\EquipmentLoanApplication::availableItems() as $key => $item)
                                <label class="flex items-center gap-3 border border-slate-200 rounded-xl p-3 cursor-pointer hover:border-emerald-300 has-[:checked]:border-emerald-400 has-[:checked]:bg-emerald-50 transition-colors">
                                    <input type="checkbox" name="items_requested[]" value="{{ $key }}"
                                        {{ in_array($key, old('items_requested', [])) ? 'checked' : '' }}
                                        class="rounded text-emerald-600"
                                        x-model="selectedItems"
                                        @change="recalculate">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-slate-700">{{ $item['label'] }}</p>
                                        <p class="text-xs text-slate-400">~${{ $item['est_usd'] }}</p>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                            @error('items_requested') <p class="form-error mt-2">{{ $message }}</p> @enderror
                        </div>

                        {{-- Live estimate --}}
                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 space-y-2 text-sm" x-show="selectedItems.length > 0">
                            <p class="font-semibold text-slate-700">Estimated Loan Summary</p>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Estimated equipment cost</span>
                                <span class="font-medium">$<span x-text="estimatedTotal"></span></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Monthly repayment (over <span x-text="months"></span> months)</span>
                                <span class="font-semibold text-emerald-700">$<span x-text="monthly"></span>/mo</span>
                            </div>
                            <p class="text-xs text-slate-400">* Interest-free. Exact amount and schedule confirmed upon approval.</p>
                        </div>

                        {{-- Amount & period --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="form-group">
                                <label class="form-label">Amount Requested (USD) <span class="text-red-500">*</span></label>
                                <input type="number" name="amount_requested_usd" class="form-input" min="10" max="1000" step="1"
                                    placeholder="e.g. 430"
                                    value="{{ old('amount_requested_usd') }}"
                                    :value="estimatedTotal"
                                    required>
                                @error('amount_requested_usd') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">Preferred Repayment Period <span class="text-red-500">*</span></label>
                                <select name="repayment_period_months" class="form-select" required x-model.number="months" @change="recalculate">
                                    @foreach([3 => '3 months', 6 => '6 months', 9 => '9 months', 12 => '12 months', 18 => '18 months', 24 => '24 months'] as $val => $lbl)
                                    <option value="{{ $val }}" {{ old('repayment_period_months', 12) == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                                @error('repayment_period_months') <p class="form-error">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Purpose --}}
                        <div class="form-group">
                            <label class="form-label">Why do you need this equipment? <span class="text-red-500">*</span></label>
                            <textarea name="purpose" rows="3" class="form-textarea" required minlength="30" placeholder="Describe your current situation and how this equipment will help you teach more effectively…">{{ old('purpose') }}</textarea>
                            @error('purpose') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        {{-- Employment context --}}
                        <div class="form-group">
                            <label class="form-label">Teaching Context</label>
                            <textarea name="employment_context" rows="2" class="form-textarea" placeholder="How many courses do you teach? How many students? What are your expected monthly earnings on EduBridge?">{{ old('employment_context') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Submit Loan Application</button>
                    </form>
                </div>
                @endif
            </div>
        </div>

        {{-- TAB: Loan History --}}
        @if($loans->count())
        <div x-show="tab === 'history'" x-cloak>
            <div class="card overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h2 class="section-title">My Loan Applications</h2>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($loans as $loan)
                    <div class="px-6 py-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="font-semibold text-sm text-slate-800">{{ implode(', ', $loan->requestedItemLabels()) }}</p>
                                    <span class="{{ $loan->statusBadgeClass() }}">{{ $loan->statusLabel() }}</span>
                                </div>
                                <p class="text-xs text-slate-400 mt-0.5">Ref: {{ $loan->reference_number }} · Submitted {{ $loan->created_at->format('d M Y') }}</p>
                            </div>
                            <p class="text-lg font-bold text-slate-800">${{ number_format($loan->amount_requested_usd, 2) }}</p>
                        </div>
                        @if($loan->approved_amount_usd)
                        <div class="bg-emerald-50 border border-emerald-100 rounded-lg px-3 py-2 text-xs text-emerald-700 grid grid-cols-3 gap-2">
                            <div><p class="text-emerald-500">Approved Amount</p><p class="font-semibold">${{ number_format($loan->approved_amount_usd, 2) }}</p></div>
                            <div><p class="text-emerald-500">Monthly Repayment</p><p class="font-semibold">${{ number_format($loan->monthly_repayment_usd, 2) }}/mo</p></div>
                            <div><p class="text-emerald-500">Period</p><p class="font-semibold">{{ $loan->approved_months }} months</p></div>
                        </div>
                        @endif
                        @if($loan->admin_notes)
                        <p class="text-xs text-slate-500 bg-slate-50 rounded-lg px-3 py-2">Admin note: {{ $loan->admin_notes }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>
</x-app-layout>

@push('scripts')
<script>
function loanCalculator() {
    const prices = @json(collect(App\Models\EquipmentLoanApplication::availableItems())->map(fn($i) => $i['est_usd']));
    return {
        selectedItems: [],
        months: 12,
        estimatedTotal: 0,
        monthly: '0.00',
        recalculate() {
            this.estimatedTotal = this.selectedItems.reduce((sum, key) => sum + (prices[key] || 0), 0);
            this.monthly = this.months > 0 ? (this.estimatedTotal / this.months).toFixed(2) : '0.00';
        }
    }
}
</script>
@endpush
