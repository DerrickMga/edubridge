@extends('layouts.app')

@section('page-title', 'Contingency / Risk Matrix')

@section('content')
<div class="space-y-6">
    <div class="flex items-end justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Contingency Matrix</h1>
            <p class="text-sm text-slate-500">Foreseeable failure modes, severity tiers and response playbooks.</p>
        </div>
        <a href="{{ route('admin.policies.index') }}" class="text-sm text-slate-500 hover:underline">← Policies</a>
    </div>

    @if(session('success'))
        <div class="px-4 py-2 rounded-lg bg-emerald-50 text-emerald-800 text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="px-4 py-2 rounded-lg bg-rose-50 text-rose-800 text-sm">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
    @endif

    {{-- Matrix grouped by severity --}}
    @php $tierBorder = ['critical'=>'border-rose-400','high'=>'border-amber-400','medium'=>'border-yellow-400','low'=>'border-slate-400']; @endphp
    <div class="grid md:grid-cols-2 gap-4">
        @foreach(\App\Models\PolicyRiskEvent::severities() as $sev)
        <div class="card overflow-hidden border-t-4 {{ $tierBorder[$sev] ?? 'border-slate-400' }}">
            <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-semibold text-slate-800 text-sm capitalize">{{ $sev }} severity</h2>
                <span class="text-xs text-slate-400">{{ ($bySeverity[$sev] ?? collect())->count() }}</span>
            </div>
            <ul class="divide-y divide-slate-100 text-sm">
                @forelse($bySeverity[$sev] ?? [] as $e)
                <li class="px-5 py-3">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-slate-800">{{ $e->title }}</span>
                        <code class="text-[10px] text-slate-400">{{ $e->code }}</code>
                    </div>
                    @if($e->trigger)<p class="text-xs text-slate-500 mt-1"><strong>Trigger:</strong> {{ $e->trigger }}</p>@endif
                    <details class="mt-2">
                        <summary class="text-xs text-slate-600 cursor-pointer hover:underline">Response playbook</summary>
                        <pre class="text-xs whitespace-pre-wrap mt-1 text-slate-700">{{ $e->response_playbook }}</pre>
                    </details>
                </li>
                @empty
                <li class="px-5 py-3 text-xs text-slate-400">No entries.</li>
                @endforelse
            </ul>
        </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-2 gap-4">
        {{-- Add a new event --}}
        <form method="POST" action="{{ route('admin.policies.matrix.events.store') }}" class="card p-5 space-y-3">
            @csrf
            <h2 class="font-semibold text-slate-800 text-sm">Add risk event</h2>
            <div class="grid grid-cols-2 gap-2 text-sm">
                <label><span class="block text-xs text-slate-500">Code (UPPER_SNAKE)</span>
                    <input name="code" required pattern="[A-Z0-9_]+" class="w-full px-2 py-1.5 rounded border border-slate-200">
                </label>
                <label><span class="block text-xs text-slate-500">Title</span>
                    <input name="title" required class="w-full px-2 py-1.5 rounded border border-slate-200">
                </label>
                <label><span class="block text-xs text-slate-500">Category</span>
                    <select name="category" class="w-full px-2 py-1.5 rounded border border-slate-200">
                        @foreach(\App\Models\PolicyRiskEvent::categories() as $cat)<option>{{ $cat }}</option>@endforeach
                    </select>
                </label>
                <label><span class="block text-xs text-slate-500">Severity</span>
                    <select name="severity" class="w-full px-2 py-1.5 rounded border border-slate-200">
                        @foreach(\App\Models\PolicyRiskEvent::severities() as $s)<option>{{ $s }}</option>@endforeach
                    </select>
                </label>
            </div>
            <label class="text-sm block"><span class="block text-xs text-slate-500">Trigger</span>
                <textarea name="trigger" rows="2" class="w-full px-2 py-1.5 rounded border border-slate-200"></textarea>
            </label>
            <label class="text-sm block"><span class="block text-xs text-slate-500">Response playbook</span>
                <textarea name="response_playbook" rows="5" required class="w-full px-2 py-1.5 rounded border border-slate-200"></textarea>
            </label>
            <div class="text-right">
                <button class="px-3 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800">Add to matrix</button>
            </div>
        </form>

        {{-- Log incident --}}
        <form method="POST" action="{{ route('admin.policies.matrix.incidents.store') }}" class="card p-5 space-y-3">
            @csrf
            <h2 class="font-semibold text-slate-800 text-sm">Log new incident</h2>
            <label class="text-sm block"><span class="block text-xs text-slate-500">Risk event</span>
                <select name="risk_event_id" required class="w-full px-2 py-1.5 rounded border border-slate-200">
                    @foreach($events as $e)<option value="{{ $e->id }}">[{{ $e->severity }}] {{ $e->title }}</option>@endforeach
                </select>
            </label>
            <label class="text-sm block"><span class="block text-xs text-slate-500">Teacher</span>
                <select name="teacher_id" required class="w-full px-2 py-1.5 rounded border border-slate-200">
                    @foreach(\App\Models\User::where('role','teacher')->orderBy('name')->get() as $t)
                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-sm block"><span class="block text-xs text-slate-500">Severity override (optional)</span>
                <select name="severity_override" class="w-full px-2 py-1.5 rounded border border-slate-200">
                    <option value="">(use matrix default)</option>
                    @foreach(\App\Models\PolicyRiskEvent::severities() as $s)<option>{{ $s }}</option>@endforeach
                </select>
            </label>
            <label class="text-sm block"><span class="block text-xs text-slate-500">Summary</span>
                <textarea name="summary" rows="3" required class="w-full px-2 py-1.5 rounded border border-slate-200"></textarea>
            </label>
            <div class="text-right">
                <button class="px-3 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800">Log incident</button>
            </div>
        </form>
    </div>

    {{-- Recent incidents --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100"><h2 class="font-semibold text-slate-800 text-sm">Recent incidents</h2></div>
        @if($incidents->isEmpty())
            <p class="px-5 py-3 text-sm text-slate-500">No incidents recorded.</p>
        @else
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                <tr><th class="text-left px-4 py-2">When</th><th>Event</th><th>Teacher</th><th>Status</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($incidents as $i)
                <tr>
                    <td class="px-4 py-2 text-xs text-slate-500">{{ optional($i->opened_at)->format('d M Y H:i') }}</td>
                    <td class="px-2">{{ optional($i->event)->title }}</td>
                    <td class="px-2">{{ optional($i->teacher)->name }}</td>
                    <td class="px-2 capitalize">{{ $i->status }}</td>
                    <td class="px-2 text-right">
                        <form method="POST" action="{{ route('admin.policies.matrix.incidents.update', $i) }}" class="inline-flex items-center gap-1">
                            @csrf @method('PATCH')
                            <select name="status" class="text-xs px-1 py-0.5 rounded border border-slate-200">
                                @foreach(\App\Models\PolicyRiskIncident::statuses() as $s)
                                    <option value="{{ $s }}" @selected($i->status === $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                            <button class="text-xs px-2 py-0.5 rounded bg-slate-900 text-white">Update</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection
