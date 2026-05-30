<?php
namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\{Course, Resource};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResourceController extends Controller
{
    public function index(Course $course)
    {
        abort_if($course->teacher_id !== auth()->id(), 403);
        $resources = $course->resources()->with('lesson')->get();
        return view('teacher.resources.index', compact('course', 'resources'));
    }

    public function store(Request $request, Course $course)
    {
        abort_if($course->teacher_id !== auth()->id(), 403);

        $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'type'         => ['required', 'in:pdf,video,audio,image,link,document,spreadsheet,other'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'lesson_id'    => ['nullable', 'integer', 'exists:lessons,id'],
            'external_url' => ['required_if:type,link', 'nullable', 'url', 'max:500'],
            'file'         => ['required_unless:type,link', 'nullable', 'file', 'max:102400'],
            'is_downloadable' => ['boolean'],
        ]);

        $resource = new Resource([
            'course_id'       => $course->id,
            'lesson_id'       => $request->lesson_id,
            'uploaded_by'     => auth()->id(),
            'title'           => $request->title,
            'description'     => $request->description,
            'type'            => $request->type,
            'external_url'    => $request->external_url,
            'is_downloadable' => $request->boolean('is_downloadable', true),
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->storeAs(
                "resources/{$course->id}",
                Str::slug($request->title).'-'.time().'.'.$file->getClientOriginalExtension(),
                'public'
            );
            $resource->storage_path   = $path;
            $resource->mime_type      = $file->getMimeType();
            $resource->file_size_bytes = $file->getSize();
        }

        $resource->save();
        return back()->with('success', 'Resource added: '.$resource->title);
    }

    public function destroy(Course $course, Resource $resource)
    {
        abort_if($course->teacher_id !== auth()->id(), 403);
        abort_if($resource->course_id !== $course->id, 404);
        if ($resource->storage_path) {
            Storage::disk('public')->delete($resource->storage_path);
        }
        $resource->delete();
        return back()->with('success', 'Resource deleted.');
    }
}
