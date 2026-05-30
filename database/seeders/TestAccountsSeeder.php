<?php
namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestAccountsSeeder extends Seeder
{
    public function run(): void
    {
        // ── Student ──────────────────────────────────────────────────────────
        $student = User::firstOrCreate(
            ['email' => 'student@edubridge.co.zw'],
            [
                'name'     => 'Test Student',
                'password' => Hash::make('password'),
                'role'     => 'student',
                'country'  => 'ZW',
            ]
        );
        $student->assignRole('student');

        // ── Teacher ──────────────────────────────────────────────────────────
        $teacher = User::firstOrCreate(
            ['email' => 'teacher@edubridge.co.zw'],
            [
                'name'     => 'Test Tutor',
                'password' => Hash::make('password'),
                'role'     => 'teacher',
                'country'  => 'ZW',
            ]
        );
        $teacher->assignRole('teacher');

        // ── Demo course with lessons (owned by teacher) ───────────────────────
        $course = Course::firstOrCreate(
            ['title' => 'Mathematics — O-Level Revision', 'teacher_id' => $teacher->id],
            [
                'subject'      => 'Mathematics',
                'grade_level'  => 'Form 4 (O-Level)',
                'description'  => 'Comprehensive O-Level maths revision covering algebra, geometry, and statistics.',
                'status'       => 'published',
                'price_usd'    => 5.00,
                'price_zwg'    => 180.00,
            ]
        );

        $lessons = [
            ['title' => 'Introduction to Algebra', 'description' => 'Basics of variables, expressions and equations.', 'order' => 1, 'status' => 'published'],
            ['title' => 'Quadratic Equations',      'description' => 'Solving quadratics by factoring and the formula.', 'order' => 2, 'status' => 'published'],
            ['title' => 'Circle Theorems',           'description' => 'Angles, arcs and chord properties in circles.', 'order' => 3, 'status' => 'published'],
            ['title' => 'Statistics & Probability',  'description' => 'Mean, median, mode, probability trees.', 'order' => 4, 'status' => 'draft'],
        ];

        foreach ($lessons as $data) {
            Lesson::firstOrCreate(
                ['course_id' => $course->id, 'title' => $data['title']],
                $data
            );
        }

        // Enrol the student in the demo course
        $student->enrollments()->syncWithoutDetaching([$course->id]);
    }
}
