@extends('layouts.app')

@section('page-title', $policy->title)

@section('content')
<div class="max-w-3xl space-y-5">
    <div>
        <p class="text-xs text-slate-400 uppercase tracking-wide">{{ str_replace('_', ' ', $policy->category) }} · v{{ $policy->version }}</p>
        <h1 class="text-2xl font-bold text-slate-900">{{ $policy->title }}</h1>
        <p class="text-xs text-slate-500 mt-1">Effective {{ optional($policy->effective_at)->format('d M Y') }}</p>
    </div>

    @if(session('success'))
        <div class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-800 text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="px-4 py-2 rounded-lg bg-rose-50 text-rose-800 text-sm">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
    @endif

    <div class="card p-5 prose prose-sm max-w-none text-slate-700 whitespace-pre-wrap leading-relaxed">{!! nl2br(e($policy->body)) !!}</div>

    @if($acked)
        <div class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-800 text-sm">✅ You have acknowledged this version.</div>
    @else
        <form method="POST" action="{{ route('teacher.policies.acknowledge', $policy) }}" class="card p-5">
            @csrf
            <label class="flex items-start gap-2 text-sm text-slate-700">
                <input type="checkbox" name="agree" value="1" required class="mt-0.5 rounded border-slate-300">
                <span>I have read and agree to <strong>{{ $policy->title }}</strong> (version {{ $policy->version }}).</span>
            </label>
            <div class="flex items-center justify-end mt-3">
                <button class="px-3 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800">Acknowledge</button>
            </div>
        </form>
    @endif

    <a href="{{ route('teacher.policies.index') }}" class="text-sm text-slate-500 hover:underline">← Back to policies</a>
</div>
@endsection
