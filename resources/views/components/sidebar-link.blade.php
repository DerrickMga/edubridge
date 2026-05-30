@props(['href', 'active' => false])
<a
    href="{{ $href }}"
    @class([
        'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-150 group',
        'bg-emerald-50 text-emerald-700 font-semibold' => $active,
        'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => ! $active,
    ])
>
    @isset($icon)
    <svg
        class="w-[18px] h-[18px] flex-shrink-0 {{ $active ? 'text-emerald-600' : 'text-slate-400 group-hover:text-slate-500' }}"
        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75"
    >{{ $icon }}</svg>
    @endisset
    <span>{{ $slot }}</span>
</a>
