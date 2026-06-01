@extends('layouts.app')
@section('page-title', $bundle->title)

@section('content')
<div class="max-w-4xl mx-auto space-y-5">
    @if($bundle->thumbnail)<img src="{{ $bundle->thumbnail }}" class="w-full h-56 object-cover rounded-xl" alt="">@endif

    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $bundle->title }}</h1>
            @if($bundle->description)<p class="text-sm text-slate-600 mt-2">{{ $bundle->description }}</p>@endif
        </div>
        <div class="text-right">
            <p class="text-3xl font-extrabold text-slate-900">${{ number_format((float) $bundle->price_usd, 2) }}</p>
            @auth
            <form method="POST" action="{{ route('bundles.buy', $bundle) }}" class="mt-2">
                @csrf
                <button class="w-full px-5 py-2 rounded-lg bg-emerald-600 text-white font-semibold hover:bg-emerald-700">Buy bundle</button>
            </form>
            @else
            <a href="{{ route('login') }}" class="inline-block mt-2 px-5 py-2 rounded-lg bg-emerald-600 text-white font-semibold">Sign in to buy</a>
            @endauth
        </div>
    </div>

    <div class="card p-5">
        <h2 class="font-semibold text-slate-800 mb-3">Included courses</h2>
        <ul class="divide-y divide-slate-100">
            @foreach($bundle->courses as $c)
            <li class="py-3 flex items-center justify-between">
                <a href="{{ route('courses.show', $c) }}" class="hover:underline">{{ $c->title }}</a>
                <span class="text-xs text-slate-400">${{ number_format((float) $c->price_usd, 2) }}</span>
            </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection
