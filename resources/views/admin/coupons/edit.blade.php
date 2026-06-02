<x-app-layout>
    <x-slot name="title">{{ $coupon->exists ? 'Edit Coupon' : 'New Coupon' }} — Admin</x-slot>

    @php $isEdit = $coupon->exists; @endphp
    <div class="max-w-2xl space-y-5">
    <h1 class="text-2xl font-bold text-slate-900">{{ $isEdit ? 'Edit coupon' : 'New coupon' }}</h1>

    @if($errors->any())<div class="px-4 py-2 rounded-lg bg-rose-50 text-rose-800 text-sm">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>@endif

    <form method="POST" action="{{ $isEdit ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}" class="card p-5 space-y-4">
        @csrf
        @if($isEdit) @method('PUT') @endif

        <div class="grid grid-cols-2 gap-3 text-sm">
            <label><span class="block text-xs text-slate-500 uppercase mb-1">Code</span>
                <input name="code" required maxlength="40" pattern="[A-Z0-9_\-]+" value="{{ old('code', $coupon->code) }}" class="w-full px-3 py-2 rounded-lg border border-slate-200 uppercase font-mono">
            </label>
            <label><span class="block text-xs text-slate-500 uppercase mb-1">Type</span>
                <select name="type" class="w-full px-3 py-2 rounded-lg border border-slate-200">
                    <option value="percent" @selected(old('type', $coupon->type) === 'percent')>Percent off</option>
                    <option value="fixed" @selected(old('type', $coupon->type) === 'fixed')>Fixed amount off</option>
                </select>
            </label>
            <label><span class="block text-xs text-slate-500 uppercase mb-1">Value</span>
                <input name="value" type="number" step="0.01" min="0" required value="{{ old('value', $coupon->value) }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
            </label>
            <label><span class="block text-xs text-slate-500 uppercase mb-1">Currency (fixed)</span>
                <input name="currency" maxlength="8" value="{{ old('currency', $coupon->currency ?? 'USD') }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
            </label>
            <label class="col-span-2"><span class="block text-xs text-slate-500 uppercase mb-1">Description</span>
                <input name="description" maxlength="200" value="{{ old('description', $coupon->description) }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
            </label>
            <label class="col-span-2"><span class="block text-xs text-slate-500 uppercase mb-1">Scope</span>
                <select name="course_id" class="w-full px-3 py-2 rounded-lg border border-slate-200">
                    <option value="">Global (any course)</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}" @selected(old('course_id', $coupon->course_id) == $c->id)>{{ $c->title }}</option>
                    @endforeach
                </select>
            </label>
            <label><span class="block text-xs text-slate-500 uppercase mb-1">Max total uses</span>
                <input name="max_redemptions" type="number" min="1" value="{{ old('max_redemptions', $coupon->max_redemptions) }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
            </label>
            <label><span class="block text-xs text-slate-500 uppercase mb-1">Per-user limit</span>
                <input name="per_user_limit" type="number" min="1" required value="{{ old('per_user_limit', $coupon->per_user_limit ?? 1) }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
            </label>
            <label><span class="block text-xs text-slate-500 uppercase mb-1">Min order value</span>
                <input name="min_order_value" type="number" step="0.01" min="0" value="{{ old('min_order_value', $coupon->min_order_value) }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
            </label>
            <label><span class="block text-xs text-slate-500 uppercase mb-1">Active</span>
                <select name="is_active" class="w-full px-3 py-2 rounded-lg border border-slate-200">
                    <option value="1" @selected(old('is_active', $coupon->is_active ?? true))>Yes</option>
                    <option value="0" @selected(! old('is_active', $coupon->is_active ?? true))>No</option>
                </select>
            </label>
            <label><span class="block text-xs text-slate-500 uppercase mb-1">Starts at</span>
                <input name="starts_at" type="datetime-local" value="{{ old('starts_at', optional($coupon->starts_at)->format('Y-m-d\TH:i')) }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
            </label>
            <label><span class="block text-xs text-slate-500 uppercase mb-1">Expires at</span>
                <input name="expires_at" type="datetime-local" value="{{ old('expires_at', optional($coupon->expires_at)->format('Y-m-d\TH:i')) }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
            </label>
        </div>

        <div class="flex items-center justify-end gap-2">
            <a href="{{ route('admin.coupons.index') }}" class="px-3 py-2 text-sm text-slate-500 hover:underline">Cancel</a>
            <button class="px-3 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800">{{ $isEdit ? 'Save changes' : 'Create coupon' }}</button>
        </div>
    </form>
    </div>
</x-app-layout>
