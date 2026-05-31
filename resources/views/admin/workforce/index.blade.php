<x-app-layout>
<x-slot name="title">Workforce Management</x-slot>

{{-- ============================================================
     Workforce Management — Alpine.js slide-over panel
     ============================================================ --}}
<div
    x-data="workforcePanel({{ request()->query('teacher', 0) }})"
    x-init="init()"
    @keydown.escape.window="closePanel()"
    class="space-y-6"
>

    {{-- Header --}}
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Workforce</h1>
            <p class="text-sm text-slate-500">Week of {{ $weekStart->format('D, d M') }} – {{ $weekEnd->format('d M Y') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.workforce.rota') }}"
               class="px-3 py-2 text-sm rounded-lg border border-slate-200 hover:bg-slate-50">
                Open rota grid
            </a>
            <form method="POST" action="{{ route('admin.workforce.auto-assign') }}">
                @csrf
                <button class="px-3 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800">⚡ Auto-assign upcoming</button>
            </form>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="px-4 py-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="px-4 py-2.5 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">{{ session('error') }}</div>
    @endif

    {{-- KPI bar --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        @php
            $kpis = [
                ['label' => 'Teachers',           'value' => $totals['teachers'],                           'icon' => '👥'],
                ['label' => 'Online now',          'value' => $totals['online_now'],                         'icon' => '🟢'],
                ['label' => 'Hours this week',     'value' => number_format($totals['weekly_hours'], 1),     'icon' => '⏱'],
                ['label' => 'Payout this week',    'value' => '$'.number_format($totals['weekly_payout'], 2),'icon' => '💵'],
                ['label' => 'Unassigned sessions', 'value' => $totals['unassigned'],                         'icon' => '📋'],
            ];
        @endphp
        @foreach($kpis as $k)
        <div class="card p-4 flex items-start gap-3">
            <span class="text-xl">{{ $k['icon'] }}</span>
            <div>
                <p class="text-xs uppercase tracking-wide text-slate-400">{{ $k['label'] }}</p>
                <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ $k['value'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Teacher roster --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800 text-sm">Teacher roster — this week</h2>
            <span class="text-xs text-slate-400">Click <strong>Manage</strong> to edit rate, availability &amp; shifts</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50/60 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-2 text-left">Teacher</th>
                        <th class="px-4 py-2 text-left">Status</th>
                        <th class="px-4 py-2 text-right">Hourly rate</th>
                        <th class="px-4 py-2 text-left">Timezone</th>
                        <th class="px-4 py-2 text-left">Last seen</th>
                        <th class="px-4 py-2 text-right">Hrs (wk)</th>
                        <th class="px-4 py-2 text-right">Payout (wk)</th>
                        <th class="px-4 py-2 text-right">Upcoming</th>
                        <th class="px-4 py-2 text-right">Done</th>
                        <th class="px-4 py-2 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($rows->sortByDesc('weekly_hours') as $r)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ $r->is_online ? 'bg-emerald-500 ring-2 ring-emerald-200' : 'bg-slate-300' }}"></span>
                                <div>
                                    <p class="font-medium text-slate-900">{{ $r->teacher->name }}</p>
                                    <p class="text-xs text-slate-400">{{ $r->teacher->email }}</p>
                                </div>
                                @if(! $r->accepts)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-50 text-amber-700 border border-amber-100">not accepting</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs capitalize px-2 py-0.5 rounded-full
                                {{ $r->status === 'online' ? 'bg-emerald-50 text-emerald-700' :
                                   ($r->status === 'busy'   ? 'bg-amber-50 text-amber-700' :
                                   ($r->status === 'away'   ? 'bg-blue-50 text-blue-600' : 'bg-slate-50 text-slate-400')) }}">
                                {{ $r->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-800">
                            @if($r->teacher->hourly_rate_usd)
                                ${{ number_format($r->teacher->hourly_rate_usd, 2) }}
                            @else
                                <span class="text-amber-500 text-xs font-normal">Not set</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500">{{ $r->teacher->timezone ?? 'UTC' }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500">
                            {{ $r->last_seen ? $r->last_seen->diffForHumans() : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right font-medium {{ $r->weekly_hours > 0 ? 'text-slate-900' : 'text-slate-300' }}">
                            {{ number_format($r->weekly_hours, 1) }}
                        </td>
                        <td class="px-4 py-3 text-right {{ $r->weekly_payout > 0 ? 'text-emerald-700 font-medium' : 'text-slate-300' }}">
                            ${{ number_format($r->weekly_payout, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="{{ $r->upcoming_shifts > 0 ? 'bg-blue-50 text-blue-700' : 'text-slate-300' }} text-xs px-1.5 py-0.5 rounded">
                                {{ $r->upcoming_shifts }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="{{ $r->completed_shifts > 0 ? 'bg-emerald-50 text-emerald-700' : 'text-slate-300' }} text-xs px-1.5 py-0.5 rounded">
                                {{ $r->completed_shifts }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <button
                                @click="openPanel({{ $r->teacher->id }})"
                                class="px-3 py-1 text-xs rounded-md bg-slate-900 text-white hover:bg-slate-700 transition-colors"
                            >
                                Manage
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                {{-- Totals row --}}
                <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                    <tr class="text-sm font-semibold text-slate-700">
                        <td class="px-4 py-2.5" colspan="5">Totals</td>
                        <td class="px-4 py-2.5 text-right">{{ number_format($totals['weekly_hours'], 1) }} hrs</td>
                        <td class="px-4 py-2.5 text-right text-emerald-700">${{ number_format($totals['weekly_payout'], 2) }}</td>
                        <td class="px-4 py-2.5 text-right">{{ $rows->sum('upcoming_shifts') }}</td>
                        <td class="px-4 py-2.5 text-right">{{ $rows->sum('completed_shifts') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- ==================================================================
         SLIDE-OVER PANEL
         ================================================================== --}}
    {{-- Overlay --}}
    <div
        x-show="panelOpen"
        x-transition:enter="ease-in-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in-out duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="closePanel()"
        class="fixed inset-0 bg-slate-900/40 z-40"
        style="display:none"
    ></div>

    {{-- Panel --}}
    <div
        x-show="panelOpen"
        x-transition:enter="transform transition ease-in-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in-out duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 w-full max-w-xl bg-white shadow-2xl z-50 flex flex-col overflow-hidden"
        style="display:none"
    >
        {{-- Panel header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50 shrink-0">
            <div x-show="!loading" class="min-w-0">
                <h2 class="font-bold text-slate-900 text-base truncate" x-text="teacher.name || 'Teacher'"></h2>
                <p class="text-xs text-slate-400 truncate" x-text="teacher.email || ''"></p>
            </div>
            <div x-show="loading" class="text-sm text-slate-400 animate-pulse">Loading…</div>
            <button @click="closePanel()" class="ml-4 p-1.5 rounded-lg hover:bg-slate-200 text-slate-500 shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Tabs --}}
        <div class="flex border-b border-slate-100 shrink-0 bg-white overflow-x-auto">
            <template x-for="tab in tabs" :key="tab.id">
                <button
                    @click="activeTab = tab.id"
                    :class="activeTab === tab.id
                        ? 'border-b-2 border-slate-900 text-slate-900 font-semibold'
                        : 'text-slate-400 hover:text-slate-600'"
                    class="px-4 py-2.5 text-xs uppercase tracking-wide whitespace-nowrap transition-colors"
                    x-text="tab.label"
                ></button>
            </template>
        </div>

        {{-- Panel body --}}
        <div class="flex-1 overflow-y-auto p-5 space-y-5" x-show="!loading">

            {{-- ── Pay & Settings ── --}}
            <div x-show="activeTab === 'pay'" class="space-y-4">
                <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="text-2xl font-bold text-emerald-600" x-text="teacher.hourly_rate_usd ? '$' + parseFloat(teacher.hourly_rate_usd).toFixed(2) + '/hr' : 'Rate not set'"></div>
                    <div class="text-xs text-slate-500 space-y-0.5">
                        <p x-text="teacher.accepts_assignments ? '✅ Accepting assignments' : '🚫 Not accepting'"></p>
                        <p x-text="'TZ: ' + (teacher.timezone || 'UTC')"></p>
                        <p x-show="teacher.qualification" x-text="teacher.qualification" class="text-slate-400"></p>
                    </div>
                </div>
                <div class="text-xs text-amber-700 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2">
                    ℹ️ Changing the rate only affects <strong>new shifts</strong>. Existing shifts lock in the rate at creation.
                </div>
                <form method="POST" :action="`/admin/workforce/teachers/${teacherId}`" class="space-y-3">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Hourly rate (USD)</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-medium text-sm">$</span>
                            <input type="number" name="hourly_rate_usd" step="0.01" min="0" max="9999"
                                   :value="teacher.hourly_rate_usd || ''"
                                   class="pl-7 w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-400 focus:outline-none"
                                   placeholder="0.00" required />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Timezone</label>
                        <input type="text" name="timezone" :value="teacher.timezone || 'Africa/Harare'"
                               list="tz-list"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-400 focus:outline-none"
                               placeholder="Africa/Harare" required />
                        <datalist id="tz-list">
                            <option value="Africa/Harare"/>
                            <option value="Africa/Nairobi"/>
                            <option value="Africa/Johannesburg"/>
                            <option value="Africa/Lagos"/>
                            <option value="Europe/London"/>
                            <option value="Europe/Paris"/>
                            <option value="America/New_York"/>
                            <option value="America/Chicago"/>
                            <option value="America/Los_Angeles"/>
                            <option value="Asia/Dubai"/>
                            <option value="Asia/Karachi"/>
                            <option value="Asia/Kolkata"/>
                            <option value="UTC"/>
                        </datalist>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-slate-50 rounded-lg">
                        <input type="hidden" name="accepts_assignments" value="0" />
                        <input type="checkbox" name="accepts_assignments" id="accepts_chk" value="1"
                               :checked="teacher.accepts_assignments"
                               class="w-4 h-4 accent-slate-900" />
                        <label for="accepts_chk" class="text-sm text-slate-700 cursor-pointer">Accepting shift assignments</label>
                    </div>
                    <button type="submit"
                            class="w-full py-2 px-4 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800 transition-colors">
                        Save pay settings
                    </button>
                </form>
            </div>

            {{-- ── Availability ── --}}
            <div x-show="activeTab === 'availability'" class="space-y-4">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Weekly recurring windows</h3>
                <div x-show="availability.length === 0" class="text-sm text-slate-400 text-center py-6 bg-slate-50 rounded-lg border border-dashed border-slate-200">
                    No availability windows set yet.
                </div>
                <div class="space-y-2">
                    <template x-for="w in availability" :key="w.id">
                        <div class="flex items-center justify-between bg-slate-50 rounded-lg px-3 py-2 border border-slate-100">
                            <div>
                                <span class="font-semibold text-slate-800 text-sm w-10 inline-block" x-text="w.day_name"></span>
                                <span class="text-slate-500 text-sm" x-text="w.start_time + ' – ' + w.end_time"></span>
                            </div>
                            <form method="POST" :action="`/admin/workforce/availability/${w.id}`">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 px-2 py-1 rounded hover:bg-red-50 transition-colors">Remove</button>
                            </form>
                        </div>
                    </template>
                </div>
                <div class="border-t border-slate-100 pt-4">
                    <h4 class="text-xs font-semibold text-slate-600 mb-3">Add availability window</h4>
                    <form method="POST" action="{{ route('admin.workforce.availability.store') }}" class="space-y-2">
                        @csrf
                        <input type="hidden" name="teacher_id" :value="teacherId" />
                        <select name="day_of_week" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-400 focus:outline-none">
                            <option value="">Day of week…</option>
                            <option value="1">Monday</option>
                            <option value="2">Tuesday</option>
                            <option value="3">Wednesday</option>
                            <option value="4">Thursday</option>
                            <option value="5">Friday</option>
                            <option value="6">Saturday</option>
                            <option value="0">Sunday</option>
                        </select>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-[10px] text-slate-400">Start time</label>
                                <input type="time" name="start_time" required class="w-full border border-slate-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-slate-400 focus:outline-none" />
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-400">End time</label>
                                <input type="time" name="end_time" required class="w-full border border-slate-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-slate-400 focus:outline-none" />
                            </div>
                        </div>
                        <button type="submit" class="w-full py-2 px-4 bg-slate-900 text-white rounded-lg text-sm font-medium hover:bg-slate-800 transition-colors">
                            Add window
                        </button>
                    </form>
                </div>
            </div>

            {{-- ── Time Off ── --}}
            <div x-show="activeTab === 'timeoff'" class="space-y-4">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Upcoming approved time off</h3>
                <div x-show="timeOff.length === 0" class="text-sm text-slate-400 text-center py-6 bg-slate-50 rounded-lg border border-dashed border-slate-200">
                    No time off on record.
                </div>
                <div class="space-y-2">
                    <template x-for="t in timeOff" :key="t.id">
                        <div class="flex items-start justify-between bg-slate-50 rounded-lg px-3 py-2.5 border border-slate-100 gap-3">
                            <div class="min-w-0">
                                <p class="text-sm text-slate-800 font-medium truncate" x-text="t.starts_at + ' → ' + t.ends_at"></p>
                                <p class="text-xs text-slate-500 mt-0.5" x-text="t.reason || 'No reason given'"></p>
                                <span class="inline-block text-[10px] px-1.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 capitalize mt-1" x-text="t.status"></span>
                            </div>
                            <form method="POST" :action="`/admin/workforce/time-off/${t.id}`" class="shrink-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700 px-2 py-1 rounded hover:bg-red-50 transition-colors">Remove</button>
                            </form>
                        </div>
                    </template>
                </div>
                <div class="border-t border-slate-100 pt-4">
                    <h4 class="text-xs font-semibold text-slate-600 mb-3">Add time off block</h4>
                    <form method="POST" action="{{ route('admin.workforce.time-off.store') }}" class="space-y-2">
                        @csrf
                        <input type="hidden" name="teacher_id" :value="teacherId" />
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-[10px] text-slate-400">From</label>
                                <input type="datetime-local" name="starts_at" required class="w-full border border-slate-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-slate-400 focus:outline-none" />
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-400">Until</label>
                                <input type="datetime-local" name="ends_at" required class="w-full border border-slate-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-slate-400 focus:outline-none" />
                            </div>
                        </div>
                        <input type="text" name="reason" placeholder="Reason (optional)" maxlength="255"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-400 focus:outline-none" />
                        <button type="submit" class="w-full py-2 px-4 bg-slate-900 text-white rounded-lg text-sm font-medium hover:bg-slate-800 transition-colors">
                            Add time off
                        </button>
                    </form>
                </div>
            </div>

            {{-- ── Shifts ── --}}
            <div x-show="activeTab === 'shifts'" class="space-y-5">

                {{-- This-week mini dashboard --}}
                <div x-show="thisWeek" class="grid grid-cols-4 gap-2">
                    <div class="bg-slate-50 rounded-lg p-3 text-center border border-slate-100">
                        <p class="text-[10px] text-slate-400 uppercase">Hours</p>
                        <p class="font-bold text-slate-800 text-lg" x-text="thisWeek?.hours ?? 0"></p>
                    </div>
                    <div class="bg-emerald-50 rounded-lg p-3 text-center border border-emerald-100">
                        <p class="text-[10px] text-emerald-500 uppercase">Payout</p>
                        <p class="font-bold text-emerald-700 text-lg" x-text="'$' + parseFloat(thisWeek?.payout ?? 0).toFixed(2)"></p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-3 text-center border border-blue-100">
                        <p class="text-[10px] text-blue-400 uppercase">Upcoming</p>
                        <p class="font-bold text-blue-700 text-lg" x-text="thisWeek?.upcoming ?? 0"></p>
                    </div>
                    <div class="bg-slate-50 rounded-lg p-3 text-center border border-slate-100">
                        <p class="text-[10px] text-slate-400 uppercase">Done</p>
                        <p class="font-bold text-slate-800 text-lg" x-text="thisWeek?.completed ?? 0"></p>
                    </div>
                </div>

                {{-- Add shift --}}
                <div class="border border-dashed border-slate-200 rounded-xl p-4 space-y-3">
                    <h4 class="text-xs font-semibold text-slate-600 uppercase tracking-wide">Add shift</h4>
                    <form method="POST" action="{{ route('admin.workforce.shifts.store') }}" class="space-y-3">
                        @csrf
                        <input type="hidden" name="teacher_id" :value="teacherId" />
                        <input type="text" name="title" placeholder="Shift title (e.g. Maths – Year 10)" required maxlength="200"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-400 focus:outline-none" />
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-[10px] text-slate-400">Start</label>
                                <input type="datetime-local" name="starts_at" required class="w-full border border-slate-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-slate-400 focus:outline-none" />
                            </div>
                            <div>
                                <label class="text-[10px] text-slate-400">End</label>
                                <input type="datetime-local" name="ends_at" required class="w-full border border-slate-200 rounded-lg px-2 py-1.5 text-sm focus:ring-2 focus:ring-slate-400 focus:outline-none" />
                            </div>
                        </div>
                        <textarea name="notes" rows="2" placeholder="Notes (optional)" maxlength="1000"
                                  class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-slate-400 focus:outline-none resize-none"></textarea>
                        <p class="text-[10px] text-slate-400">
                            Rate snapshot: <span class="font-semibold" x-text="teacher.hourly_rate_usd ? '$' + parseFloat(teacher.hourly_rate_usd).toFixed(2) + '/hr' : 'not set — set rate first'"></span> locked at creation.
                        </p>
                        <button type="submit" class="w-full py-2 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800 transition-colors">
                            Create shift
                        </button>
                    </form>
                </div>

                {{-- Upcoming shifts --}}
                <div x-show="upcomingShifts.length > 0" class="space-y-2">
                    <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Upcoming shifts</h4>
                    <template x-for="s in upcomingShifts" :key="s.id">
                        <div class="bg-blue-50 border border-blue-100 rounded-lg px-3 py-2.5">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-slate-800 truncate" x-text="s.title"></p>
                                    <p class="text-xs text-slate-500" x-text="s.starts_at + ' – ' + s.ends_at + ' (' + s.hours + ' hrs)'"></p>
                                </div>
                                <div class="flex gap-1 shrink-0">
                                    <form method="POST" :action="`/admin/workforce/shifts/${s.id}`">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="in_progress" />
                                        <button type="submit" class="text-xs px-2 py-1 rounded bg-amber-100 text-amber-700 hover:bg-amber-200 transition-colors">▶ Start</button>
                                    </form>
                                    <form method="POST" :action="`/admin/workforce/shifts/${s.id}`">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs px-2 py-1 rounded bg-red-50 text-red-500 hover:bg-red-100 transition-colors">✕</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Recent shifts --}}
                <div x-show="recentShifts.length > 0" class="space-y-2">
                    <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Recent shifts (last 10)</h4>
                    <template x-for="s in recentShifts" :key="s.id">
                        <div class="border border-slate-100 rounded-lg px-3 py-2.5 space-y-2 bg-white">
                            <div class="flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-slate-800 truncate" x-text="s.title"></p>
                                    <p class="text-xs text-slate-500" x-text="s.starts_at"></p>
                                </div>
                                <span class="text-xs px-2 py-0.5 rounded-full capitalize shrink-0"
                                      :class="{
                                          'bg-emerald-50 text-emerald-700': s.status === 'completed',
                                          'bg-red-50 text-red-500': s.status === 'missed' || s.status === 'cancelled',
                                          'bg-amber-50 text-amber-600': s.status === 'in_progress',
                                          'bg-slate-50 text-slate-500': s.status === 'scheduled',
                                      }"
                                      x-text="s.status.replace('_', ' ')"></span>
                            </div>
                            <div x-show="s.status === 'completed'" class="text-xs text-emerald-700 bg-emerald-50 rounded px-2.5 py-1.5">
                                ✓ <span x-text="(s.hours_worked || s.duration_hours) + ' hrs worked'"></span>
                                <span x-show="s.payout && s.payout > 0"> · <span class="font-semibold" x-text="'$' + parseFloat(s.payout).toFixed(2) + ' payout'"></span></span>
                            </div>
                            <div x-show="s.status !== 'completed'" class="border-t border-slate-100 pt-2">
                                <form method="POST" :action="`/admin/workforce/shifts/${s.id}`" class="flex items-end gap-2">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="completed" />
                                    <div class="flex-1">
                                        <label class="text-[10px] text-slate-400 block mb-0.5">Actual hours worked</label>
                                        <input type="number" name="hours_worked" step="0.25" min="0" max="24"
                                               :value="s.duration_hours"
                                               class="w-full border border-slate-200 rounded-lg px-2 py-1.5 text-sm focus:ring-1 focus:ring-slate-400 focus:outline-none" />
                                    </div>
                                    <button type="submit" class="text-xs px-3 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors whitespace-nowrap">
                                        Mark complete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="upcomingShifts.length === 0 && recentShifts.length === 0"
                     class="text-sm text-slate-400 text-center py-6 bg-slate-50 rounded-lg border border-dashed border-slate-200">
                    No shifts on record. Add one above.
                </div>
            </div>

        </div>

        {{-- Loading state --}}
        <div x-show="loading" class="flex-1 flex items-center justify-center">
            <div class="text-center space-y-2">
                <div class="w-8 h-8 border-2 border-slate-200 border-t-slate-600 rounded-full animate-spin mx-auto"></div>
                <p class="text-slate-400 text-sm">Loading teacher data…</p>
            </div>
        </div>

        {{-- ── Courses ── --}}
        <div x-show="activeTab === 'courses' && !loading" class="flex-1 overflow-y-auto p-5 space-y-4">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-500">Assigned courses &amp; delivery stats</h3>

            <div x-show="courses.length === 0" class="text-sm text-slate-400 text-center py-10 bg-slate-50 rounded-lg border border-dashed border-slate-200">
                No courses assigned to this teacher.
            </div>

            {{-- All-courses summary --}}
            <div x-show="courses.length > 0" class="grid grid-cols-4 gap-2">
                <div class="bg-slate-50 rounded-lg p-3 text-center border border-slate-100">
                    <p class="text-[10px] text-slate-400 uppercase">Courses</p>
                    <p class="font-bold text-slate-800 text-lg" x-text="courses.length"></p>
                </div>
                <div class="bg-blue-50 rounded-lg p-3 text-center border border-blue-100">
                    <p class="text-[10px] text-blue-400 uppercase">Sessions</p>
                    <p class="font-bold text-blue-700 text-lg" x-text="courses.reduce((s,c) => s + c.sessions_total, 0)"></p>
                </div>
                <div class="bg-emerald-50 rounded-lg p-3 text-center border border-emerald-100">
                    <p class="text-[10px] text-emerald-400 uppercase">Completed</p>
                    <p class="font-bold text-emerald-700 text-lg" x-text="courses.reduce((s,c) => s + c.sessions_done, 0)"></p>
                </div>
                <div class="bg-violet-50 rounded-lg p-3 text-center border border-violet-100">
                    <p class="text-[10px] text-violet-400 uppercase">Attendance</p>
                    <p class="font-bold text-violet-700 text-lg" x-text="courses.reduce((s,c) => s + c.total_attendance, 0)"></p>
                </div>
            </div>

            <template x-for="c in courses" :key="c.id">
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    {{-- Course header --}}
                    <div class="flex items-start justify-between gap-3 px-4 py-3 bg-slate-50 border-b border-slate-100">
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-900 text-sm leading-snug truncate" x-text="c.title"></p>
                            <p class="text-xs text-slate-400 mt-0.5" x-text="(c.subject || 'General') + (c.grade_level ? ' · ' + c.grade_level : '')"></p>
                        </div>
                        <span :class="c.status === 'published' ? 'bg-emerald-100 text-emerald-700 border-emerald-200' : 'bg-amber-100 text-amber-700 border-amber-200'"
                              class="text-[10px] px-2 py-0.5 rounded-full capitalize shrink-0 font-semibold border" x-text="c.status"></span>
                    </div>

                    {{-- Course stats grid --}}
                    <div class="grid grid-cols-5 divide-x divide-slate-100 text-center">
                        <div class="px-2 py-3">
                            <p class="text-[10px] text-slate-400">Students</p>
                            <p class="font-bold text-slate-800" x-text="c.enrollments_count"></p>
                        </div>
                        <div class="px-2 py-3">
                            <p class="text-[10px] text-slate-400">Lessons</p>
                            <p class="font-bold text-slate-700" x-text="c.lessons_count"></p>
                        </div>
                        <div class="px-2 py-3">
                            <p class="text-[10px] text-blue-400">Upcoming</p>
                            <p class="font-bold text-blue-700" x-text="c.sessions_upcoming"></p>
                        </div>
                        <div class="px-2 py-3">
                            <p class="text-[10px] text-emerald-400">Done</p>
                            <p class="font-bold text-emerald-700" x-text="c.sessions_done"></p>
                        </div>
                        <div class="px-2 py-3">
                            <p class="text-[10px] text-violet-400">Attended</p>
                            <p class="font-bold text-violet-700" x-text="c.total_attendance"></p>
                        </div>
                    </div>

                    {{-- Completion bar --}}
                    <div class="px-4 py-2 border-t border-slate-100 bg-white">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-slate-100 rounded-full h-1.5">
                                <div class="bg-emerald-500 h-1.5 rounded-full transition-all"
                                     :style="'width:' + (c.sessions_total > 0 ? Math.round((c.sessions_done / c.sessions_total) * 100) : 0) + '%'"></div>
                            </div>
                            <span class="text-[10px] text-slate-400 shrink-0"
                                  x-text="c.sessions_total > 0 ? Math.round((c.sessions_done / c.sessions_total) * 100) + '% done' : 'No sessions'"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

</div>

<script>
function workforcePanel(autoOpenId) {
    return {
        panelOpen: false,
        loading: false,
        teacherId: null,
        activeTab: 'pay',
        tabs: [
            { id: 'pay',          label: 'Pay & Settings' },
            { id: 'availability', label: 'Availability' },
            { id: 'timeoff',      label: 'Time Off' },
            { id: 'shifts',       label: 'Shifts' },
            { id: 'courses',      label: 'Courses' },
        ],
        teacher: {},
        availability: [],
        timeOff: [],
        upcomingShifts: [],
        recentShifts: [],
        thisWeek: null,
        courses: [],

        init() {
            if (autoOpenId) {
                this.openPanel(autoOpenId);
            }
        },

        openPanel(id) {
            this.teacherId = id;
            this.panelOpen = true;
            this.loading = true;
            this.teacher = {};
            this.availability = [];
            this.timeOff = [];
            this.upcomingShifts = [];
            this.recentShifts = [];
            this.thisWeek = null;
            this.courses = [];

            fetch(`/admin/workforce/teachers/${id}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                }
            })
            .then(r => {
                if (!r.ok) throw new Error('Failed to load');
                return r.json();
            })
            .then(data => {
                this.teacher        = data.teacher;
                this.availability   = data.availability;
                this.timeOff        = data.time_off;
                this.upcomingShifts = data.upcoming_shifts;
                this.recentShifts   = data.recent_shifts;
                this.thisWeek       = data.this_week;
                this.courses        = data.courses ?? [];
                this.loading        = false;
            })
            .catch(() => { this.loading = false; });
        },

        closePanel() {
            this.panelOpen = false;
            this.teacherId = null;
        },
    };
}
</script>
</x-app-layout>

