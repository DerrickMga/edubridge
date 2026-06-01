<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $enrolledCourseIds = $user->enrollments()->pluck('course_id');

        $announcements = Announcement::query()
            ->whereNotNull('published_at')
            ->where(function ($q) use ($enrolledCourseIds) {
                $q->where('audience', 'all')
                  ->orWhere(function ($qq) use ($enrolledCourseIds) {
                      $qq->where('audience', 'enrolled')
                         ->whereIn('course_id', $enrolledCourseIds);
                  });
            })
            ->with(['course', 'author'])
            ->latest('published_at')
            ->paginate(15);

        return view('student.announcements.index', compact('announcements'));
    }
}
