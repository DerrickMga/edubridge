<x-app-layout>
    <x-slot name="title">Leaderboard</x-slot>

    <div class="max-w-2xl">
        <div class="page-header mb-6">
            <h1 class="page-title">Leaderboard</h1>
            <p class="page-subtitle">Top students on EduBridge by XP earned</p>
        </div>

        {{-- My position --}}
        <div class="card p-5 mb-6 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 font-bold text-lg flex items-center justify-center flex-shrink-0 uppercase">
                {{ substr(auth()->user()->name, 0, 1) }}
            </div>
            <div class="flex-1">
                <p class="font-semibold text-slate-900">{{ auth()->user()->name }} <span class="text-slate-400 font-normal text-sm">(you)</span></p>
                <div class="flex items-center gap-3 mt-0.5">
                    <span class="text-sm text-slate-500">Level {{ $myStat->level }}</span>
                    <span class="text-sm font-semibold text-emerald-600">{{ number_format($myStat->xp) }} XP</span>
                </div>
            </div>
            <div class="text-center flex-shrink-0">
                <p class="text-3xl font-extrabold text-slate-800">#{{ $myRank }}</p>
                <p class="text-xs text-slate-400">your rank</p>
            </div>
        </div>

        {{-- Leaderboard --}}
        <div class="card overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100">
                <h2 class="font-semibold text-slate-900 text-sm">Top {{ $leaders->count() }} Students</h2>
            </div>

            @if($leaders->isEmpty())
            <div class="p-8 text-center text-slate-400 text-sm">No students on the board yet.</div>
            @else
            <ul class="divide-y divide-slate-100">
                @foreach($leaders as $i => $stat)
                @php $pos = $i + 1; @endphp
                <li class="px-5 py-3.5 flex items-center gap-4 {{ $stat->user_id === auth()->id() ? 'bg-emerald-50' : '' }}">
                    {{-- Position badge --}}
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-sm font-bold
                        @if($pos === 1) bg-amber-400 text-white
                        @elseif($pos === 2) bg-slate-400 text-white
                        @elseif($pos === 3) bg-orange-400 text-white
                        @else bg-slate-100 text-slate-500
                        @endif">
                        @if($pos <= 3)
                            {{ ['🥇','🥈','🥉'][$pos-1] }}
                        @else
                            {{ $pos }}
                        @endif
                    </div>
                    {{-- Avatar --}}
                    <div class="w-9 h-9 rounded-full bg-slate-200 text-slate-600 font-bold text-sm flex items-center justify-center flex-shrink-0 uppercase">
                        {{ substr($stat->user->name ?? '?', 0, 1) }}
                    </div>
                    {{-- Name --}}
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-slate-900 truncate">
                            {{ $stat->user->name ?? 'Unknown' }}
                            @if($stat->user_id === auth()->id())
                            <span class="text-xs text-emerald-600 font-semibold ml-1">(you)</span>
                            @endif
                        </p>
                        <p class="text-xs text-slate-400">Level {{ $stat->level }}
                            @if($stat->streak_days >= 3) · 🔥 {{ $stat->streak_days }}-day streak @endif
                        </p>
                    </div>
                    {{-- XP --}}
                    <div class="text-right flex-shrink-0">
                        <p class="font-bold text-slate-800">{{ number_format($stat->xp) }}</p>
                        <p class="text-xs text-slate-400">XP</p>
                    </div>
                </li>
                @endforeach
            </ul>
            @endif
        </div>

        <p class="text-center text-xs text-slate-400 mt-4">Ranking updates in real time as students earn XP.</p>
    </div>
</x-app-layout>
