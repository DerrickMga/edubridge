<x-app-layout>
    <x-slot name="title">Announcements</x-slot>

    <div class="space-y-4 max-w-3xl">
    <h1 class="text-2xl font-bold text-slate-900">Announcements</h1>

    @forelse($announcements as $a)
    <article class="card p-5">
        <div class="flex items-center justify-between mb-2">
            <div>
                <h2 class="font-semibold text-slate-900">{{ $a->title }}</h2>
                <p class="text-xs text-slate-400">
                    {{ optional($a->author)->name }} · {{ $a->published_at->diffForHumans() }}
                    @if($a->course) · <a href="{{ route('courses.show', $a->course) }}" class="text-emerald-600 hover:underline">{{ $a->course->title }}</a> @endif
                </p>
            </div>
            <span class="text-[10px] uppercase tracking-wide px-2 py-0.5 rounded bg-slate-100 text-slate-600">{{ $a->audience }}</span>
        </div>
        <div class="prose prose-sm max-w-none text-slate-700">{!! \Illuminate\Support\Str::markdown($a->body) !!}</div>
    </article>
    @empty
    <p class="text-slate-400 text-sm">No announcements yet.</p>
    @endforelse

    {{ $announcements->links() }}
    </div>
</x-app-layout>
