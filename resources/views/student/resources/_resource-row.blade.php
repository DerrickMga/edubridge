@php
$icons = [
    'pdf'         => ['color'=>'text-red-500',    'bg'=>'bg-red-50',    'path'=>'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'],
    'video'       => ['color'=>'text-violet-500', 'bg'=>'bg-violet-50', 'path'=>'m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z'],
    'audio'       => ['color'=>'text-blue-500',   'bg'=>'bg-blue-50',   'path'=>'M19.114 5.636a9 9 0 0 1 0 12.728M16.463 8.288a5.25 5.25 0 0 1 0 7.424M6.75 8.25l4.72-4.72a.75.75 0 0 1 1.28.53v15.88a.75.75 0 0 1-1.28.53l-4.72-4.72H4.51c-.88 0-1.704-.507-1.938-1.354A9.009 9.009 0 0 1 2.25 12c0-.83.112-1.633.322-2.396C2.806 8.756 3.63 8.25 4.51 8.25H6.75Z'],
    'image'       => ['color'=>'text-pink-500',   'bg'=>'bg-pink-50',   'path'=>'m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z'],
    'link'        => ['color'=>'text-cyan-500',   'bg'=>'bg-cyan-50',   'path'=>'M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244'],
    'document'    => ['color'=>'text-amber-500',  'bg'=>'bg-amber-50',  'path'=>'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'],
    'spreadsheet' => ['color'=>'text-green-500',  'bg'=>'bg-green-50',  'path'=>'M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 0 1-1.125-1.125M3.375 19.5h1.5C5.496 19.5 6 18.996 6 18.375m-3.75.125V5.625m0 12.75V5.625m0 0A1.125 1.125 0 0 1 4.5 4.5h12.75A1.125 1.125 0 0 1 18.375 5.625m-14.625 0h14.625m0 0V19.5m0-13.875v1.875A1.125 1.125 0 0 0 19.5 8.625v-.75A1.125 1.125 0 0 0 18.375 6.75m0 0H4.5m13.875 0L12 6.75M4.5 6.75l6.375 3.375'],
    'other'       => ['color'=>'text-slate-500',  'bg'=>'bg-slate-50',  'path'=>'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z'],
];
$icon = $icons[$resource->type] ?? $icons['other'];
@endphp
<div class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50/80 transition-colors">
    {{-- Icon --}}
    <div class="w-10 h-10 rounded-lg {{ $icon['bg'] }} flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 {{ $icon['color'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon['path'] }}"/>
        </svg>
    </div>

    {{-- Info --}}
    <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-slate-800 truncate">{{ $resource->title }}</p>
        <div class="flex items-center gap-2 mt-0.5">
            <span class="text-[11px] font-medium uppercase tracking-wide text-slate-400">{{ $resource->type }}</span>
            @if($resource->file_size_bytes)
            <span class="text-[11px] text-slate-300">·</span>
            <span class="text-[11px] text-slate-400">{{ $resource->file_size_formatted }}</span>
            @endif
            @if($resource->download_count > 0)
            <span class="text-[11px] text-slate-300">·</span>
            <span class="text-[11px] text-slate-400">{{ $resource->download_count }} download{{ $resource->download_count !== 1 ? 's' : '' }}</span>
            @endif
        </div>
        @if($resource->description)
        <p class="text-xs text-slate-500 mt-1 line-clamp-1">{{ $resource->description }}</p>
        @endif
    </div>

    {{-- Action --}}
    @if($resource->type === 'link' && $resource->external_url)
    <a href="{{ $resource->external_url }}" target="_blank" rel="noopener noreferrer"
        class="btn-secondary btn-sm flex-shrink-0 flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
        </svg>
        Open
    </a>
    @elseif($resource->is_downloadable)
    <a href="{{ route('student.resources.download', [$resource->course_id, $resource]) }}"
        class="btn-primary btn-sm flex-shrink-0 flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
        </svg>
        Download
    </a>
    @else
    <span class="text-xs text-slate-400 flex-shrink-0">View only</span>
    @endif
</div>
