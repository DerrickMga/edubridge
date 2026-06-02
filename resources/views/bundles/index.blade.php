<x-app-layout>
    <x-slot name="title">Bundles</x-slot>

    <div class="max-w-5xl mx-auto space-y-5">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Course bundles</h1>
        <p class="text-sm text-slate-500">Grouped courses at a single price.</p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($bundles as $b)
        <a href="{{ route('bundles.show', $b) }}" class="card overflow-hidden hover:shadow-md transition">
            @if($b->thumbnail)<img src="{{ $b->thumbnail }}" class="w-full h-32 object-cover" alt="">@endif
            <div class="p-4">
                <h2 class="font-semibold text-slate-900">{{ $b->title }}</h2>
                <p class="text-xs text-slate-400 mt-0.5">{{ $b->courses->count() }} course{{ $b->courses->count() === 1 ? '' : 's' }}</p>
                <p class="text-lg font-bold text-slate-900 mt-2">${{ number_format((float) $b->price_usd, 2) }}</p>
            </div>
        </a>
        @empty
        <p class="text-slate-400 text-sm">No bundles available yet.</p>
        @endforelse
    </div>
    {{ $bundles->links() }}
    </div>
</x-app-layout>
