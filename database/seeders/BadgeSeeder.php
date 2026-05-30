<?php
namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            ['slug' => 'first-lesson',    'name' => 'First Step',    'description' => 'Completed your first lesson.',      'icon' => '👣', 'colour' => 'emerald'],
            ['slug' => 'bookworm',        'name' => 'Bookworm',       'description' => 'Completed 10 lessons.',             'icon' => '📚', 'colour' => 'blue'],
            ['slug' => 'scholar',         'name' => 'Scholar',        'description' => 'Completed 25 lessons.',             'icon' => '🎓', 'colour' => 'violet'],
            ['slug' => 'streak-3',        'name' => 'On Fire',        'description' => '3-day learning streak.',            'icon' => '🔥', 'colour' => 'orange'],
            ['slug' => 'streak-7',        'name' => 'Unstoppable',    'description' => '7-day learning streak.',            'icon' => '⚡', 'colour' => 'amber'],
            ['slug' => 'quiz-pass',       'name' => 'Quiz Taker',     'description' => 'Passed your first quiz.',           'icon' => '📝', 'colour' => 'teal'],
            ['slug' => 'quiz-perfect',    'name' => 'Perfect Score',  'description' => 'Scored 100% on a quiz.',            'icon' => '💯', 'colour' => 'emerald'],
            ['slug' => 'quiz-master',     'name' => 'Quiz Master',    'description' => 'Passed 5 quizzes.',                 'icon' => '🏆', 'colour' => 'amber'],
            ['slug' => 'course-complete', 'name' => 'Graduate',       'description' => 'Completed a full course.',          'icon' => '🎖️', 'colour' => 'purple'],
            ['slug' => 'xp-100',          'name' => 'Rising Star',    'description' => 'Earned 100 XP.',                   'icon' => '⭐', 'colour' => 'yellow'],
            ['slug' => 'xp-500',          'name' => 'High Achiever',  'description' => 'Earned 500 XP.',                   'icon' => '🌟', 'colour' => 'amber'],
            ['slug' => 'xp-1000',         'name' => 'Legend',         'description' => 'Earned 1,000 XP.',                 'icon' => '👑', 'colour' => 'amber'],
        ];

        foreach ($badges as $b) {
            Badge::updateOrCreate(['slug' => $b['slug']], $b);
        }
    }
}
