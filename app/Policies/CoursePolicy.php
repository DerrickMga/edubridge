<?php
namespace App\Policies;

use App\Models\{Course, User};

class CoursePolicy
{
    /** Any authenticated teacher (or admin) can claim a course to teach it */
    public function claim(User $user, Course $course): bool
    {
        if ($user->isAdmin()) return true;
        // Teacher can claim any course that isn't already theirs
        return $course->teacher_id !== $user->id;
    }

    public function update(User $user, Course $course): bool
    {
        return $user->isAdmin() || $course->teacher_id === $user->id;
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->isAdmin();
    }
}
