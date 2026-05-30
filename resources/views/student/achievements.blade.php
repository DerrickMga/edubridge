<x-app-layout>
    <x-slot name="title">Achievements</x-slot>

    @php
    $colourMap = [
        'emerald' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'blue'    => 'bg-blue-100 text-blue-800 border-blue-200',
        'violet'  => 'bg-violet-100 text-violet-800 border-violet-200',
        'orange'  => 'bg-orange-100 text-orange-800 border-orange-200',
        'amber'   => 'bg-amber-100 text-amber-800 border-amber-200',
        'teal'    => 'bg-teal-100 text-teal-800 border-teal-200',
        'purple'  => 'bg-purple-100 text-purple-800 border-purple-200',
        'yellow'  => 'bg-yellow-100 text-yellow-800 border-yellow-200',
    ];
    $nextXp    = $stat->next_level_xp;
    $currXp    = $stat->current_level_xp;
    $pct       = $stat->level_progress_percent;
    @endphp

    <div class="max-w-3xl">
        <div class="page-header mb-6">
            <h1 class="page-title">Achievements</h1>
            <p class="page-subtitle">Your XP, level, streaks and badges</p>
        </div>

        {{-- XP + Level card --}}
        <div class="card p-6 mb-6">
            <div class="flex items-center gap-5">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center flex-shrink-0 shadow-lg">
                    <span class="text-white font-extrabold text-2xl">{{ $stat->level }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1">
                        <p class="font-bold text-slate-900 text-lg">Level {{ $stat->level }}</p>
                        <p class="text-sm text-slate-500 font-semibold">{{ number_format($stat->xp) }} XP total</p>
                    </div>
                    <div class="progress-bar mb-1">
                        <div class="progress-fill" style="width: {{ $pct }}%"></div>
                    </div>
                    <p class="text-xs text-slate-400">
                        @if($stat->level < 10)
                            {{ number_format($nextXp - $stat->xp) }} XP to Level {{ $stat->level + 1 }}
                        @else
                            Max level reached 🏆
                        @endif
                    </p>
                </div>
            </div>

            {{-- Streak + stats row --}}
            <div class="grid grid-cols-3 gap-4 mt-5 pt-5 border-t border-slate-100">
                <div class="text-center">
                    <p class="text-2xl font-extrabold text-orange-500">{{ $stat->streak_days }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">Day streak 🔥</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-extrabold text-slate-700">{{ $stat->longest_streak }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">Longest streak</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-extrabold text-emerald-600">{{ $earnedBadges->count() }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">Badges earned</p>
                </div>
            </div>
        </div>

        {{-- Badges grid --}}
        <h2 class="section-title mb-4">Badges ({{ $earnedBadges->count() }}/{{ $allBadges->count() }})</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-8">
            @foreach($allBadges as $badge)
            @php $earned = $earnedBadges->has($badge->slug); @endphp
            <div class="card p-4 flex items-start gap-3 {{ !$earned ? 'opacity-40 grayscale' : '' }} transition-all">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl flex-shrink-0
                    {{ $earned ? ($colourMap[$badge->colour] ?? 'bg-slate-100 text-slate-700') : 'bg-slate-100' }} border">
                    {{ $badge->icon }}
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-slate-900 text-sm">{{ $badge->name }}</p>
                    <p class="text-xs text-slate-500 mt-0.5 leading-snug">{{ $badge->description }}</p>
                    @if($earned)
                    <p class="text-xs text-emerald-600 font-semibold mt-1">
                        Earned {{ $earnedBadges[$badge->slug]->pivot->earned_at ? \Carbon\Carbon::parse($earnedBadges[$badge->slug]->pivot->earned_at)->diffForHumans() : '' }}
                    </p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- XP history --}}
        <h2 class="section-title mb-4">Recent Activity</h2>
        @if($recentXp->isEmpty())
        <div class="card p-8 text-center text-slate-400 text-sm">
            No activity yet — start completing lessons to earn XP!
        </div>
        @else
        <div class="card divide-y divide-slate-100 overflow-hidden">
            @foreach($recentXp as $event)
            <div class="px-5 py-3 flex items-center gap-4">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0 text-sm">
                    @php
                    echo match($event->event_type) {
                        'lesson_complete'  => '📖',
                        'quiz_pass'        => '✅',
                        'course_complete'  => '🎓',
                        'streak'           => '🔥',
                        default            => '⭐',
                    };
                    @endphp
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-slate-700 truncate">{{ $event->description }}</p>
                    <p class="text-xs text-slate-400">{{ $event->created_at->diffForHumans() }}</p>
                </div>
                <span class="text-sm font-bold text-emerald-600 flex-shrink-0">+{{ $event->xp }} XP</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</x-app-layout>
