@extends('layouts.app')
@section('page-title', 'Redeem gift')

@section('content')
<div class="max-w-md mx-auto card p-6 mt-10">
    <h1 class="text-xl font-bold text-slate-900 mb-2">You've received a gift! 🎁</h1>
    @if($payment->gift_recipient_name)
        <p class="text-sm text-slate-500">For {{ $payment->gift_recipient_name }}</p>
    @endif
    <p class="mt-3 text-sm">Course: <strong>{{ optional($payment->course)->title }}</strong></p>
    @if($payment->gift_message)
        <blockquote class="mt-3 p-3 rounded bg-amber-50 text-sm italic text-amber-900">"{{ $payment->gift_message }}"</blockquote>
    @endif

    @if($payment->gift_redeemed_at)
        <p class="mt-4 text-sm text-rose-600">This gift was already redeemed on {{ $payment->gift_redeemed_at->format('d M Y') }}.</p>
    @else
        <form method="POST" action="{{ route('gifts.redeem', $payment->gift_token) }}" class="mt-5">
            @csrf
            <button class="w-full py-2 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700">Redeem &amp; enrol</button>
        </form>
    @endif
</div>
@endsection
