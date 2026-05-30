<?php
namespace App\Policies;

use App\Models\{Course, User};

class CoursePolicy
{
    public function update(User $user, Course $course): bool
    {
        return $user->isAdmin() || $course->teacher_id === $user->id;
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->isAdmin() || $course->teacher_id === $user->id;
    }
}
