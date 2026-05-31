<x-app-layout>
    <x-slot name="title">Manage Courses</x-slot>

    <div class="page-header flex items-start justify-between gap-4 flex-wrap">
        <div>
            <h1 class="page-title">Course Management</h1>
            <p class="page-subtitle">Review, publish, and manage all courses on EduBridge</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold px-4 py-2 rounded-lg transition">
            ← Dashboard
        </a>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm">
        {{ session('success') }}
    </div>
    @endif

    {{-- Filters --}}
    <div class="card p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wider">Search</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Title or subject…"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wider">Status</label>
                <select name="status" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                    <option value="">All</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wider">Teacher</label>
                <select name="teacher_id" class="border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                    <option value="">All teachers</option>
                    @foreach($teachers as $t)
                    <option value="{{ $t->id }}" {{ request('teacher_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-5 py-2 rounded-lg transition">
                Filter
            </button>
            @if(request()->hasAny(['search','status','teacher_id']))
            <a href="{{ route('admin.courses.index') }}" class="text-sm text-slate-500 hover:text-slate-700 py-2">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Teacher</th>
                    <th>Subject / Level</th>
                    <th>Price</th>
                    <th>Enrolled</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($courses as $course)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            @if($course->thumbnail)
                            <img src="{{ asset('storage/'.$course->thumbnail) }}" class="w-10 h-10 rounded-lg object-cover flex-shrink-0" alt="">
                            @else
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                            @endif
                            <div>
                                <p class="font-medium text-slate-800 max-w-[180px] truncate">{{ $course->title }}</p>
                                <p class="text-xs text-slate-400">ID #{{ $course->id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="text-sm text-slate-600">{{ $course->teacher->name ?? '—' }}</td>
                    <td>
                        <p class="text-sm text-slate-700">{{ $course->subject }}</p>
                        <p class="text-xs text-slate-400">{{ $course->grade_level }}</p>
                    </td>
                    <td class="font-semibold text-emerald-700">
                        {{ $course->price_usd > 0 ? '$'.number_format($course->price_usd, 2) : '<span class="badge-green">Free</span>' }}
                    </td>
                    <td class="font-semibold text-indigo-700 text-center">{{ $course->enrollments_count }}</td>
                    <td>
                        <span class="{{ $course->status === 'published' ? 'badge-green' : 'badge-amber' }}">
                            {{ $course->status }}
                        </span>
                    </td>
                    <td class="text-xs text-slate-400">{{ $course->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="flex items-center gap-2">
                            {{-- Toggle publish --}}
                            <form method="POST" action="{{ route('admin.courses.toggle-status', $course) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="text-xs font-semibold px-2.5 py-1 rounded-lg transition {{ $course->status === 'published' ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }}">
                                    {{ $course->status === 'published' ? 'Unpublish' : 'Publish' }}
                                </button>
                            </form>
                            {{-- View --}}
                            <a href="{{ route('courses.show', $course) }}"
                               target="_blank"
                               class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 hover:bg-slate-200 rounded-lg transition">
                                View
                            </a>
                            {{-- Delete --}}
                            <form method="POST" action="{{ route('admin.courses.destroy', $course) }}"
                                  onsubmit="return confirm('Delete course \'{{ addslashes($course->title) }}\'? This cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="text-xs font-semibold px-2.5 py-1 bg-red-100 text-red-600 hover:bg-red-200 rounded-lg transition">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-12 text-slate-400">
                        <svg class="w-12 h-12 mx-auto mb-3 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"/></svg>
                        No courses found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($courses->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $courses->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
