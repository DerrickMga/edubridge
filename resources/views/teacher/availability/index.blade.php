<x-app-layout>
<x-slot name="title">My Availability</x-slot>
<div class="space-y-6 max-w-4xl">

    <div>
        <h1 class="text-2xl font-bold text-slate-900">Availability & Status</h1>
        <p class="text-sm text-slate-500">Tell us when you're free so we can auto-assign you to live sessions fairly.</p>
    </div>

    @if(session('success'))
        <div class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-800 text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="px-4 py-2 rounded-lg bg-rose-50 text-rose-800 text-sm">
            @foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach
        </div>
    @endif

    {{-- Status + accepting assignments --}}
    <div class="card p-5">
        <h2 class="font-semibold text-slate-800 mb-3">Status</h2>
        <form method="POST" action="{{ route('teacher.availability.status') }}" class="flex flex-wrap items-center gap-3">
            @csrf
            <select name="status" class="px-3 py-2 text-sm rounded-lg border border-slate-200">
                @foreach(['online' => 'Online', 'busy' => 'Busy', 'away' => 'Away', 'offline' => 'Offline'] as $k => $v)
                    <option value="{{ $k }}" @selected($teacher->availability_status === $k)>{{ $v }}</option>
                @endforeach
            </select>
            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="accepts_assignments" value="1" @checked($teacher->accepts_assignments) class="rounded border-slate-300">
                Accept new auto-assignments
            </label>
            <button class="ml-auto px-3 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800">Save</button>
        </form>
        <p class="text-xs text-slate-400 mt-2">Last seen: {{ $teacher->last_seen_at ? $teacher->last_seen_at->diffForHumans() : 'never' }}</p>
    </div>

    {{-- Weekly windows --}}
    <div class="card p-5">
        <h2 class="font-semibold text-slate-800 mb-3">Weekly availability windows</h2>
        <form method="POST" action="{{ route('teacher.availability.windows.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
            @csrf
            <select name="day_of_week" required class="px-3 py-2 text-sm rounded-lg border border-slate-200">
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $i => $d)
                    <option value="{{ $i }}">{{ $d }}</option>
                @endforeach
            </select>
            <input type="time" name="start_time" required class="px-3 py-2 text-sm rounded-lg border border-slate-200">
            <input type="time" name="end_time"   required class="px-3 py-2 text-sm rounded-lg border border-slate-200">
            <button class="px-3 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800">Add window</button>
        </form>

        @if($windows->isEmpty())
            <p class="text-sm text-slate-400">No availability windows set — add one above so you can be auto-assigned.</p>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($windows as $w)
                <li class="flex items-center justify-between py-2 text-sm">
                    <span><span class="font-medium text-slate-800">{{ \App\Models\TeacherAvailability::dayName($w->day_of_week) }}</span> · {{ \Carbon\Carbon::parse($w->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($w->end_time)->format('H:i') }}</span>
                    <form method="POST" action="{{ route('teacher.availability.windows.destroy', $w) }}">@csrf @method('DELETE')
                        <button class="text-xs text-rose-600 hover:underline">Remove</button>
                    </form>
                </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- Time off --}}
    <div class="card p-5">
        <h2 class="font-semibold text-slate-800 mb-3">Time off</h2>
        <form method="POST" action="{{ route('teacher.availability.time-off.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
            @csrf
            <input type="datetime-local" name="starts_at" required class="px-3 py-2 text-sm rounded-lg border border-slate-200">
            <input type="datetime-local" name="ends_at"   required class="px-3 py-2 text-sm rounded-lg border border-slate-200">
            <input type="text" name="reason" placeholder="Reason (optional)" maxlength="255" class="px-3 py-2 text-sm rounded-lg border border-slate-200">
            <button class="px-3 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800">Request time off</button>
        </form>

        @if($timeOff->isEmpty())
            <p class="text-sm text-slate-400">No time-off entries.</p>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($timeOff as $t)
                <li class="flex items-center justify-between py-2 text-sm">
                    <span>
                        {{ $t->starts_at->format('d M H:i') }} → {{ $t->ends_at->format('d M H:i') }}
                        @if($t->reason)<span class="text-slate-400">· {{ $t->reason }}</span>@endif
                        <span class="ml-2 text-[10px] px-1.5 py-0.5 rounded
                            {{ $t->status === 'approved' ? 'bg-emerald-50 text-emerald-700' :
                               ($t->status === 'denied' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                            {{ $t->status }}
                        </span>
                    </span>
                    <form method="POST" action="{{ route('teacher.availability.time-off.destroy', $t) }}">@csrf @method('DELETE')
                        <button class="text-xs text-rose-600 hover:underline">Remove</button>
                    </form>
                </li>
                @endforeach
            </ul>
        @endif
    </div>

</div>
</x-app-layout>
