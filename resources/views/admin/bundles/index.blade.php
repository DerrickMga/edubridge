@extends('layouts.app')
@section('page-title', 'Bundles')

@section('content')
<div class="space-y-5">
    <div class="flex items-end justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Bundles</h1>
            <p class="text-sm text-slate-500">Group courses sold together.</p>
        </div>
        <a href="{{ route('admin.bundles.create') }}" class="px-3 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800">+ New bundle</a>
    </div>

    @if(session('success'))<div class="px-4 py-2 rounded bg-emerald-50 text-emerald-800 text-sm">{{ session('success') }}</div>@endif

    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500 uppercase">
                <tr><th class="text-left px-4 py-2">Title</th><th>Courses</th><th>Price</th><th>Active</th><th></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($bundles as $b)
                <tr>
                    <td class="px-4 py-2 font-semibold">{{ $b->title }}</td>
                    <td class="px-2">{{ $b->courses->count() }}</td>
                    <td class="px-2">${{ number_format((float) $b->price_usd, 2) }}</td>
                    <td class="px-2">{{ $b->is_active ? 'Yes' : 'No' }}</td>
                    <td class="px-2 py-2 text-right">
                        <a href="{{ route('admin.bundles.edit', $b) }}" class="text-xs text-slate-600 hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.bundles.destroy', $b) }}" class="inline" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-rose-500 hover:underline ml-2">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $bundles->links() }}
</div>
@endsection
