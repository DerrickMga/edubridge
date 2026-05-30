<?php
namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\{Announcement, Course};
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function store(Request $request, Course $course)
    {
        abort_if($course->teacher_id !== auth()->id(), 403);

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body'  => ['required', 'string'],
        ]);

        Announcement::create([
            'course_id'    => $course->id,
            'author_id'    => auth()->id(),
            'title'        => $request->title,
            'body'         => $request->body,
            'audience'     => 'enrolled',
            'published_at' => now(),
        ]);

        return back()->with('success', 'Announcement posted.');
    }

    public function destroy(Course $course, Announcement $announcement)
    {
        abort_if($course->teacher_id !== auth()->id(), 403);
        $announcement->delete();
        return back()->with('success', 'Announcement deleted.');
    }
}
