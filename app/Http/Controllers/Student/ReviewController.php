<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseReview;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $user = $request->user();
        abort_unless($user->enrollments()->where('courses.id', $course->id)->exists(), 403, 'Enrol first.');

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title'  => 'nullable|string|max:200',
            'body'   => 'nullable|string|max:4000',
        ]);

        CourseReview::updateOrCreate(
            ['course_id' => $course->id, 'user_id' => $user->id],
            $data + ['is_visible' => true],
        );

        $this->recalculate($course);

        return back()->with('success', 'Thanks for your review!');
    }

    public function destroy(Request $request, CourseReview $review)
    {
        abort_unless($review->user_id === $request->user()->id, 403);
        $course = $review->course;
        $review->delete();
        $this->recalculate($course);

        return back()->with('success', 'Review removed.');
    }

    protected function recalculate(Course $course): void
    {
        $visible = $course->reviews()->where('is_visible', true);
        $course->update([
            'reviews_count'  => $visible->count(),
            'average_rating' => round((float) $visible->avg('rating'), 2),
        ]);
    }
}
