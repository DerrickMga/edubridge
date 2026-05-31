<x-app-layout>
<x-slot name="title">New policy version</x-slot>
<div class="max-w-3xl space-y-5">
    <h1 class="text-2xl font-bold text-slate-900">New policy version</h1>
    <p class="text-sm text-slate-500">Re-using an existing slug will publish a new version; previous versions remain on record for audit.</p>

    @if($errors->any())
        <div class="px-4 py-2 rounded-lg bg-rose-50 text-rose-800 text-sm">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
    @endif

    <form method="POST" action="{{ route('admin.policies.store') }}" class="card p-5 space-y-4">
        @csrf
        <div class="grid md:grid-cols-2 gap-3">
            <label class="text-sm">
                <span class="block text-xs uppercase text-slate-500 mb-1">Slug</span>
                <input name="slug" required pattern="[a-z0-9\-]+" value="{{ old('slug') }}" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200">
            </label>
            <label class="text-sm">
                <span class="block text-xs uppercase text-slate-500 mb-1">Title</span>
                <input name="title" required value="{{ old('title') }}" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200">
            </label>
            <label class="text-sm">
                <span class="block text-xs uppercase text-slate-500 mb-1">Category</span>
                <select name="category" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200">
                    @foreach(['conduct','ip','privacy','acceptable_use','safeguarding','payments','termination','legal'] as $c)
                        <option value="{{ $c }}">{{ str_replace('_', ' ', $c) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-sm">
                <span class="block text-xs uppercase text-slate-500 mb-1">Active</span>
                <select name="is_active" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200">
                    <option value="1">Yes</option><option value="0">No</option>
                </select>
            </label>
        </div>
        <label class="text-sm block">
            <span class="block text-xs uppercase text-slate-500 mb-1">Body (Markdown / plain text)</span>
            <textarea name="body" required rows="14" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 font-mono">{{ old('body') }}</textarea>
        </label>
        <div class="flex items-center justify-end gap-2">
            <a href="{{ route('admin.policies.index') }}" class="px-3 py-2 text-sm text-slate-500 hover:underline">Cancel</a>
            <button class="px-3 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800">Publish version</button>
        </div>
    </form>
</div>
</x-app-layout>
