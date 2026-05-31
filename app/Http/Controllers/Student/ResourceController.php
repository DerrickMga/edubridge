<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\{Course, Resource};
use Illuminate\Support\Facades\Storage;

class ResourceController extends Controller
{
    private function gateEnrolled(Course $course): void
    {
        $user = auth()->user();
        $enrolled = $user->enrollments()->where('course_id', $course->id)->exists();
        abort_if(!$enrolled && !$user->isAdmin(), 403, 'Enrol in this course to access resources.');
    }

    public function index(Course $course)
    {
        $this->gateEnrolled($course);

        $resources = $course->resources()
            ->with('lesson')
            ->get()
            ->groupBy(fn($r) => $r->lesson_id ?? 0);

        $lessons = $course->lessons()
            ->where('status', 'published')
            ->orderBy('order')
            ->get()
            ->keyBy('id');

        return view('student.resources.index', compact('course', 'resources', 'lessons'));
    }

    public function download(Course $course, Resource $resource)
    {
        $this->gateEnrolled($course);
        abort_if($resource->course_id !== $course->id, 404);

        $resource->increment('download_count');

        if ($resource->external_url) {
            return redirect($resource->external_url);
        }

        abort_if(!$resource->storage_path, 404);
        abort_if(!Storage::disk('public')->exists($resource->storage_path), 404);

        $filename = basename($resource->storage_path);
        return Storage::disk('public')->download($resource->storage_path, $filename);
    }
}
