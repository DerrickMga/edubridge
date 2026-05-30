<x-app-layout>
    <x-slot name="title">Create Course</x-slot>
    <div class="max-w-2xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Create New Course</h1>
        <form method="POST" action="{{ route('teacher.courses.store') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Course Title</label>
                <input type="text" name="title" value="{{ old('title') }}" class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-green-500 outline-none" required>
                @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" placeholder="e.g. Mathematics" class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-green-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Grade Level</label>
                    <select name="grade_level" class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-green-500 outline-none" required>
                        <option value="">Select...</option>
                        @foreach(['Form 1','Form 2','Form 3','Form 4 (O-Level)','Form 5 (O-Level)','Lower 6 (A-Level)','Upper 6 (A-Level)'] as $grade)
                            <option value="{{ $grade }}" {{ old('grade_level') === $grade ? 'selected' : '' }}>{{ $grade }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="4" class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-green-500 outline-none">{{ old('description') }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price (USD)</label>
                    <input type="number" name="price_usd" value="{{ old('price_usd', 0) }}" min="0" step="0.01" class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-green-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price (ZWG)</label>
                    <input type="number" name="price_zwg" value="{{ old('price_zwg', 0) }}" min="0" step="0.01" class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:ring-2 focus:ring-green-500 outline-none">
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="bg-green-700 text-white px-6 py-3 rounded-xl font-semibold hover:bg-green-800 transition">Create Course</button>
                <a href="{{ route('teacher.dashboard') }}" class="px-6 py-3 rounded-xl border border-gray-300 text-gray-600 hover:bg-gray-50 transition">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
