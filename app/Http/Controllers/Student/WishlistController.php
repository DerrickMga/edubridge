<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $items = $request->user()->wishlist()->with('teacher')->latest('wishlists.created_at')->paginate(20);
        return view('student.wishlist.index', compact('items'));
    }

    public function toggle(Request $request, Course $course)
    {
        $user = $request->user();
        $exists = $user->wishlist()->where('courses.id', $course->id)->exists();
        if ($exists) {
            $user->wishlist()->detach($course->id);
            $msg = 'Removed from wishlist.';
        } else {
            $user->wishlist()->attach($course->id);
            $msg = 'Saved to wishlist.';
        }
        if ($request->wantsJson()) {
            return response()->json(['saved' => ! $exists]);
        }
        return back()->with('success', $msg);
    }
}
