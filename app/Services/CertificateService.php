<?php
namespace App\Services;

use App\Models\{Certificate, Course, LessonProgress, User};
use App\Notifications\CertificateEarnedNotification;

class CertificateService
{
    /**
     * Issue a certificate if the student has completed all lessons.
     * Returns existing certificate if already issued.
     */
    public function issue(User $student, Course $course): ?Certificate
    {
        $existing = Certificate::where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->first();
        if ($existing) return $existing;

        $totalLessons = $course->lessons()->count();
        if ($totalLessons === 0) return null;

        $completedLessons = LessonProgress::where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->where('completed', true)
            ->count();

        if ($completedLessons < $totalLessons) return null;

        $certificate = Certificate::create([
            'student_id'         => $student->id,
            'course_id'          => $course->id,
            'certificate_number' => Certificate::generateNumber(),
            'issued_at'          => now(),
        ]);

        $student->notify(new CertificateEarnedNotification($certificate, $course));

        return $certificate;
    }
}
