<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;

class LessonAccessService
{
    /** Returns true if the user can access this lesson based on drip + enrolment. */
    public function canAccess(User $user, Lesson $lesson): bool
    {
        if ($user->isAdmin() || $lesson->course->teacher_id === $user->id) return true;

        $enrolment = Enrollment::where('user_id', $user->id)->where('course_id', $lesson->course_id)->first();
        if (! $enrolment) {
            // Check active subscription for any-course access
            return $user->activeSubscription() !== null;
        }

        $days = (int) ($lesson->release_after_days ?? 0);
        if ($days <= 0) return true;

        $unlockAt = ($enrolment->enrolled_at ?? $enrolment->created_at)->copy()->addDays($days);
        return now()->greaterThanOrEqualTo($unlockAt);
    }

    public function unlocksAt(User $user, Lesson $lesson): ?\Illuminate\Support\Carbon
    {
        $enrolment = Enrollment::where('user_id', $user->id)->where('course_id', $lesson->course_id)->first();
        if (! $enrolment) return null;
        $days = (int) ($lesson->release_after_days ?? 0);
        if ($days <= 0) return null;
        return ($enrolment->enrolled_at ?? $enrolment->created_at)->copy()->addDays($days);
    }
}
