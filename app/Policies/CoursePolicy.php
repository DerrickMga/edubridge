<?php
namespace App\Policies;

use App\Models\{Course, User};

class CoursePolicy
{
    /** Teacher can claim a course that has no teacher, or admin can always claim */
    public function claim(User $user, Course $course): bool
    {
        if ($user->isAdmin()) return true;
        return is_null($course->teacher_id);
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
