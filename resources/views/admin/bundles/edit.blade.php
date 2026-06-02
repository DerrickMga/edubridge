<x-app-layout>
    <x-slot name="title">{{ $bundle->exists ? 'Edit Bundle' : 'New Bundle' }} — Admin</x-slot>

    @php $isEdit = $bundle->exists; @endphp
    <div class="max-w-2xl space-y-5">
    <h1 class="text-2xl font-bold text-slate-900">{{ $isEdit ? 'Edit bundle' : 'New bundle' }}</h1>

    @if($errors->any())<div class="px-4 py-2 rounded bg-rose-50 text-rose-800 text-sm">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>@endif

    <form method="POST" action="{{ $isEdit ? route('admin.bundles.update', $bundle) : route('admin.bundles.store') }}" class="card p-5 space-y-4">
        @csrf
        @if($isEdit)@method('PUT')@endif

        <div class="grid grid-cols-2 gap-3 text-sm">
            <label class="col-span-2"><span class="block text-xs text-slate-500 uppercase mb-1">Title</span>
                <input name="title" required maxlength="200" value="{{ old('title', $bundle->title) }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
            </label>
            <label><span class="block text-xs text-slate-500 uppercase mb-1">Slug</span>
                <input name="slug" maxlength="200" value="{{ old('slug', $bundle->slug) }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
            </label>
            <label><span class="block text-xs text-slate-500 uppercase mb-1">Thumbnail URL</span>
                <input name="thumbnail" maxlength="500" value="{{ old('thumbnail', $bundle->thumbnail) }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
            </label>
            <label><span class="block text-xs text-slate-500 uppercase mb-1">Price USD</span>
                <input name="price_usd" type="number" step="0.01" min="0" required value="{{ old('price_usd', $bundle->price_usd) }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
            </label>
            <label><span class="block text-xs text-slate-500 uppercase mb-1">Price ZWG</span>
                <input name="price_zwg" type="number" step="0.01" min="0" value="{{ old('price_zwg', $bundle->price_zwg) }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
            </label>
            <label class="col-span-2"><span class="block text-xs text-slate-500 uppercase mb-1">Description</span>
                <textarea name="description" rows="3" class="w-full px-3 py-2 rounded-lg border border-slate-200">{{ old('description', $bundle->description) }}</textarea>
            </label>
            <label class="col-span-2"><span class="block text-xs text-slate-500 uppercase mb-1">Courses</span>
                <select name="course_ids[]" multiple size="8" class="w-full px-3 py-2 rounded-lg border border-slate-200">
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}" @selected(in_array($c->id, old('course_ids', $selected)))>{{ $c->title }}</option>
                    @endforeach
                </select>
                <p class="text-xs text-slate-400 mt-1">Hold ⌘/Ctrl to select multiple.</p>
            </label>
            <label><span class="block text-xs text-slate-500 uppercase mb-1">Active</span>
                <select name="is_active" class="w-full px-3 py-2 rounded-lg border border-slate-200">
                    <option value="1" @selected(old('is_active', $bundle->is_active ?? true))>Yes</option>
                    <option value="0" @selected(! old('is_active', $bundle->is_active ?? true))>No</option>
                </select>
            </label>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('admin.bundles.index') }}" class="px-3 py-2 text-sm text-slate-500 hover:underline">Cancel</a>
            <button class="px-3 py-2 text-sm rounded-lg bg-slate-900 text-white">{{ $isEdit ? 'Save' : 'Create' }}</button>
        </div>
    </form>
    </div>
</x-app-layout>
