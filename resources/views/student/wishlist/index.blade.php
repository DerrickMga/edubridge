@extends('layouts.app')
@section('page-title', 'My Wishlist')

@section('content')
<div class="space-y-5">
    <div class="flex items-end justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Wishlist</h1>
            <p class="text-sm text-slate-500">Courses you've saved for later.</p>
        </div>
        <a href="{{ route('courses.index') }}" class="text-sm text-slate-500 hover:underline">Browse courses →</a>
    </div>

    @if(session('success'))
        <div class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-800 text-sm">{{ session('success') }}</div>
    @endif

    @if($items->isEmpty())
        <div class="card p-8 text-center text-slate-500 text-sm">Your wishlist is empty. Tap <strong>♡ Save for later</strong> on any course to add it here.</div>
    @else
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($items as $c)
        <div class="card p-4 flex flex-col gap-2">
            <p class="text-xs text-slate-400 uppercase">{{ $c->subject }}</p>
            <h3 class="font-bold text-slate-900">{{ $c->title }}</h3>
            <p class="text-xs text-slate-500">By {{ optional($c->teacher)->name }}</p>
            <div class="text-sm mt-1">
                @if(($c->price_usd ?? 0) > 0)
                    <strong class="text-emerald-700">${{ number_format($c->price_usd, 2) }}</strong>
                @else
                    <strong class="text-emerald-700">Free</strong>
                @endif
            </div>
            <div class="flex gap-2 mt-2">
                <a href="{{ route('courses.show', $c) }}" class="flex-1 text-center text-xs px-3 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800">View</a>
                <form method="POST" action="{{ route('student.wishlist.toggle', $c) }}">
                    @csrf
                    <button class="text-xs px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-50">Remove</button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    {{ $items->links() }}
    @endif
</div>
@endsection
