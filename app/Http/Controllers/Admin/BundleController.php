<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BundleController extends Controller
{
    public function index()
    {
        $bundles = Bundle::with('courses')->latest()->paginate(20);
        return view('admin.bundles.index', compact('bundles'));
    }

    public function create()
    {
        return view('admin.bundles.edit', [
            'bundle' => new Bundle(['is_active' => true]),
            'courses' => Course::orderBy('title')->get(),
            'selected' => [],
        ]);
    }

    public function edit(Bundle $bundle)
    {
        return view('admin.bundles.edit', [
            'bundle' => $bundle,
            'courses' => Course::orderBy('title')->get(),
            'selected' => $bundle->courses->pluck('id')->all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request, null);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $data['created_by'] = $request->user()->id;
        $bundle = Bundle::create($data);
        $bundle->courses()->sync($request->input('course_ids', []));
        return redirect()->route('admin.bundles.index')->with('success', 'Bundle created.');
    }

    public function update(Request $request, Bundle $bundle)
    {
        $data = $this->validateData($request, $bundle->id);
        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $bundle->update($data);
        $bundle->courses()->sync($request->input('course_ids', []));
        return redirect()->route('admin.bundles.index')->with('success', 'Bundle updated.');
    }

    public function destroy(Bundle $bundle)
    {
        $bundle->delete();
        return redirect()->route('admin.bundles.index')->with('success', 'Bundle deleted.');
    }

    private function validateData(Request $request, ?int $id): array
    {
        return $request->validate([
            'title'       => ['required', 'string', 'max:200'],
            'slug'        => ['nullable', 'string', 'max:200', Rule::unique('bundles', 'slug')->ignore($id)],
            'description' => ['nullable', 'string', 'max:5000'],
            'thumbnail'   => ['nullable', 'string', 'max:500'],
            'price_usd'   => ['required', 'numeric', 'min:0'],
            'price_zwg'   => ['nullable', 'numeric', 'min:0'],
            'is_active'   => ['boolean'],
            'course_ids'  => ['nullable', 'array'],
            'course_ids.*'=> ['integer', 'exists:courses,id'],
        ]);
    }
}
