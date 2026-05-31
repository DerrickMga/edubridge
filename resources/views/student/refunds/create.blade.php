@extends('layouts.app')
@section('page-title', 'Request Refund')

@section('content')
<div class="max-w-2xl space-y-5">
    <h1 class="text-2xl font-bold text-slate-900">Request a refund</h1>
    <p class="text-sm text-slate-500">Tell us why you'd like a refund. Our team will review within 5 business days as per our refund policy.</p>

    @if($errors->any())
        <div class="px-4 py-2 rounded-lg bg-rose-50 text-rose-800 text-sm">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
    @endif

    <div class="card p-4">
        <p class="text-xs text-slate-400 uppercase">Order</p>
        <p class="font-semibold">{{ optional($payment->course)->title ?? 'Payment #'.$payment->id }}</p>
        <p class="text-xs text-slate-500 mt-1">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }} · paid {{ $payment->created_at->format('d M Y') }}</p>
    </div>

    <form method="POST" action="{{ route('student.refunds.store', $payment) }}" class="card p-5 space-y-3">
        @csrf
        <label class="block text-sm">
            <span class="block text-xs text-slate-500 uppercase mb-1">Reason (min 20 characters)</span>
            <textarea name="reason" required minlength="20" maxlength="2000" rows="6"
                      class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200"></textarea>
        </label>
        <div class="flex items-center justify-end gap-2">
            <a href="{{ route('student.refunds.index') }}" class="px-3 py-2 text-sm text-slate-500 hover:underline">Cancel</a>
            <button class="px-3 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800">Submit request</button>
        </div>
    </form>
</div>
@endsection
