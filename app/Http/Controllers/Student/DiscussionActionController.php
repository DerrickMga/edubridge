<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Discussion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiscussionActionController extends Controller
{
    /** Toggle upvote: simple increment/decrement gated by session marker (no separate table) */
    public function upvote(Request $request, Discussion $discussion)
    {
        $key = 'd_up_'.$discussion->id;
        $session = $request->session();

        if ($session->has($key)) {
            $discussion->decrement('upvotes');
            $session->forget($key);
        } else {
            $discussion->increment('upvotes');
            $session->put($key, true);
        }

        return back();
    }

    /** Pin: teacher of the course or admin only */
    public function pin(Request $request, Discussion $discussion)
    {
        $user = $request->user();
        $course = $discussion->course;
        abort_unless(
            $user->isAdmin()
            || $course->teacher_id === $user->id
            || $course->teachers()->where('users.id', $user->id)->exists(),
            403
        );
        $discussion->update(['is_pinned' => ! $discussion->is_pinned]);
        return back()->with('success', $discussion->is_pinned ? 'Pinned.' : 'Unpinned.');
    }

    /** Mark resolved: author of the root thread, teacher, or admin */
    public function resolve(Request $request, Discussion $discussion)
    {
        $user = $request->user();
        $course = $discussion->course;
        abort_unless(
            $user->isAdmin()
            || $discussion->author_id === $user->id
            || $course->teacher_id === $user->id
            || $course->teachers()->where('users.id', $user->id)->exists(),
            403
        );
        $discussion->update(['is_resolved' => ! $discussion->is_resolved]);
        return back()->with('success', $discussion->is_resolved ? 'Marked resolved.' : 'Reopened.');
    }
}
