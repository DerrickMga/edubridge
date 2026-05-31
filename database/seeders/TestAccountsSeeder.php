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

        // ── Textile Technology & Design — O-Level (ZIMSEC 4058) ──────────────
        $textileCourse = Course::firstOrCreate(
            ['title' => 'Textile Technology & Design — O-Level', 'teacher_id' => $teacher->id],
            [
                'subject'      => 'Textile Technology & Design',
                'grade_level'  => 'Form 4 (O-Level)',
                'description'  => 'ZIMSEC O-Level Textile Technology & Design (4058). Covers fabric science, garment construction, pattern making, design principles and consumer education.',
                'status'       => 'published',
                'price_usd'    => 4.00,
                'price_zwg'    => 145.00,
            ]
        );

        $textileLessons = [
            ['title' => 'Fabric Types & Properties',    'description' => 'Natural vs synthetic fibres, blends (cotton/polyester), fabric weight, feel and structure.',             'order' => 1, 'status' => 'published'],
            ['title' => 'Garment Construction',         'description' => 'Darts, linings, zips, seams and the sequence of construction for lined garments.',                       'order' => 2, 'status' => 'published'],
            ['title' => 'Seams & Stitching Techniques', 'description' => 'Double stitched seams, binding curved edges, crossway strips and their applications.',                   'order' => 3, 'status' => 'published'],
            ['title' => 'Pattern Making & Layout',      'description' => 'Laying out pattern pieces, pattern markings, pleats (inverted, tucks), grainlines and cutting.',        'order' => 4, 'status' => 'published'],
            ['title' => 'Design Principles',            'description' => 'Elements and principles of design, fashion sketching using silhouettes, use of stripes and motifs.',     'order' => 5, 'status' => 'published'],
            ['title' => 'Wardrobe Planning & Consumer Ed', 'description' => 'Planning a wardrobe, budgeting, make vs buy decisions, gender equality in Textile Technology.',      'order' => 6, 'status' => 'draft'],
            ['title' => 'Equipment Care & Workshop Safety', 'description' => 'Care of tracing wheel, stiletto and tailor\'s chalk; safety precautions in the sewing workshop.', 'order' => 7, 'status' => 'draft'],
        ];

        foreach ($textileLessons as $data) {
            Lesson::firstOrCreate(
                ['course_id' => $textileCourse->id, 'title' => $data['title']],
                $data
            );
        }
    }
}
