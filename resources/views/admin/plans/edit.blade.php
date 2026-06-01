@extends('layouts.app')
@section('page-title', $plan->exists ? 'Edit plan' : 'New plan')

@section('content')
@php $isEdit = $plan->exists; @endphp
<div class="max-w-2xl space-y-5">
    <h1 class="text-2xl font-bold text-slate-900">{{ $isEdit ? 'Edit plan' : 'New plan' }}</h1>

    @if($errors->any())<div class="px-4 py-2 rounded bg-rose-50 text-rose-800 text-sm">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>@endif

    <form method="POST" action="{{ $isEdit ? route('admin.plans.update', $plan) : route('admin.plans.store') }}" class="card p-5 space-y-4">
        @csrf
        @if($isEdit)@method('PUT')@endif

        <div class="grid grid-cols-2 gap-3 text-sm">
            <label class="col-span-2"><span class="block text-xs text-slate-500 uppercase mb-1">Name</span>
                <input name="name" required maxlength="100" value="{{ old('name', $plan->name) }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
            </label>
            <label><span class="block text-xs text-slate-500 uppercase mb-1">Slug</span>
                <input name="slug" maxlength="100" value="{{ old('slug', $plan->slug) }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
            </label>
            <label><span class="block text-xs text-slate-500 uppercase mb-1">Interval</span>
                <select name="interval" class="w-full px-3 py-2 rounded-lg border border-slate-200">
                    <option value="month" @selected(old('interval', $plan->interval) === 'month')>Monthly</option>
                    <option value="year"  @selected(old('interval', $plan->interval) === 'year')>Yearly</option>
                </select>
            </label>
            <label><span class="block text-xs text-slate-500 uppercase mb-1">Price USD</span>
                <input name="price_usd" type="number" step="0.01" min="0" required value="{{ old('price_usd', $plan->price_usd) }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
            </label>
            <label><span class="block text-xs text-slate-500 uppercase mb-1">Price ZWG</span>
                <input name="price_zwg" type="number" step="0.01" min="0" value="{{ old('price_zwg', $plan->price_zwg) }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
            </label>
            <label class="col-span-2"><span class="block text-xs text-slate-500 uppercase mb-1">Description</span>
                <textarea name="description" rows="3" class="w-full px-3 py-2 rounded-lg border border-slate-200">{{ old('description', $plan->description) }}</textarea>
            </label>
            <label><span class="block text-xs text-slate-500 uppercase mb-1">Active</span>
                <select name="is_active" class="w-full px-3 py-2 rounded-lg border border-slate-200">
                    <option value="1" @selected(old('is_active', $plan->is_active ?? true))>Yes</option>
                    <option value="0" @selected(! old('is_active', $plan->is_active ?? true))>No</option>
                </select>
            </label>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.plans.index') }}" class="px-3 py-2 text-sm text-slate-500 hover:underline">Cancel</a>
            <button class="px-3 py-2 text-sm rounded-lg bg-slate-900 text-white">{{ $isEdit ? 'Save' : 'Create' }}</button>
        </div>
    </form>
</div>
@endsection
